<?php
/**
 * SQLite Database Manager with WAL Mode
 * Handles both Owner and Vendor databases
 */

class Database {
    private $connection;
    private $dbPath;
    
    /**
     * Create database connection
     * @param string $type 'owner' or vendor ID
     */
    public function __construct($type = 'owner') {
        if ($type === 'owner') {
            $this->dbPath = OWNER_DB_PATH . '/owner.db';
        } else {
            // Vendor-specific database
            $vendorDir = VENDOR_DB_PATH . '/' . $type;
            if (!file_exists($vendorDir)) {
                mkdir($vendorDir, 0755, true);
            }
            $this->dbPath = $vendorDir . '/vendor.db';
        }
        
        $this->connect();
        $this->initialize();
    }
    
    /**
     * Establish SQLite connection with WAL mode
     */
    private function connect() {
        try {
            $this->connection = new PDO('sqlite:' . $this->dbPath);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Enable WAL mode for better concurrency
            $this->connection->exec('PRAGMA journal_mode=WAL;');
            $this->connection->exec('PRAGMA synchronous=NORMAL;');
            $this->connection->exec('PRAGMA foreign_keys=ON;');
            $this->connection->exec('PRAGMA busy_timeout=30000;'); // 30 second timeout
            $this->connection->exec('PRAGMA cache_size=10000;'); // Increase cache
            $this->connection->exec('PRAGMA temp_store=MEMORY;'); // Use memory for temp
            
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Initialize database schema
     */
    private function initialize() {
        // Check if tables exist
        $tables = $this->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($tables)) {
            $this->createSchema();
        }
    }
    
    /**
     * Create database schema based on type
     */
    private function createSchema() {
        if (strpos($this->dbPath, 'owner.db') !== false) {
            $this->createOwnerSchema();
        } else {
            $this->createVendorSchema();
        }
    }
    
    /**
     * Create Owner database schema
     */
    private function createOwnerSchema() {
        $sql = "
        -- Users table (Owner, Vendors, and Normal Users)
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role TEXT DEFAULT 'user', -- owner, vendor, user
            vendor_id TEXT NULL, -- For vendors
            is_active INTEGER DEFAULT 1,
            totp_secret TEXT NULL,
            totp_enabled INTEGER DEFAULT 0,
            created_at INTEGER NOT NULL,
            updated_at INTEGER NOT NULL
        );
        
        -- Tokens table (Access provisioning)
        CREATE TABLE IF NOT EXISTS tokens (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            token TEXT UNIQUE NOT NULL,
            role TEXT NOT NULL, -- vendor, reseller
            parent_id INTEGER NULL, -- User ID of creator
            expiry_date INTEGER NOT NULL,
            is_unlimited INTEGER DEFAULT 0,
            initial_balance REAL DEFAULT 0,
            discount_rate REAL DEFAULT 0, -- Percentage (0-100)
            can_create_products INTEGER DEFAULT 0,
            payment_access INTEGER DEFAULT 0,
            consumed_at INTEGER NULL,
            consumed_by INTEGER NULL,
            created_at INTEGER NOT NULL,
            FOREIGN KEY (parent_id) REFERENCES users(id),
            FOREIGN KEY (consumed_by) REFERENCES users(id)
        );
        
        -- Vendors table
        CREATE TABLE IF NOT EXISTS vendors (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER UNIQUE NOT NULL,
            vendor_code TEXT UNIQUE NOT NULL,
            company_name TEXT NOT NULL,
            is_unlimited INTEGER DEFAULT 0,
            balance REAL DEFAULT 0,
            discount_rate REAL DEFAULT 0,
            can_create_products INTEGER DEFAULT 0,
            payment_access INTEGER DEFAULT 0,
            expiry_date INTEGER NOT NULL,
            is_active INTEGER DEFAULT 1,
            created_at INTEGER NOT NULL,
            updated_at INTEGER NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id)
        );
        
        -- Global Products table
        CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT,
            base_price REAL NOT NULL,
            is_active INTEGER DEFAULT 1,
            created_at INTEGER NOT NULL,
            updated_at INTEGER NOT NULL
        );
        
        -- Vendor-specific products
        CREATE TABLE IF NOT EXISTS vendor_products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            vendor_id INTEGER NOT NULL,
            product_id INTEGER NULL, -- NULL if custom product
            name TEXT NOT NULL,
            description TEXT,
            base_price REAL NOT NULL,
            is_custom INTEGER DEFAULT 0,
            is_active INTEGER DEFAULT 1,
            created_at INTEGER NOT NULL,
            updated_at INTEGER NOT NULL,
            FOREIGN KEY (vendor_id) REFERENCES vendors(id),
            FOREIGN KEY (product_id) REFERENCES products(id)
        );
        
        -- Currency transactions log
        CREATE TABLE IF NOT EXISTS currency_transactions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            vendor_id INTEGER NOT NULL,
            amount REAL NOT NULL,
            type TEXT NOT NULL, -- credit, debit
            description TEXT,
            balance_after REAL NOT NULL,
            created_at INTEGER NOT NULL,
            FOREIGN KEY (vendor_id) REFERENCES vendors(id)
        );
        
        -- System settings
        CREATE TABLE IF NOT EXISTS settings (
            key TEXT PRIMARY KEY,
            value TEXT NOT NULL,
            updated_at INTEGER NOT NULL
        );
        
        -- Activity logs (compressed)
        CREATE TABLE IF NOT EXISTS activity_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            action TEXT NOT NULL,
            details BLOB, -- Compressed JSON
            ip_address TEXT,
            user_agent TEXT,
            created_at INTEGER NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id)
        );
        
        -- Create indexes
        CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
        CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);
        CREATE INDEX IF NOT EXISTS idx_tokens_token ON tokens(token);
        CREATE INDEX IF NOT EXISTS idx_tokens_consumed ON tokens(consumed_at);
        CREATE INDEX IF NOT EXISTS idx_vendors_code ON vendors(vendor_code);
        CREATE INDEX IF NOT EXISTS idx_vendors_user ON vendors(user_id);
        CREATE INDEX IF NOT EXISTS idx_activity_user ON activity_logs(user_id);
        CREATE INDEX IF NOT EXISTS idx_activity_created ON activity_logs(created_at);
        ";
        
        $this->connection->exec($sql);
        
        // Insert default owner account
        $this->insertDefaultOwner();
    }
    
    /**
     * Create Vendor database schema
     */
    private function createVendorSchema() {
        $sql = "
        -- Resellers table
        CREATE TABLE IF NOT EXISTS resellers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL, -- Reference to owner DB
            reseller_code TEXT UNIQUE NOT NULL,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            is_unlimited INTEGER DEFAULT 0,
            balance REAL DEFAULT 0,
            discount_rate REAL DEFAULT 0,
            expiry_date INTEGER NOT NULL,
            is_active INTEGER DEFAULT 1,
            created_at INTEGER NOT NULL,
            updated_at INTEGER NOT NULL
        );
        
        -- Licenses table
        CREATE TABLE IF NOT EXISTS licenses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            license_key TEXT UNIQUE NOT NULL,
            product_id INTEGER NOT NULL,
            product_name TEXT NOT NULL,
            reseller_id INTEGER NULL,
            customer_email TEXT,
            customer_name TEXT,
            max_devices INTEGER DEFAULT 1,
            duration TEXT NOT NULL, -- 1hr, 1d, 7d, etc.
            price REAL NOT NULL,
            discount_applied REAL DEFAULT 0,
            expires_at INTEGER NOT NULL,
            is_active INTEGER DEFAULT 1,
            is_suspended INTEGER DEFAULT 0,
            created_at INTEGER NOT NULL,
            updated_at INTEGER NOT NULL,
            FOREIGN KEY (reseller_id) REFERENCES resellers(id)
        );
        
        -- Devices table (for multi-device licenses)
        CREATE TABLE IF NOT EXISTS devices (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            license_id INTEGER NOT NULL,
            hwid TEXT NOT NULL,
            device_name TEXT,
            first_activated INTEGER NOT NULL,
            last_seen INTEGER NOT NULL,
            is_suspended INTEGER DEFAULT 0,
            FOREIGN KEY (license_id) REFERENCES licenses(id),
            UNIQUE(license_id, hwid)
        );
        
        -- Payment gateway configuration
        CREATE TABLE IF NOT EXISTS payment_gateways (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            gateway TEXT NOT NULL, -- cryptomus, razorpay
            api_key TEXT NOT NULL,
            api_secret TEXT NOT NULL,
            is_active INTEGER DEFAULT 1,
            created_at INTEGER NOT NULL,
            updated_at INTEGER NOT NULL
        );
        
        -- Payment transactions
        CREATE TABLE IF NOT EXISTS transactions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            transaction_id TEXT UNIQUE NOT NULL,
            gateway TEXT NOT NULL,
            license_id INTEGER NULL,
            amount REAL NOT NULL,
            currency TEXT DEFAULT 'USD',
            status TEXT NOT NULL, -- pending, completed, failed
            customer_email TEXT,
            details BLOB, -- Compressed JSON
            created_at INTEGER NOT NULL,
            updated_at INTEGER NOT NULL,
            FOREIGN KEY (license_id) REFERENCES licenses(id)
        );
        
        -- Reseller currency transactions
        CREATE TABLE IF NOT EXISTS reseller_transactions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            reseller_id INTEGER NOT NULL,
            amount REAL NOT NULL,
            type TEXT NOT NULL, -- credit, debit
            description TEXT,
            balance_after REAL NOT NULL,
            created_at INTEGER NOT NULL,
            FOREIGN KEY (reseller_id) REFERENCES resellers(id)
        );
        
        -- Create indexes
        CREATE INDEX IF NOT EXISTS idx_resellers_code ON resellers(reseller_code);
        CREATE INDEX IF NOT EXISTS idx_licenses_key ON licenses(license_key);
        CREATE INDEX IF NOT EXISTS idx_licenses_reseller ON licenses(reseller_id);
        CREATE INDEX IF NOT EXISTS idx_devices_license ON devices(license_id);
        CREATE INDEX IF NOT EXISTS idx_devices_hwid ON devices(hwid);
        CREATE INDEX IF NOT EXISTS idx_transactions_id ON transactions(transaction_id);
        ";
        
        $this->connection->exec($sql);
    }
    
    /**
     * Insert default owner account
     */
    private function insertDefaultOwner() {
        $stmt = $this->connection->prepare("SELECT COUNT(*) FROM users WHERE role = 'owner'");
        $stmt->execute();
        
        if ($stmt->fetchColumn() == 0) {
            $now = time();
            $password = password_hash('admin123', PASSWORD_BCRYPT);
            
            $stmt = $this->connection->prepare("
                INSERT INTO users (email, password, role, is_active, created_at, updated_at)
                VALUES (?, ?, 'owner', 1, ?, ?)
            ");
            $stmt->execute(['admin@licensify.local', $password, $now, $now]);
        }
    }
    
    /**
     * Execute a query with retry logic for locked database
     */
    public function query($sql, $params = [], $maxRetries = 3) {
        $attempt = 0;
        $lastException = null;
        
        while ($attempt < $maxRetries) {
            try {
                $stmt = $this->connection->prepare($sql);
                $stmt->execute($params);
                return $stmt;
            } catch (PDOException $e) {
                $lastException = $e;
                
                // Check if it's a database locked error
                if (strpos($e->getMessage(), 'database is locked') !== false) {
                    $attempt++;
                    if ($attempt < $maxRetries) {
                        // Wait before retry (exponential backoff)
                        usleep(100000 * $attempt); // 100ms, 200ms, 300ms
                        continue;
                    }
                }
                
                // Not a locking issue or max retries reached
                error_log('Database query error: ' . $e->getMessage());
                throw $e;
            }
        }
        
        // If we get here, all retries failed
        error_log('Database query failed after ' . $maxRetries . ' attempts: ' . $lastException->getMessage());
        throw $lastException;
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->connection->rollBack();
    }
    
    /**
     * Get last insert ID
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Get connection
     */
    public function getConnection() {
        return $this->connection;
    }
}

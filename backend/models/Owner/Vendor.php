<?php
/**
 * Vendor Model (Owner Database)
 */

class Vendor {
    private $db;
    private $encryption;
    
    public function __construct() {
        $this->db = new Database('owner');
        $this->encryption = new Encryption();
    }
    
    /**
     * Create vendor from token
     */
    public function createFromToken($userId, $tokenData, $companyName) {
        $vendorCode = $this->generateVendorCode();
        $now = time();
        
        $this->db->beginTransaction();
        
        try {
            // Create vendor
            $stmt = $this->db->query(
                "INSERT INTO vendors (user_id, vendor_code, company_name, is_unlimited, 
                 balance, discount_rate, can_create_products, payment_access, 
                 expiry_date, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $userId,
                    $vendorCode,
                    $companyName,
                    $tokenData['is_unlimited'],
                    $tokenData['initial_balance'],
                    $tokenData['discount_rate'],
                    $tokenData['can_create_products'],
                    $tokenData['payment_access'],
                    $tokenData['expiry_date'],
                    $now,
                    $now
                ]
            );
            
            $vendorId = $this->db->lastInsertId();
            
            // Update user role
            $this->db->query(
                "UPDATE users SET role = 'vendor', vendor_id = ?, updated_at = ? WHERE id = ?",
                [$vendorCode, $now, $userId]
            );
            
            $this->db->commit();
            
            // Create vendor database AFTER committing the transaction
            // This prevents database locking issues
            try {
                $vendorDb = new Database($vendorCode);
            } catch (Exception $e) {
                error_log('Failed to create vendor database: ' . $e->getMessage());
                // Database creation failed, but vendor record exists
                // This can be retried later
            }
            
            return [
                'id' => $vendorId,
                'vendor_code' => $vendorCode
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Generate unique vendor code
     */
    private function generateVendorCode() {
        do {
            $code = 'VND' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            $stmt = $this->db->query("SELECT COUNT(*) FROM vendors WHERE vendor_code = ?", [$code]);
        } while ($stmt->fetchColumn() > 0);
        
        return $code;
    }
    
    /**
     * Get vendor by ID
     */
    public function getById($id) {
        $stmt = $this->db->query("SELECT * FROM vendors WHERE id = ?", [$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get vendor by code
     */
    public function getByCode($code) {
        $stmt = $this->db->query("SELECT * FROM vendors WHERE vendor_code = ?", [$code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get vendor by user ID
     */
    public function getByUserId($userId) {
        $stmt = $this->db->query("SELECT * FROM vendors WHERE user_id = ?", [$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * List all vendors
     */
    public function list($filters = []) {
        $where = [];
        $params = [];
        
        if (isset($filters['is_active'])) {
            $where[] = "is_active = ?";
            $params[] = $filters['is_active'];
        }
        
        if (isset($filters['search'])) {
            $where[] = "(company_name LIKE ? OR vendor_code LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }
        
        $sql = "SELECT v.*, u.email 
                FROM vendors v 
                JOIN users u ON v.user_id = u.id";
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $sql .= " ORDER BY v.created_at DESC";
        
        if (isset($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
            if (isset($filters['offset'])) {
                $sql .= " OFFSET " . (int)$filters['offset'];
            }
        }
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Update vendor
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = ['company_name', 'is_unlimited', 'balance', 'discount_rate', 
                          'can_create_products', 'payment_access', 'expiry_date', 'is_active'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $fields[] = "updated_at = ?";
        $params[] = time();
        $params[] = $id;
        
        $sql = "UPDATE vendors SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->query($sql, $params);
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Check if vendor expired
     */
    public function checkExpiry($vendorId) {
        $vendor = $this->getById($vendorId);
        
        if (!$vendor) {
            return false;
        }
        
        if ($vendor['expiry_date'] < time() && $vendor['is_active']) {
            // Expire vendor - revert to normal user
            $this->db->query(
                "UPDATE vendors SET is_active = 0, updated_at = ? WHERE id = ?",
                [time(), $vendorId]
            );
            
            $this->db->query(
                "UPDATE users SET role = 'user', updated_at = ? WHERE id = ?",
                [time(), $vendor['user_id']]
            );
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Extend vendor expiry
     */
    public function extendExpiry($vendorId, $newExpiryDate) {
        $stmt = $this->db->query(
            "UPDATE vendors SET expiry_date = ?, updated_at = ? WHERE id = ?",
            [$newExpiryDate, time(), $vendorId]
        );
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Get vendor statistics
     */
    public function getStats($vendorId = null) {
        if ($vendorId) {
            // Single vendor stats
            $vendor = $this->getById($vendorId);
            
            if (!$vendor) {
                return null;
            }
            
            // Get reseller count from vendor DB
            $vendorDb = new Database($vendor['vendor_code']);
            $stmt = $vendorDb->query("SELECT COUNT(*) FROM resellers WHERE is_active = 1");
            $resellerCount = $stmt->fetchColumn();
            
            $stmt = $vendorDb->query("SELECT COUNT(*) FROM licenses WHERE is_active = 1");
            $licenseCount = $stmt->fetchColumn();
            
            return [
                'vendor' => $vendor,
                'resellers' => $resellerCount,
                'licenses' => $licenseCount
            ];
        } else {
            // All vendors stats
            $stmt = $this->db->query(
                "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN is_unlimited = 1 THEN 1 ELSE 0 END) as unlimited,
                    SUM(balance) as total_balance
                 FROM vendors"
            );
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
}

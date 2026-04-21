<?php
/**
 * License Model (Vendor Database)
 * Handles license generation and management
 */

class License {
    private $db;
    private $encryption;
    private $currency;
    
    public function __construct($vendorCode) {
        $this->db = new Database($vendorCode);
        $this->encryption = new Encryption();
        $this->currency = new Currency($this->db);
    }
    
    /**
     * Generate license
     */
    public function generate($data) {
        $licenseKey = $this->encryption->generateLicenseKey();
        $now = time();
        
        // Calculate price
        $price = $this->currency->calculateLicensePrice(
            $data['base_price'],
            $data['max_devices'] ?? 1,
            $data['duration'],
            $data['discount_rate'] ?? 0
        );
        
        // Calculate expiry
        $durationSeconds = LICENSE_DURATIONS[$data['duration']] ?? 86400;
        $expiresAt = $now + $durationSeconds;
        
        $this->db->beginTransaction();
        
        try {
            // Create license
            $stmt = $this->db->query(
                "INSERT INTO licenses (license_key, product_id, product_name, reseller_id, 
                 customer_email, customer_name, max_devices, duration, price, discount_applied, 
                 expires_at, is_active, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?)",
                [
                    $licenseKey,
                    $data['product_id'],
                    $data['product_name'],
                    $data['reseller_id'] ?? null,
                    $data['customer_email'] ?? null,
                    $data['customer_name'] ?? null,
                    $data['max_devices'] ?? 1,
                    $data['duration'],
                    $price,
                    $data['discount_rate'] ?? 0,
                    $expiresAt,
                    $now,
                    $now
                ]
            );
            
            $licenseId = $this->db->lastInsertId();
            
            // Deduct balance if reseller
            if (isset($data['reseller_id']) && $data['reseller_id']) {
                $this->currency->deductResellerBalance(
                    $data['reseller_id'],
                    $price,
                    "License generated: {$licenseKey}"
                );
            }
            
            $this->db->commit();
            
            return [
                'id' => $licenseId,
                'license_key' => $licenseKey,
                'price' => $price,
                'expires_at' => $expiresAt
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Validate license
     */
    public function validate($licenseKey, $hwid = null) {
        $stmt = $this->db->query(
            "SELECT * FROM licenses WHERE license_key = ?",
            [$licenseKey]
        );
        
        $license = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$license) {
            return ['valid' => false, 'message' => 'License not found'];
        }
        
        if (!$license['is_active']) {
            return ['valid' => false, 'message' => 'License is inactive'];
        }
        
        if ($license['is_suspended']) {
            return ['valid' => false, 'message' => 'License is suspended'];
        }
        
        if ($license['expires_at'] < time()) {
            return ['valid' => false, 'message' => 'License has expired'];
        }
        
        // Check device if HWID provided
        if ($hwid) {
            $deviceCheck = $this->checkDevice($license['id'], $hwid);
            
            if (!$deviceCheck['allowed']) {
                return ['valid' => false, 'message' => $deviceCheck['message']];
            }
        }
        
        return [
            'valid' => true,
            'license' => $license,
            'message' => 'License is valid'
        ];
    }
    
    /**
     * Check device activation
     */
    private function checkDevice($licenseId, $hwid) {
        // Get license
        $stmt = $this->db->query("SELECT max_devices FROM licenses WHERE id = ?", [$licenseId]);
        $license = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$license) {
            return ['allowed' => false, 'message' => 'License not found'];
        }
        
        // Check if device already registered
        $stmt = $this->db->query(
            "SELECT * FROM devices WHERE license_id = ? AND hwid = ?",
            [$licenseId, $hwid]
        );
        
        $device = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($device) {
            // Device exists
            if ($device['is_suspended']) {
                return ['allowed' => false, 'message' => 'Device is suspended'];
            }
            
            // Update last seen
            $this->db->query(
                "UPDATE devices SET last_seen = ? WHERE id = ?",
                [time(), $device['id']]
            );
            
            return ['allowed' => true, 'device' => $device];
        }
        
        // New device - check limit
        $stmt = $this->db->query(
            "SELECT COUNT(*) FROM devices WHERE license_id = ?",
            [$licenseId]
        );
        
        $deviceCount = $stmt->fetchColumn();
        
        if ($deviceCount >= $license['max_devices']) {
            return ['allowed' => false, 'message' => 'Maximum device limit reached'];
        }
        
        // Register new device
        $now = time();
        $this->db->query(
            "INSERT INTO devices (license_id, hwid, first_activated, last_seen)
             VALUES (?, ?, ?, ?)",
            [$licenseId, $hwid, $now, $now]
        );
        
        return ['allowed' => true, 'new_device' => true];
    }
    
    /**
     * Get license by key
     */
    public function getByKey($licenseKey) {
        $stmt = $this->db->query("SELECT * FROM licenses WHERE license_key = ?", [$licenseKey]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get license devices
     */
    public function getDevices($licenseId) {
        $stmt = $this->db->query(
            "SELECT * FROM devices WHERE license_id = ? ORDER BY first_activated DESC",
            [$licenseId]
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Suspend license
     */
    public function suspend($licenseId, $reason = null) {
        $stmt = $this->db->query(
            "UPDATE licenses SET is_suspended = 1, updated_at = ? WHERE id = ?",
            [time(), $licenseId]
        );
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Unsuspend license
     */
    public function unsuspend($licenseId) {
        $stmt = $this->db->query(
            "UPDATE licenses SET is_suspended = 0, updated_at = ? WHERE id = ?",
            [time(), $licenseId]
        );
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Suspend device
     */
    public function suspendDevice($licenseId, $hwid) {
        $stmt = $this->db->query(
            "UPDATE devices SET is_suspended = 1 WHERE license_id = ? AND hwid = ?",
            [$licenseId, $hwid]
        );
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Unsuspend device
     */
    public function unsuspendDevice($licenseId, $hwid) {
        $stmt = $this->db->query(
            "UPDATE devices SET is_suspended = 0 WHERE license_id = ? AND hwid = ?",
            [$licenseId, $hwid]
        );
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * List licenses
     */
    public function list($filters = []) {
        $where = [];
        $params = [];
        
        if (isset($filters['reseller_id'])) {
            $where[] = "reseller_id = ?";
            $params[] = $filters['reseller_id'];
        }
        
        if (isset($filters['is_active'])) {
            $where[] = "is_active = ?";
            $params[] = $filters['is_active'];
        }
        
        if (isset($filters['search'])) {
            $where[] = "(license_key LIKE ? OR customer_email LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }
        
        $sql = "SELECT * FROM licenses";
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $sql .= " ORDER BY created_at DESC";
        
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
     * Get license statistics
     */
    public function getStats($resellerId = null) {
        $where = $resellerId ? "WHERE reseller_id = ?" : "";
        $params = $resellerId ? [$resellerId] : [];
        
        $stmt = $this->db->query(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN is_suspended = 1 THEN 1 ELSE 0 END) as suspended,
                SUM(CASE WHEN expires_at < ? THEN 1 ELSE 0 END) as expired,
                SUM(price) as total_revenue
             FROM licenses {$where}",
            array_merge([time()], $params)
        );
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

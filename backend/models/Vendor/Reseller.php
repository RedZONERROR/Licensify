<?php
/**
 * Reseller Model (Vendor Database)
 */

class Reseller {
    private $db;
    private $encryption;
    
    public function __construct($vendorCode) {
        $this->db = new Database($vendorCode);
        $this->encryption = new Encryption();
    }
    
    /**
     * Create reseller from token
     */
    public function createFromToken($userId, $tokenData, $name, $email) {
        $resellerCode = $this->generateResellerCode();
        $now = time();
        
        $stmt = $this->db->query(
            "INSERT INTO resellers (user_id, reseller_code, name, email, is_unlimited, 
             balance, discount_rate, expiry_date, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $userId,
                $resellerCode,
                $name,
                $email,
                $tokenData['is_unlimited'],
                $tokenData['initial_balance'],
                $tokenData['discount_rate'],
                $tokenData['expiry_date'],
                $now,
                $now
            ]
        );
        
        return [
            'id' => $this->db->lastInsertId(),
            'reseller_code' => $resellerCode
        ];
    }
    
    /**
     * Generate unique reseller code
     */
    private function generateResellerCode() {
        do {
            $code = 'RSL' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            $stmt = $this->db->query("SELECT COUNT(*) FROM resellers WHERE reseller_code = ?", [$code]);
        } while ($stmt->fetchColumn() > 0);
        
        return $code;
    }
    
    /**
     * Get reseller by ID
     */
    public function getById($id) {
        $stmt = $this->db->query("SELECT * FROM resellers WHERE id = ?", [$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get reseller by code
     */
    public function getByCode($code) {
        $stmt = $this->db->query("SELECT * FROM resellers WHERE reseller_code = ?", [$code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get reseller by user ID
     */
    public function getByUserId($userId) {
        $stmt = $this->db->query("SELECT * FROM resellers WHERE user_id = ?", [$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * List resellers
     */
    public function list($filters = []) {
        $where = [];
        $params = [];
        
        if (isset($filters['is_active'])) {
            $where[] = "is_active = ?";
            $params[] = $filters['is_active'];
        }
        
        if (isset($filters['search'])) {
            $where[] = "(name LIKE ? OR email LIKE ? OR reseller_code LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        
        $sql = "SELECT * FROM resellers";
        
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
     * Update reseller
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = ['name', 'email', 'is_unlimited', 'balance', 'discount_rate', 
                          'expiry_date', 'is_active'];
        
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
        
        $sql = "UPDATE resellers SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->query($sql, $params);
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Check if reseller expired
     */
    public function checkExpiry($resellerId) {
        $reseller = $this->getById($resellerId);
        
        if (!$reseller) {
            return false;
        }
        
        if ($reseller['expiry_date'] < time() && $reseller['is_active']) {
            // Expire reseller
            $this->db->query(
                "UPDATE resellers SET is_active = 0, updated_at = ? WHERE id = ?",
                [time(), $resellerId]
            );
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Extend reseller expiry
     */
    public function extendExpiry($resellerId, $newExpiryDate) {
        $stmt = $this->db->query(
            "UPDATE resellers SET expiry_date = ?, updated_at = ? WHERE id = ?",
            [$newExpiryDate, time(), $resellerId]
        );
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Get reseller statistics
     */
    public function getStats($resellerId = null) {
        if ($resellerId) {
            // Single reseller stats
            $reseller = $this->getById($resellerId);
            
            if (!$reseller) {
                return null;
            }
            
            $stmt = $this->db->query(
                "SELECT COUNT(*) FROM licenses WHERE reseller_id = ? AND is_active = 1",
                [$resellerId]
            );
            $licenseCount = $stmt->fetchColumn();
            
            $stmt = $this->db->query(
                "SELECT SUM(price) FROM licenses WHERE reseller_id = ?",
                [$resellerId]
            );
            $totalRevenue = $stmt->fetchColumn();
            
            return [
                'reseller' => $reseller,
                'licenses' => $licenseCount,
                'revenue' => $totalRevenue
            ];
        } else {
            // All resellers stats
            $stmt = $this->db->query(
                "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN is_unlimited = 1 THEN 1 ELSE 0 END) as unlimited,
                    SUM(balance) as total_balance
                 FROM resellers"
            );
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
}

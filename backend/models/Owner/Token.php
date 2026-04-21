<?php
/**
 * Token Model (Owner Database)
 * Handles access token generation and consumption
 */

class Token {
    private $db;
    private $encryption;
    
    public function __construct() {
        $this->db = new Database('owner');
        $this->encryption = new Encryption();
    }
    
    /**
     * Generate new access token
     */
    public function generate($data) {
        $token = $this->encryption->generateToken(32);
        $now = time();
        
        $stmt = $this->db->query(
            "INSERT INTO tokens (token, role, parent_id, expiry_date, is_unlimited, 
             initial_balance, discount_rate, can_create_products, payment_access, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $token,
                $data['role'],
                $data['parent_id'] ?? null,
                $data['expiry_date'],
                $data['is_unlimited'] ?? 0,
                $data['initial_balance'] ?? 0,
                $data['discount_rate'] ?? 0,
                $data['can_create_products'] ?? 0,
                $data['payment_access'] ?? 0,
                $now
            ]
        );
        
        return $token;
    }
    
    /**
     * Validate and get token details
     */
    public function validate($token) {
        $stmt = $this->db->query(
            "SELECT * FROM tokens WHERE token = ? AND consumed_at IS NULL",
            [$token]
        );
        
        $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$tokenData) {
            return ['valid' => false, 'message' => 'Invalid or already used token'];
        }
        
        // Check expiry
        if ($tokenData['expiry_date'] < time()) {
            return ['valid' => false, 'message' => 'Token has expired'];
        }
        
        return ['valid' => true, 'data' => $tokenData];
    }
    
    /**
     * Consume token (mark as used)
     */
    public function consume($token, $userId) {
        $stmt = $this->db->query(
            "UPDATE tokens SET consumed_at = ?, consumed_by = ? WHERE token = ?",
            [time(), $userId, $token]
        );
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Get token by ID
     */
    public function getById($id) {
        $stmt = $this->db->query("SELECT * FROM tokens WHERE id = ?", [$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * List tokens by parent
     */
    public function listByParent($parentId, $includeConsumed = false) {
        $sql = "SELECT * FROM tokens WHERE parent_id = ?";
        
        if (!$includeConsumed) {
            $sql .= " AND consumed_at IS NULL";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->query($sql, [$parentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Revoke token
     */
    public function revoke($tokenId) {
        $stmt = $this->db->query(
            "UPDATE tokens SET consumed_at = ? WHERE id = ? AND consumed_at IS NULL",
            [time(), $tokenId]
        );
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Get token statistics
     */
    public function getStats($parentId = null) {
        $where = $parentId ? "WHERE parent_id = ?" : "";
        $params = $parentId ? [$parentId] : [];
        
        $stmt = $this->db->query(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN consumed_at IS NULL THEN 1 ELSE 0 END) as unused,
                SUM(CASE WHEN consumed_at IS NOT NULL THEN 1 ELSE 0 END) as used,
                SUM(CASE WHEN expiry_date < ? THEN 1 ELSE 0 END) as expired
             FROM tokens {$where}",
            array_merge([time()], $params)
        );
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

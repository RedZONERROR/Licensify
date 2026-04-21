<?php
/**
 * Virtual Currency Manager
 */

class Currency {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Add balance to vendor
     */
    public function addVendorBalance($vendorId, $amount, $description = 'Balance added') {
        $this->db->beginTransaction();
        
        try {
            // Get current balance
            $stmt = $this->db->query("SELECT balance FROM vendors WHERE id = ?", [$vendorId]);
            $vendor = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$vendor) {
                throw new Exception('Vendor not found');
            }
            
            $newBalance = $vendor['balance'] + $amount;
            
            // Update balance
            $this->db->query(
                "UPDATE vendors SET balance = ?, updated_at = ? WHERE id = ?",
                [$newBalance, time(), $vendorId]
            );
            
            // Log transaction
            $this->db->query(
                "INSERT INTO currency_transactions (vendor_id, amount, type, description, balance_after, created_at)
                 VALUES (?, ?, 'credit', ?, ?, ?)",
                [$vendorId, $amount, $description, $newBalance, time()]
            );
            
            $this->db->commit();
            return $newBalance;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Deduct balance from vendor
     */
    public function deductVendorBalance($vendorId, $amount, $description = 'Balance deducted') {
        $this->db->beginTransaction();
        
        try {
            // Get current balance and unlimited status
            $stmt = $this->db->query(
                "SELECT balance, is_unlimited FROM vendors WHERE id = ?",
                [$vendorId]
            );
            $vendor = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$vendor) {
                throw new Exception('Vendor not found');
            }
            
            // Check if unlimited
            if ($vendor['is_unlimited']) {
                // No deduction needed
                $this->db->commit();
                return $vendor['balance'];
            }
            
            // Check sufficient balance
            if ($vendor['balance'] < $amount) {
                throw new Exception('Insufficient balance');
            }
            
            $newBalance = $vendor['balance'] - $amount;
            
            // Update balance
            $this->db->query(
                "UPDATE vendors SET balance = ?, updated_at = ? WHERE id = ?",
                [$newBalance, time(), $vendorId]
            );
            
            // Log transaction
            $this->db->query(
                "INSERT INTO currency_transactions (vendor_id, amount, type, description, balance_after, created_at)
                 VALUES (?, ?, 'debit', ?, ?, ?)",
                [$vendorId, $amount, $description, $newBalance, time()]
            );
            
            $this->db->commit();
            return $newBalance;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Add balance to reseller (vendor DB)
     */
    public function addResellerBalance($resellerId, $amount, $description = 'Balance added') {
        $this->db->beginTransaction();
        
        try {
            // Get current balance
            $stmt = $this->db->query("SELECT balance FROM resellers WHERE id = ?", [$resellerId]);
            $reseller = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$reseller) {
                throw new Exception('Reseller not found');
            }
            
            $newBalance = $reseller['balance'] + $amount;
            
            // Update balance
            $this->db->query(
                "UPDATE resellers SET balance = ?, updated_at = ? WHERE id = ?",
                [$newBalance, time(), $resellerId]
            );
            
            // Log transaction
            $this->db->query(
                "INSERT INTO reseller_transactions (reseller_id, amount, type, description, balance_after, created_at)
                 VALUES (?, ?, 'credit', ?, ?, ?)",
                [$resellerId, $amount, $description, $newBalance, time()]
            );
            
            $this->db->commit();
            return $newBalance;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Deduct balance from reseller (vendor DB)
     */
    public function deductResellerBalance($resellerId, $amount, $description = 'Balance deducted') {
        $this->db->beginTransaction();
        
        try {
            // Get current balance and unlimited status
            $stmt = $this->db->query(
                "SELECT balance, is_unlimited FROM resellers WHERE id = ?",
                [$resellerId]
            );
            $reseller = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$reseller) {
                throw new Exception('Reseller not found');
            }
            
            // Check if unlimited
            if ($reseller['is_unlimited']) {
                // No deduction needed
                $this->db->commit();
                return $reseller['balance'];
            }
            
            // Check sufficient balance
            if ($reseller['balance'] < $amount) {
                throw new Exception('Insufficient balance');
            }
            
            $newBalance = $reseller['balance'] - $amount;
            
            // Update balance
            $this->db->query(
                "UPDATE resellers SET balance = ?, updated_at = ? WHERE id = ?",
                [$newBalance, time(), $resellerId]
            );
            
            // Log transaction
            $this->db->query(
                "INSERT INTO reseller_transactions (reseller_id, amount, type, description, balance_after, created_at)
                 VALUES (?, ?, 'debit', ?, ?, ?)",
                [$resellerId, $amount, $description, $newBalance, time()]
            );
            
            $this->db->commit();
            return $newBalance;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Calculate license price
     */
    public function calculateLicensePrice($basePrice, $deviceCount, $duration, $discountRate = 0) {
        // Get duration multiplier
        $durationSeconds = LICENSE_DURATIONS[$duration] ?? 86400;
        $durationMultiplier = $durationSeconds / 86400; // Days
        
        // Calculate price
        $price = $basePrice * $deviceCount * $durationMultiplier;
        
        // Apply discount
        if ($discountRate > 0) {
            $price = $price * (1 - ($discountRate / 100));
        }
        
        return round($price, 2);
    }
    
    /**
     * Get vendor balance
     */
    public function getVendorBalance($vendorId) {
        $stmt = $this->db->query(
            "SELECT balance, is_unlimited FROM vendors WHERE id = ?",
            [$vendorId]
        );
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get reseller balance
     */
    public function getResellerBalance($resellerId) {
        $stmt = $this->db->query(
            "SELECT balance, is_unlimited FROM resellers WHERE id = ?",
            [$resellerId]
        );
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

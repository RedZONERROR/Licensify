<?php
/**
 * Product Model (Owner Database)
 * Manages global products
 */

class Product {
    private $db;
    
    public function __construct() {
        $this->db = new Database('owner');
    }
    
    /**
     * Create product
     */
    public function create($data) {
        $now = time();
        
        $stmt = $this->db->query(
            "INSERT INTO products (name, description, base_price, is_active, created_at, updated_at)
             VALUES (?, ?, ?, 1, ?, ?)",
            [
                $data['name'],
                $data['description'] ?? '',
                $data['base_price'],
                $now,
                $now
            ]
        );
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Get product by ID
     */
    public function getById($id) {
        $stmt = $this->db->query("SELECT * FROM products WHERE id = ?", [$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * List products
     */
    public function list($activeOnly = true) {
        $sql = "SELECT * FROM products";
        
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        
        $sql .= " ORDER BY name ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Update product
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = ['name', 'description', 'base_price', 'is_active'];
        
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
        
        $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->query($sql, $params);
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Delete product
     */
    public function delete($id) {
        // Soft delete
        $stmt = $this->db->query(
            "UPDATE products SET is_active = 0, updated_at = ? WHERE id = ?",
            [time(), $id]
        );
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Sync product to vendor
     */
    public function syncToVendor($productId, $vendorId) {
        $product = $this->getById($productId);
        
        if (!$product) {
            return false;
        }
        
        $vendorModel = new Vendor();
        $vendor = $vendorModel->getById($vendorId);
        
        if (!$vendor) {
            return false;
        }
        
        // Add to vendor products
        $stmt = $this->db->query(
            "INSERT OR REPLACE INTO vendor_products 
             (vendor_id, product_id, name, description, base_price, is_custom, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, 0, 1, ?, ?)",
            [
                $vendorId,
                $productId,
                $product['name'],
                $product['description'],
                $product['base_price'],
                time(),
                time()
            ]
        );
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Sync all products to vendor
     */
    public function syncAllToVendor($vendorId) {
        $products = $this->list(true);
        $synced = 0;
        
        foreach ($products as $product) {
            if ($this->syncToVendor($product['id'], $vendorId)) {
                $synced++;
            }
        }
        
        return $synced;
    }
    
    /**
     * Get vendor products
     */
    public function getVendorProducts($vendorId, $includeCustom = true) {
        $sql = "SELECT * FROM vendor_products WHERE vendor_id = ? AND is_active = 1";
        
        if (!$includeCustom) {
            $sql .= " AND is_custom = 0";
        }
        
        $sql .= " ORDER BY name ASC";
        
        $stmt = $this->db->query($sql, [$vendorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create custom vendor product
     */
    public function createVendorProduct($vendorId, $data) {
        $now = time();
        
        $stmt = $this->db->query(
            "INSERT INTO vendor_products 
             (vendor_id, product_id, name, description, base_price, is_custom, is_active, created_at, updated_at)
             VALUES (?, NULL, ?, ?, ?, 1, 1, ?, ?)",
            [
                $vendorId,
                $data['name'],
                $data['description'] ?? '',
                $data['base_price'],
                $now,
                $now
            ]
        );
        
        return $this->db->lastInsertId();
    }
}

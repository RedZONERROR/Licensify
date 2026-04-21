<?php
/**
 * License Controller
 * Handles license generation, validation, and management
 */

class LicenseController {
    private $vendorCode;
    private $licenseModel;
    private $currency;
    
    public function __construct($vendorCode) {
        $this->vendorCode = $vendorCode;
        $this->licenseModel = new License($vendorCode);
        $this->currency = new Currency(new Database($vendorCode));
    }
    
    /**
     * Generate license
     */
    public function generate($data) {
        // Validate input
        $validator = new Validator($data);
        $validator->required('product_id')
                  ->required('product_name')
                  ->required('base_price')
                  ->required('duration')
                  ->in('duration', array_keys(LICENSE_DURATIONS))
                  ->numeric('max_devices')
                  ->numeric('base_price');
        
        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors()];
        }
        
        try {
            // Check balance if reseller
            if (isset($data['reseller_id']) && $data['reseller_id']) {
                $balance = $this->currency->getResellerBalance($data['reseller_id']);
                
                if (!$balance['is_unlimited']) {
                    $price = $this->currency->calculateLicensePrice(
                        $data['base_price'],
                        $data['max_devices'] ?? 1,
                        $data['duration'],
                        $data['discount_rate'] ?? 0
                    );
                    
                    if ($balance['balance'] < $price) {
                        return ['success' => false, 'message' => 'Insufficient balance'];
                    }
                }
            }
            
            $license = $this->licenseModel->generate($data);
            
            return [
                'success' => true,
                'message' => 'License generated successfully',
                'data' => $license
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Validate license
     */
    public function validate($licenseKey, $hwid = null) {
        try {
            $result = $this->licenseModel->validate($licenseKey, $hwid);
            
            if ($result['valid']) {
                return [
                    'success' => true,
                    'message' => $result['message'],
                    'data' => [
                        'license_key' => $licenseKey,
                        'product_name' => $result['license']['product_name'],
                        'expires_at' => $result['license']['expires_at'],
                        'max_devices' => $result['license']['max_devices']
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $result['message']
                ];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get license details
     */
    public function getDetails($licenseKey) {
        try {
            $license = $this->licenseModel->getByKey($licenseKey);
            
            if (!$license) {
                return ['success' => false, 'message' => 'License not found'];
            }
            
            $devices = $this->licenseModel->getDevices($license['id']);
            
            return [
                'success' => true,
                'data' => [
                    'license' => $license,
                    'devices' => $devices
                ]
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * List licenses
     */
    public function list($filters = []) {
        try {
            $licenses = $this->licenseModel->list($filters);
            
            return [
                'success' => true,
                'data' => $licenses
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Suspend license
     */
    public function suspend($licenseKey) {
        try {
            $license = $this->licenseModel->getByKey($licenseKey);
            
            if (!$license) {
                return ['success' => false, 'message' => 'License not found'];
            }
            
            $this->licenseModel->suspend($license['id']);
            
            return [
                'success' => true,
                'message' => 'License suspended successfully'
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Unsuspend license
     */
    public function unsuspend($licenseKey) {
        try {
            $license = $this->licenseModel->getByKey($licenseKey);
            
            if (!$license) {
                return ['success' => false, 'message' => 'License not found'];
            }
            
            $this->licenseModel->unsuspend($license['id']);
            
            return [
                'success' => true,
                'message' => 'License unsuspended successfully'
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Suspend device
     */
    public function suspendDevice($licenseKey, $hwid) {
        try {
            $license = $this->licenseModel->getByKey($licenseKey);
            
            if (!$license) {
                return ['success' => false, 'message' => 'License not found'];
            }
            
            $this->licenseModel->suspendDevice($license['id'], $hwid);
            
            return [
                'success' => true,
                'message' => 'Device suspended successfully'
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Unsuspend device
     */
    public function unsuspendDevice($licenseKey, $hwid) {
        try {
            $license = $this->licenseModel->getByKey($licenseKey);
            
            if (!$license) {
                return ['success' => false, 'message' => 'License not found'];
            }
            
            $this->licenseModel->unsuspendDevice($license['id'], $hwid);
            
            return [
                'success' => true,
                'message' => 'Device unsuspended successfully'
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get statistics
     */
    public function getStats($resellerId = null) {
        try {
            $stats = $this->licenseModel->getStats($resellerId);
            
            return [
                'success' => true,
                'data' => $stats
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

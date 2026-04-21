<?php
/**
 * Authentication Controller
 * Handles login, registration, 2FA
 */

class AuthController {
    private $db;
    private $encryption;
    private $totp;
    
    public function __construct() {
        $this->db = new Database('owner');
        $this->encryption = new Encryption();
        $this->totp = new TOTP();
    }
    
    /**
     * User login
     */
    public function login($email, $password, $totpCode = null) {
        // Get user
        $stmt = $this->db->query("SELECT * FROM users WHERE email = ?", [$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !$this->encryption->verifyPassword($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }
        
        if (!$user['is_active']) {
            return ['success' => false, 'message' => 'Account is inactive'];
        }
        
        // Check 2FA if enabled
        if ($user['totp_enabled']) {
            if (!$totpCode) {
                return ['success' => false, 'message' => '2FA code required', 'requires_2fa' => true];
            }
            
            if (!$this->totp->verifyCode($user['totp_secret'], $totpCode)) {
                return ['success' => false, 'message' => 'Invalid 2FA code'];
            }
        }
        
        // Generate session token
        $sessionToken = $this->encryption->generateToken(32);
        
        // Store session (in production, use proper session management)
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['session_token'] = $sessionToken;
        
        // Log activity
        $this->logActivity($user['id'], 'login', ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
        
        return [
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role'],
                'vendor_id' => $user['vendor_id']
            ],
            'token' => $sessionToken
        ];
    }
    
    /**
     * Register with token
     */
    public function registerWithToken($tokenString, $email, $password, $additionalData = []) {
        // Validate token
        $tokenModel = new Token();
        $tokenValidation = $tokenModel->validate($tokenString);
        
        if (!$tokenValidation['valid']) {
            return ['success' => false, 'message' => $tokenValidation['message']];
        }
        
        $tokenData = $tokenValidation['data'];
        
        // Check if email exists
        $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE email = ?", [$email]);
        if ($stmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Email already registered'];
        }
        
        $this->db->beginTransaction();
        
        try {
            $now = time();
            $hashedPassword = $this->encryption->hashPassword($password);
            
            // Create user
            $stmt = $this->db->query(
                "INSERT INTO users (email, password, role, is_active, created_at, updated_at)
                 VALUES (?, ?, 'user', 1, ?, ?)",
                [$email, $hashedPassword, $now, $now]
            );
            
            $userId = $this->db->lastInsertId();
            
            // Consume token
            $tokenModel->consume($tokenString, $userId);
            
            // Upgrade account based on token role
            if ($tokenData['role'] === 'vendor') {
                $vendorModel = new Vendor();
                $vendor = $vendorModel->createFromToken(
                    $userId,
                    $tokenData,
                    $additionalData['company_name'] ?? 'Unnamed Company'
                );
                
                $result = [
                    'user_id' => $userId,
                    'role' => 'vendor',
                    'vendor_id' => $vendor['id'],
                    'vendor_code' => $vendor['vendor_code']
                ];
            } else {
                // Reseller - need vendor context
                if (!isset($additionalData['vendor_code'])) {
                    throw new Exception('Vendor code required for reseller registration');
                }
                
                $resellerModel = new Reseller($additionalData['vendor_code']);
                $reseller = $resellerModel->createFromToken(
                    $userId,
                    $tokenData,
                    $additionalData['name'] ?? $email,
                    $email
                );
                
                $result = [
                    'user_id' => $userId,
                    'role' => 'reseller',
                    'reseller_id' => $reseller['id'],
                    'reseller_code' => $reseller['reseller_code']
                ];
            }
            
            $this->db->commit();
            
            // Log activity
            $this->logActivity($userId, 'register', ['token_role' => $tokenData['role']]);
            
            return [
                'success' => true,
                'message' => 'Registration successful',
                'data' => $result
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Enable 2FA
     */
    public function enable2FA($userId) {
        $secret = $this->totp->generateSecret();
        
        // Get user email for QR code
        $stmt = $this->db->query("SELECT email FROM users WHERE id = ?", [$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }
        
        // Store secret (not enabled yet)
        $stmt = $this->db->query(
            "UPDATE users SET totp_secret = ?, updated_at = ? WHERE id = ?",
            [$secret, time(), $userId]
        );
        
        return [
            'success' => true,
            'secret' => $secret,
            'qr_code_url' => $this->totp->getQRCodeUrl($secret, $user['email']),
            'provisioning_uri' => $this->totp->getProvisioningUri($secret, $user['email'])
        ];
    }
    
    /**
     * Verify and activate 2FA
     */
    public function verify2FA($userId, $code) {
        $stmt = $this->db->query("SELECT totp_secret FROM users WHERE id = ?", [$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !$user['totp_secret']) {
            return ['success' => false, 'message' => '2FA not initialized'];
        }
        
        if (!$this->totp->verifyCode($user['totp_secret'], $code)) {
            return ['success' => false, 'message' => 'Invalid code'];
        }
        
        // Enable 2FA
        $stmt = $this->db->query(
            "UPDATE users SET totp_enabled = 1, updated_at = ? WHERE id = ?",
            [time(), $userId]
        );
        
        $this->logActivity($userId, '2fa_enabled');
        
        return ['success' => true, 'message' => '2FA enabled successfully'];
    }
    
    /**
     * Disable 2FA
     */
    public function disable2FA($userId, $password) {
        // Verify password
        $stmt = $this->db->query("SELECT password FROM users WHERE id = ?", [$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !$this->encryption->verifyPassword($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid password'];
        }
        
        // Disable 2FA
        $stmt = $this->db->query(
            "UPDATE users SET totp_enabled = 0, totp_secret = NULL, updated_at = ? WHERE id = ?",
            [time(), $userId]
        );
        
        $this->logActivity($userId, '2fa_disabled');
        
        return ['success' => true, 'message' => '2FA disabled successfully'];
    }
    
    /**
     * Logout
     */
    public function logout($userId) {
        $this->logActivity($userId, 'logout');
        
        session_destroy();
        
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
    
    /**
     * Log activity
     */
    private function logActivity($userId, $action, $details = []) {
        $compressed = Compression::compress($details);
        
        $this->db->query(
            "INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent, created_at)
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                $userId,
                $action,
                $compressed,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                time()
            ]
        );
    }
}

<?php
/**
 * Pure PHP Encryption Class (XTEA Algorithm)
 * No OpenSSL dependency - works on shared hosting
 */

class Encryption {
    private $key;
    private $rounds = 32;
    
    public function __construct($key = null) {
        $this->key = $key ?: ENCRYPTION_KEY;
        
        // Ensure key is exactly 16 bytes (128 bits)
        $this->key = substr(hash('sha256', $this->key, true), 0, 16);
    }
    
    /**
     * Encrypt data using XTEA
     */
    public function encrypt($data) {
        if (empty($data)) {
            return '';
        }
        
        // Convert data to string if needed
        if (!is_string($data)) {
            $data = json_encode($data);
        }
        
        // Generate random IV (8 bytes)
        $iv = $this->generateRandomBytes(8);
        
        // Pad data to 8-byte blocks
        $data = $this->pkcs7Pad($data, 8);
        
        // Encrypt in 8-byte blocks
        $encrypted = '';
        $blocks = str_split($data, 8);
        
        foreach ($blocks as $block) {
            $encrypted .= $this->xteaEncryptBlock($block);
        }
        
        // Prepend IV and encode
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt data using XTEA
     */
    public function decrypt($data) {
        if (empty($data)) {
            return '';
        }
        
        // Decode from base64
        $data = base64_decode($data);
        
        if ($data === false || strlen($data) < 8) {
            return false;
        }
        
        // Extract IV (first 8 bytes)
        $iv = substr($data, 0, 8);
        $encrypted = substr($data, 8);
        
        // Decrypt in 8-byte blocks
        $decrypted = '';
        $blocks = str_split($encrypted, 8);
        
        foreach ($blocks as $block) {
            $decrypted .= $this->xteaDecryptBlock($block);
        }
        
        // Remove padding
        return $this->pkcs7Unpad($decrypted);
    }
    
    /**
     * XTEA encrypt single 8-byte block
     */
    private function xteaEncryptBlock($block) {
        // Convert to two 32-bit integers
        $v = array_values(unpack('V2', str_pad($block, 8, "\0")));
        $k = array_values(unpack('V4', $this->key));
        
        $v0 = $v[0];
        $v1 = $v[1];
        $sum = 0;
        $delta = 0x9E3779B9;
        
        for ($i = 0; $i < $this->rounds; $i++) {
            $v0 += ((($v1 << 4) ^ ($v1 >> 5)) + $v1) ^ ($sum + $k[$sum & 3]);
            $v0 &= 0xFFFFFFFF;
            $sum += $delta;
            $sum &= 0xFFFFFFFF;
            $v1 += ((($v0 << 4) ^ ($v0 >> 5)) + $v0) ^ ($sum + $k[($sum >> 11) & 3]);
            $v1 &= 0xFFFFFFFF;
        }
        
        return pack('VV', $v0, $v1);
    }
    
    /**
     * XTEA decrypt single 8-byte block
     */
    private function xteaDecryptBlock($block) {
        // Convert to two 32-bit integers
        $v = array_values(unpack('V2', $block));
        $k = array_values(unpack('V4', $this->key));
        
        $v0 = $v[0];
        $v1 = $v[1];
        $delta = 0x9E3779B9;
        $sum = ($delta * $this->rounds) & 0xFFFFFFFF;
        
        for ($i = 0; $i < $this->rounds; $i++) {
            $v1 -= ((($v0 << 4) ^ ($v0 >> 5)) + $v0) ^ ($sum + $k[($sum >> 11) & 3]);
            $v1 &= 0xFFFFFFFF;
            $sum -= $delta;
            $sum &= 0xFFFFFFFF;
            $v0 -= ((($v1 << 4) ^ ($v1 >> 5)) + $v1) ^ ($sum + $k[$sum & 3]);
            $v0 &= 0xFFFFFFFF;
        }
        
        return pack('VV', $v0, $v1);
    }
    
    /**
     * PKCS7 padding
     */
    private function pkcs7Pad($data, $blockSize) {
        $padding = $blockSize - (strlen($data) % $blockSize);
        return $data . str_repeat(chr($padding), $padding);
    }
    
    /**
     * Remove PKCS7 padding
     */
    private function pkcs7Unpad($data) {
        $length = strlen($data);
        if ($length == 0) {
            return '';
        }
        
        $padding = ord($data[$length - 1]);
        
        if ($padding > $length) {
            return false;
        }
        
        return substr($data, 0, $length - $padding);
    }
    
    /**
     * Generate cryptographically secure random bytes
     */
    private function generateRandomBytes($length) {
        if (function_exists('random_bytes')) {
            return random_bytes($length);
        }
        
        // Fallback for older PHP versions
        $bytes = '';
        for ($i = 0; $i < $length; $i++) {
            $bytes .= chr(mt_rand(0, 255));
        }
        return $bytes;
    }
    
    /**
     * Hash password (using bcrypt)
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * Verify password
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Generate secure token
     */
    public function generateToken($length = 32) {
        $bytes = $this->generateRandomBytes($length);
        return bin2hex($bytes);
    }
    
    /**
     * Generate license key
     */
    public function generateLicenseKey($format = 'XXXX-XXXX-XXXX-XXXX') {
        $key = '';
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Removed ambiguous chars
        
        for ($i = 0; $i < strlen($format); $i++) {
            if ($format[$i] === 'X') {
                $key .= $chars[random_int(0, strlen($chars) - 1)];
            } else {
                $key .= $format[$i];
            }
        }
        
        return $key;
    }
}

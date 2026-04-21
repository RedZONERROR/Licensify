<?php
/**
 * TOTP (Time-based One-Time Password) Implementation
 * Compatible with Google Authenticator
 * Pure PHP - No external dependencies
 */

class TOTP {
    private $period = 30; // 30 seconds
    private $digits = 6;
    private $algorithm = 'sha1';
    
    /**
     * Generate a new secret key
     */
    public function generateSecret($length = 16) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; // Base32 alphabet
        $secret = '';
        
        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        return $secret;
    }
    
    /**
     * Generate TOTP code
     */
    public function generateCode($secret, $timestamp = null) {
        if ($timestamp === null) {
            $timestamp = time();
        }
        
        $timeCounter = floor($timestamp / $this->period);
        
        return $this->generateHOTP($secret, $timeCounter);
    }
    
    /**
     * Verify TOTP code
     */
    public function verifyCode($secret, $code, $discrepancy = 1) {
        $timestamp = time();
        $timeCounter = floor($timestamp / $this->period);
        
        // Check current and adjacent time windows
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $generatedCode = $this->generateHOTP($secret, $timeCounter + $i);
            
            if ($this->constantTimeCompare($code, $generatedCode)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Generate HOTP (HMAC-based One-Time Password)
     */
    private function generateHOTP($secret, $counter) {
        // Decode base32 secret
        $secretKey = $this->base32Decode($secret);
        
        // Convert counter to binary string (8 bytes, big-endian)
        $counterBinary = pack('N*', 0, $counter);
        
        // Generate HMAC
        $hash = hash_hmac($this->algorithm, $counterBinary, $secretKey, true);
        
        // Dynamic truncation
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $truncatedHash = substr($hash, $offset, 4);
        
        // Convert to integer
        $value = unpack('N', $truncatedHash)[1];
        $value = $value & 0x7FFFFFFF;
        
        // Generate code
        $code = $value % pow(10, $this->digits);
        
        return str_pad($code, $this->digits, '0', STR_PAD_LEFT);
    }
    
    /**
     * Base32 decode
     */
    private function base32Decode($input) {
        $input = strtoupper($input);
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $output = '';
        $buffer = 0;
        $bitsLeft = 0;
        
        for ($i = 0; $i < strlen($input); $i++) {
            $char = $input[$i];
            
            if ($char === '=') {
                break;
            }
            
            $value = strpos($alphabet, $char);
            
            if ($value === false) {
                continue;
            }
            
            $buffer = ($buffer << 5) | $value;
            $bitsLeft += 5;
            
            if ($bitsLeft >= 8) {
                $output .= chr(($buffer >> ($bitsLeft - 8)) & 0xFF);
                $bitsLeft -= 8;
            }
        }
        
        return $output;
    }
    
    /**
     * Constant time string comparison
     */
    private function constantTimeCompare($str1, $str2) {
        if (function_exists('hash_equals')) {
            return hash_equals($str1, $str2);
        }
        
        if (strlen($str1) !== strlen($str2)) {
            return false;
        }
        
        $result = 0;
        for ($i = 0; $i < strlen($str1); $i++) {
            $result |= ord($str1[$i]) ^ ord($str2[$i]);
        }
        
        return $result === 0;
    }
    
    /**
     * Generate QR code URL for Google Authenticator
     */
    public function getQRCodeUrl($secret, $label, $issuer = 'Licensify') {
        $label = rawurlencode($label);
        $issuer = rawurlencode($issuer);
        
        $otpauthUrl = "otpauth://totp/{$issuer}:{$label}?secret={$secret}&issuer={$issuer}";
        
        // Use Google Charts API for QR code
        return "https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=" . urlencode($otpauthUrl);
    }
    
    /**
     * Get provisioning URI
     */
    public function getProvisioningUri($secret, $label, $issuer = 'Licensify') {
        $label = rawurlencode($label);
        $issuer = rawurlencode($issuer);
        
        return "otpauth://totp/{$issuer}:{$label}?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30";
    }
}

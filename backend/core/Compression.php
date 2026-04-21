<?php
/**
 * Data Compression Helper
 * Uses gzcompress for efficient storage
 */

class Compression {
    /**
     * Compress data
     */
    public static function compress($data, $level = 6) {
        if (empty($data)) {
            return '';
        }
        
        // Convert to JSON if array/object
        if (is_array($data) || is_object($data)) {
            $data = json_encode($data);
        }
        
        // Compress
        $compressed = gzcompress($data, $level);
        
        if ($compressed === false) {
            return $data; // Return original if compression fails
        }
        
        return $compressed;
    }
    
    /**
     * Decompress data
     */
    public static function decompress($data, $asArray = false) {
        if (empty($data)) {
            return $asArray ? [] : '';
        }
        
        // Try to decompress
        $decompressed = @gzuncompress($data);
        
        if ($decompressed === false) {
            // Not compressed, return as is
            $decompressed = $data;
        }
        
        // Try to decode JSON
        if ($asArray) {
            $decoded = json_decode($decompressed, true);
            return $decoded !== null ? $decoded : [];
        }
        
        return $decompressed;
    }
    
    /**
     * Check if data is compressed
     */
    public static function isCompressed($data) {
        if (empty($data) || strlen($data) < 2) {
            return false;
        }
        
        // Check gzip magic number
        return ord($data[0]) === 0x1f && ord($data[1]) === 0x8b;
    }
    
    /**
     * Get compression ratio
     */
    public static function getCompressionRatio($original, $compressed) {
        $originalSize = strlen($original);
        $compressedSize = strlen($compressed);
        
        if ($originalSize === 0) {
            return 0;
        }
        
        return round((1 - ($compressedSize / $originalSize)) * 100, 2);
    }
}

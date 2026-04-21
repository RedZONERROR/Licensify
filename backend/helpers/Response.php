<?php
/**
 * API Response Helper
 */

class Response {
    /**
     * Send JSON response
     */
    public static function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Success response
     */
    public static function success($data = null, $message = 'Success', $status = 200) {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }
    
    /**
     * Error response
     */
    public static function error($message, $status = 400, $errors = null) {
        self::json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $status);
    }
    
    /**
     * Validation error response
     */
    public static function validationError($errors) {
        self::error('Validation failed', 422, $errors);
    }
    
    /**
     * Unauthorized response
     */
    public static function unauthorized($message = 'Unauthorized') {
        self::error($message, 401);
    }
    
    /**
     * Forbidden response
     */
    public static function forbidden($message = 'Forbidden') {
        self::error($message, 403);
    }
    
    /**
     * Not found response
     */
    public static function notFound($message = 'Resource not found') {
        self::error($message, 404);
    }
    
    /**
     * Server error response
     */
    public static function serverError($message = 'Internal server error') {
        self::error($message, 500);
    }
}

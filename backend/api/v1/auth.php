<?php
/**
 * Authentication API Endpoints
 */

require_once '../../config/app.php';

// Start session
session_start();

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Handle OPTIONS for CORS
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get request data
$input = json_decode(file_get_contents('php://input'), true) ?? [];

// Get endpoint
$endpoint = $_GET['endpoint'] ?? '';

$authController = new AuthController();

switch ($endpoint) {
    case 'login':
        if ($method !== 'POST') {
            Response::error('Method not allowed', 405);
        }
        
        $validator = new Validator($input);
        $validator->required('email')->email('email')
                  ->required('password');
        
        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }
        
        $result = $authController->login(
            $input['email'],
            $input['password'],
            $input['totp_code'] ?? null
        );
        
        if ($result['success']) {
            Response::success($result);
        } else {
            Response::error($result['message'], 401);
        }
        break;
    
    case 'register':
        if ($method !== 'POST') {
            Response::error('Method not allowed', 405);
        }
        
        $validator = new Validator($input);
        $validator->required('token')
                  ->required('email')->email('email')
                  ->required('password')->min('password', 8);
        
        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }
        
        $result = $authController->registerWithToken(
            $input['token'],
            $input['email'],
            $input['password'],
            $input
        );
        
        if ($result['success']) {
            Response::success($result['data'], $result['message'], 201);
        } else {
            Response::error($result['message']);
        }
        break;
    
    case '2fa/enable':
        if ($method !== 'POST') {
            Response::error('Method not allowed', 405);
        }
        
        if (!isset($_SESSION['user_id'])) {
            Response::unauthorized();
        }
        
        $result = $authController->enable2FA($_SESSION['user_id']);
        
        if ($result['success']) {
            Response::success($result);
        } else {
            Response::error($result['message']);
        }
        break;
    
    case '2fa/verify':
        if ($method !== 'POST') {
            Response::error('Method not allowed', 405);
        }
        
        if (!isset($_SESSION['user_id'])) {
            Response::unauthorized();
        }
        
        $validator = new Validator($input);
        $validator->required('code');
        
        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }
        
        $result = $authController->verify2FA($_SESSION['user_id'], $input['code']);
        
        if ($result['success']) {
            Response::success(null, $result['message']);
        } else {
            Response::error($result['message']);
        }
        break;
    
    case '2fa/disable':
        if ($method !== 'POST') {
            Response::error('Method not allowed', 405);
        }
        
        if (!isset($_SESSION['user_id'])) {
            Response::unauthorized();
        }
        
        $validator = new Validator($input);
        $validator->required('password');
        
        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }
        
        $result = $authController->disable2FA($_SESSION['user_id'], $input['password']);
        
        if ($result['success']) {
            Response::success(null, $result['message']);
        } else {
            Response::error($result['message']);
        }
        break;
    
    case 'logout':
        if ($method !== 'POST') {
            Response::error('Method not allowed', 405);
        }
        
        if (!isset($_SESSION['user_id'])) {
            Response::unauthorized();
        }
        
        $result = $authController->logout($_SESSION['user_id']);
        Response::success(null, $result['message']);
        break;
    
    default:
        Response::notFound('Endpoint not found');
}

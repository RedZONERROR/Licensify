<?php
/**
 * License API Endpoints
 */

require_once '../../config/app.php';

// Start session
session_start();

// Check authentication
if (!isset($_SESSION['user_id'])) {
    Response::unauthorized();
}

// Get vendor code (from session or request)
$vendorCode = $_GET['vendor_code'] ?? $_SESSION['vendor_code'] ?? null;

if (!$vendorCode) {
    Response::error('Vendor code required');
}

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

$licenseController = new LicenseController($vendorCode);

switch ($endpoint) {
    case 'generate':
        if ($method !== 'POST') {
            Response::error('Method not allowed', 405);
        }
        
        $result = $licenseController->generate($input);
        
        if ($result['success']) {
            Response::success($result['data'], $result['message'], 201);
        } else {
            if (isset($result['errors'])) {
                Response::validationError($result['errors']);
            } else {
                Response::error($result['message']);
            }
        }
        break;
    
    case 'validate':
        if ($method !== 'POST') {
            Response::error('Method not allowed', 405);
        }
        
        $validator = new Validator($input);
        $validator->required('license_key');
        
        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }
        
        $result = $licenseController->validate(
            $input['license_key'],
            $input['hwid'] ?? null
        );
        
        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
        break;
    
    case 'details':
        if ($method !== 'GET') {
            Response::error('Method not allowed', 405);
        }
        
        $licenseKey = $_GET['license_key'] ?? null;
        
        if (!$licenseKey) {
            Response::error('License key required');
        }
        
        $result = $licenseController->getDetails($licenseKey);
        
        if ($result['success']) {
            Response::success($result['data']);
        } else {
            Response::notFound($result['message']);
        }
        break;
    
    case 'list':
        if ($method !== 'GET') {
            Response::error('Method not allowed', 405);
        }
        
        $filters = [
            'reseller_id' => $_GET['reseller_id'] ?? null,
            'is_active' => $_GET['is_active'] ?? null,
            'search' => $_GET['search'] ?? null,
            'limit' => $_GET['limit'] ?? 50,
            'offset' => $_GET['offset'] ?? 0
        ];
        
        $result = $licenseController->list($filters);
        
        if ($result['success']) {
            Response::success($result['data']);
        } else {
            Response::error($result['message']);
        }
        break;
    
    case 'suspend':
        if ($method !== 'POST') {
            Response::error('Method not allowed', 405);
        }
        
        $validator = new Validator($input);
        $validator->required('license_key');
        
        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }
        
        $result = $licenseController->suspend($input['license_key']);
        
        if ($result['success']) {
            Response::success(null, $result['message']);
        } else {
            Response::error($result['message']);
        }
        break;
    
    case 'unsuspend':
        if ($method !== 'POST') {
            Response::error('Method not allowed', 405);
        }
        
        $validator = new Validator($input);
        $validator->required('license_key');
        
        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }
        
        $result = $licenseController->unsuspend($input['license_key']);
        
        if ($result['success']) {
            Response::success(null, $result['message']);
        } else {
            Response::error($result['message']);
        }
        break;
    
    case 'device/suspend':
        if ($method !== 'POST') {
            Response::error('Method not allowed', 405);
        }
        
        $validator = new Validator($input);
        $validator->required('license_key')->required('hwid');
        
        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }
        
        $result = $licenseController->suspendDevice($input['license_key'], $input['hwid']);
        
        if ($result['success']) {
            Response::success(null, $result['message']);
        } else {
            Response::error($result['message']);
        }
        break;
    
    case 'device/unsuspend':
        if ($method !== 'POST') {
            Response::error('Method not allowed', 405);
        }
        
        $validator = new Validator($input);
        $validator->required('license_key')->required('hwid');
        
        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }
        
        $result = $licenseController->unsuspendDevice($input['license_key'], $input['hwid']);
        
        if ($result['success']) {
            Response::success(null, $result['message']);
        } else {
            Response::error($result['message']);
        }
        break;
    
    case 'stats':
        if ($method !== 'GET') {
            Response::error('Method not allowed', 405);
        }
        
        $resellerId = $_GET['reseller_id'] ?? null;
        
        $result = $licenseController->getStats($resellerId);
        
        if ($result['success']) {
            Response::success($result['data']);
        } else {
            Response::error($result['message']);
        }
        break;
    
    default:
        Response::notFound('Endpoint not found');
}

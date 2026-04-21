<?php
/**
 * Application Configuration
 * Auto-detects paths and sets up environment
 */

// Auto-detect base paths
define('BASE_PATH', dirname(dirname(__DIR__)));
define('BACKEND_PATH', __DIR__ . '/..');
define('DATA_PATH', dirname(BASE_PATH) . '/licensify_data'); // Above web root
define('OWNER_DB_PATH', DATA_PATH . '/owner');
define('VENDOR_DB_PATH', DATA_PATH . '/vendors');

// Create data directories if they don't exist
if (!file_exists(DATA_PATH)) {
    mkdir(DATA_PATH, 0755, true);
}
if (!file_exists(OWNER_DB_PATH)) {
    mkdir(OWNER_DB_PATH, 0755, true);
}
if (!file_exists(VENDOR_DB_PATH)) {
    mkdir(VENDOR_DB_PATH, 0755, true);
}

// Application settings
define('APP_NAME', 'Licensify');
define('APP_VERSION', '1.0.0');
define('TIMEZONE', 'UTC');
date_default_timezone_set(TIMEZONE);

// Security settings
define('TOKEN_EXPIRY_BUFFER', 86400); // 24 hours buffer before hard expiry
define('SESSION_LIFETIME', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// License durations (in seconds)
define('LICENSE_DURATIONS', [
    '1hr' => 3600,
    '1d' => 86400,
    '7d' => 604800,
    '15d' => 1296000,
    '30d' => 2592000,
    '60d' => 5184000
]);

// Encryption key (should be generated and stored securely)
define('ENCRYPTION_KEY', getenv('LICENSIFY_ENCRYPTION_KEY') ?: 'CHANGE_THIS_TO_RANDOM_32_CHAR_KEY');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Autoloader
spl_autoload_register(function ($class) {
    $paths = [
        BACKEND_PATH . '/core/',
        BACKEND_PATH . '/models/Owner/',
        BACKEND_PATH . '/models/Vendor/',
        BACKEND_PATH . '/controllers/',
        BACKEND_PATH . '/helpers/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Helper functions
function response($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function error_response($message, $status = 400) {
    response(['error' => $message], $status);
}

function success_response($data, $message = 'Success') {
    response(['success' => true, 'message' => $message, 'data' => $data]);
}

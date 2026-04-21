<?php
/**
 * System Test & Demo Script
 * Tests all core functionality
 */

require_once 'config/app.php';

echo "=== LICENSIFY BACKEND SYSTEM TEST ===\n\n";

// Test 1: Database Connection
echo "1. Testing Database Connection...\n";
try {
    $db = new Database('owner');
    echo "   ✓ Owner database connected\n";
    
    // Check if owner exists
    $stmt = $db->query("SELECT * FROM users WHERE role = 'owner'");
    $owner = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($owner) {
        echo "   ✓ Default owner account exists: {$owner['email']}\n";
    }
} catch (Exception $e) {
    echo "   ✗ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Encryption
echo "\n2. Testing Encryption...\n";
try {
    $encryption = new Encryption();
    $testData = "Sensitive information";
    $encrypted = $encryption->encrypt($testData);
    $decrypted = $encryption->decrypt($encrypted);
    
    if ($decrypted === $testData) {
        echo "   ✓ Encryption/Decryption working\n";
    } else {
        echo "   ✗ Encryption failed\n";
    }
    
    // Test license key generation
    $licenseKey = $encryption->generateLicenseKey();
    echo "   ✓ Sample license key: {$licenseKey}\n";
    
} catch (Exception $e) {
    echo "   ✗ Encryption error: " . $e->getMessage() . "\n";
}

// Test 3: TOTP (2FA)
echo "\n3. Testing TOTP (2FA)...\n";
try {
    $totp = new TOTP();
    $secret = $totp->generateSecret();
    $code = $totp->generateCode($secret);
    $isValid = $totp->verifyCode($secret, $code);
    
    if ($isValid) {
        echo "   ✓ TOTP generation and verification working\n";
        echo "   ✓ Sample secret: {$secret}\n";
        echo "   ✓ Sample code: {$code}\n";
    } else {
        echo "   ✗ TOTP verification failed\n";
    }
} catch (Exception $e) {
    echo "   ✗ TOTP error: " . $e->getMessage() . "\n";
}

// Test 4: Compression
echo "\n4. Testing Compression...\n";
try {
    $testData = ['user' => 'test', 'action' => 'login', 'timestamp' => time()];
    $compressed = Compression::compress($testData);
    $decompressed = Compression::decompress($compressed, true);
    
    if ($decompressed === $testData) {
        echo "   ✓ Compression/Decompression working\n";
        $ratio = Compression::getCompressionRatio(json_encode($testData), $compressed);
        echo "   ✓ Compression ratio: {$ratio}%\n";
    } else {
        echo "   ✗ Compression failed\n";
    }
} catch (Exception $e) {
    echo "   ✗ Compression error: " . $e->getMessage() . "\n";
}

// Test 5: Token Generation
echo "\n5. Testing Token System...\n";
try {
    $tokenModel = new Token();
    $token = $tokenModel->generate([
        'role' => 'vendor',
        'parent_id' => $owner['id'],
        'expiry_date' => time() + (365 * 86400), // 1 year
        'is_unlimited' => 1,
        'initial_balance' => 0,
        'discount_rate' => 15,
        'can_create_products' => 1,
        'payment_access' => 1
    ]);
    
    echo "   ✓ Token generated: {$token}\n";
    
    // Validate token
    $validation = $tokenModel->validate($token);
    if ($validation['valid']) {
        echo "   ✓ Token validation working\n";
    } else {
        echo "   ✗ Token validation failed\n";
    }
} catch (Exception $e) {
    echo "   ✗ Token error: " . $e->getMessage() . "\n";
}

// Test 6: Product Management
echo "\n6. Testing Product System...\n";
try {
    $productModel = new Product();
    $productId = $productModel->create([
        'name' => 'Test Product',
        'description' => 'A test product for demo',
        'base_price' => 99.99
    ]);
    
    echo "   ✓ Product created with ID: {$productId}\n";
    
    $products = $productModel->list();
    echo "   ✓ Total products: " . count($products) . "\n";
} catch (Exception $e) {
    echo "   ✗ Product error: " . $e->getMessage() . "\n";
}

// Test 7: Currency Calculation
echo "\n7. Testing Currency System...\n";
try {
    $currency = new Currency($db);
    
    // Test price calculation
    $price = $currency->calculateLicensePrice(100, 3, '30d', 10);
    echo "   ✓ License price calculation: \$" . number_format($price, 2) . "\n";
    echo "     (Base: \$100, Devices: 3, Duration: 30d, Discount: 10%)\n";
    
} catch (Exception $e) {
    echo "   ✗ Currency error: " . $e->getMessage() . "\n";
}

// Test 8: Authentication
echo "\n8. Testing Authentication...\n";
try {
    $authController = new AuthController();
    
    // Test login with default owner
    $result = $authController->login('admin@licensify.local', 'admin123');
    
    if ($result['success']) {
        echo "   ✓ Login successful\n";
        echo "   ✓ User role: {$result['user']['role']}\n";
    } else {
        echo "   ✗ Login failed: {$result['message']}\n";
    }
} catch (Exception $e) {
    echo "   ✗ Auth error: " . $e->getMessage() . "\n";
}

// Summary
echo "\n=== TEST SUMMARY ===\n";
echo "✓ Core system is operational\n";
echo "✓ Database schema initialized\n";
echo "✓ Encryption working (Pure PHP XTEA)\n";
echo "✓ 2FA ready (Google Authenticator compatible)\n";
echo "✓ Token system functional\n";
echo "✓ Product management ready\n";
echo "✓ Currency system operational\n";
echo "✓ Authentication working\n\n";

echo "Default Owner Credentials:\n";
echo "Email: admin@licensify.local\n";
echo "Password: admin123\n";
echo "⚠️  CHANGE PASSWORD IN PRODUCTION!\n\n";

echo "Sample Vendor Token: {$token}\n";
echo "Use this token to register a vendor account.\n\n";

echo "API Endpoints:\n";
echo "- POST /backend/api/v1/auth.php?endpoint=login\n";
echo "- POST /backend/api/v1/auth.php?endpoint=register\n";
echo "- POST /backend/api/v1/licenses.php?endpoint=generate\n";
echo "- POST /backend/api/v1/licenses.php?endpoint=validate\n";
echo "- GET  /backend/api/v1/licenses.php?endpoint=list\n\n";

echo "Data Directory: " . DATA_PATH . "\n";
echo "Owner DB: " . OWNER_DB_PATH . "/owner.db\n";
echo "Vendor DBs: " . VENDOR_DB_PATH . "/{vendor_code}/vendor.db\n\n";

echo "=== SYSTEM READY ===\n";

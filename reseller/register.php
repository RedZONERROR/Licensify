<?php
/**
 * Reseller Registration Page (Token-based)
 */

session_start();
require_once '../backend/config/app.php';

$error = '';
$success = '';
$tokenData = null;

// Check if token is provided
$token = $_GET['token'] ?? $_POST['token'] ?? '';

if ($token) {
    // Validate token
    $tokenModel = new Token();
    $validation = $tokenModel->validate($token);
    
    if ($validation['valid']) {
        $tokenData = $validation['data'];
        
        // Check if token is for reseller
        if ($tokenData['role'] !== 'reseller') {
            $error = 'This token is not for reseller registration';
            $tokenData = null;
        }
    } else {
        $error = $validation['message'];
    }
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenData) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $name = $_POST['name'] ?? '';
    $vendorCode = $_POST['vendor_code'] ?? '';
    
    // Validation
    if (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (empty($name)) {
        $error = 'Name is required';
    } elseif (empty($vendorCode)) {
        $error = 'Vendor code is required';
    } else {
        // Verify vendor code exists
        $vendorModel = new Vendor();
        $vendor = $vendorModel->getByCode($vendorCode);
        
        if (!$vendor) {
            $error = 'Invalid vendor code';
        } else {
            $authController = new AuthController();
            $result = $authController->registerWithToken(
                $token,
                $email,
                $password,
                [
                    'name' => $name,
                    'vendor_code' => $vendorCode
                ]
            );
            
            if ($result['success']) {
                $success = 'Registration successful! You can now login.';
                // Clear token data to show success message
                $tokenData = null;
            } else {
                $error = $result['message'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reseller Registration - Licensify</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        :root {
            --primary-bg: #0a0e1a;
            --secondary-bg: #1a1f36;
            --card-bg: #1e2439;
            --border-color: #2a3150;
            --text-primary: #ffffff;
            --text-secondary: #8b92b0;
            --accent-blue: #4A9EFF;
            --accent-purple: #7B61FF;
            --accent-green: #00D4AA;
        }
        
        body {
            background: var(--primary-bg);
            color: var(--text-primary);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            margin: 0;
            padding: 0;
        }
        
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .register-box {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 48px;
            width: 100%;
            max-width: 550px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }
        
        .register-logo {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .register-logo img {
            width: 48px;
            height: 48px;
            margin-bottom: 16px;
        }
        
        .register-logo h1 {
            font-size: 28px;
            margin-bottom: 8px;
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .register-logo p {
            color: var(--text-secondary);
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            background: var(--secondary-bg);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 15px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--accent-blue);
        }
        
        .form-group small {
            display: block;
            margin-top: 6px;
            color: var(--text-secondary);
            font-size: 13px;
        }
        
        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-size: 14px;
        }
        
        .success-message {
            background: rgba(0, 212, 170, 0.1);
            border: 1px solid rgba(0, 212, 170, 0.3);
            color: var(--accent-green);
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-size: 14px;
        }
        
        .info-box {
            background: rgba(74, 158, 255, 0.1);
            border: 1px solid rgba(74, 158, 255, 0.3);
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
            color: var(--accent-blue);
            font-size: 14px;
            line-height: 1.6;
        }
        
        .token-info {
            background: var(--secondary-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        
        .token-info-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .token-info-item:last-child {
            border-bottom: none;
        }
        
        .token-info-label {
            color: var(--text-secondary);
            font-size: 13px;
        }
        
        .token-info-value {
            font-weight: 600;
        }
        
        .btn-register {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple));
            border: none;
            color: white;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(74, 158, 255, 0.4);
        }
        
        .register-footer {
            text-align: center;
            margin-top: 24px;
            color: var(--text-secondary);
            font-size: 14px;
        }
        
        .register-footer a {
            color: var(--accent-blue);
            text-decoration: none;
        }
        
        .register-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-box">
            <div class="register-logo">
                <img src="../assets/icons/logo.svg" alt="Licensify">
                <h1>LICENSIFY</h1>
                <p>Reseller Registration</p>
            </div>
            
            <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success); ?>
            </div>
            <div style="text-align: center; margin-top: 24px;">
                <a href="login.php" class="btn-register" style="display: inline-block; text-decoration: none;">
                    Go to Login
                </a>
            </div>
            <?php elseif (!$token): ?>
            <div class="info-box">
                <strong>Token Required</strong><br>
                You need a reseller access token to register. Please contact your vendor to get your token.
            </div>
            
            <form method="GET">
                <div class="form-group">
                    <label for="token">Enter Your Token</label>
                    <input type="text" id="token" name="token" required 
                           placeholder="Paste your reseller token here">
                </div>
                
                <button type="submit" class="btn-register">Validate Token</button>
            </form>
            <?php elseif ($tokenData): ?>
            <div class="info-box">
                <strong>Token Validated!</strong><br>
                Your token is valid. Please complete the registration form below.
            </div>
            
            <div class="token-info">
                <h3 style="margin-bottom: 16px; font-size: 16px;">Token Details</h3>
                <div class="token-info-item">
                    <span class="token-info-label">Role</span>
                    <span class="token-info-value">Reseller</span>
                </div>
                <div class="token-info-item">
                    <span class="token-info-label">Initial Balance</span>
                    <span class="token-info-value">$<?php echo number_format($tokenData['initial_balance'], 2); ?></span>
                </div>
                <div class="token-info-item">
                    <span class="token-info-label">Discount Rate</span>
                    <span class="token-info-value"><?php echo $tokenData['discount_rate']; ?>%</span>
                </div>
                <div class="token-info-item">
                    <span class="token-info-label">Expiry Date</span>
                    <span class="token-info-value"><?php echo date('Y-m-d', $tokenData['expiry_date']); ?></span>
                </div>
            </div>
            
            <form method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <div class="form-group">
                    <label for="vendor_code">Vendor Code *</label>
                    <input type="text" id="vendor_code" name="vendor_code" required 
                           placeholder="Enter your vendor's code (e.g., VND12345678)">
                    <small>Ask your vendor for this code</small>
                </div>
                
                <div class="form-group">
                    <label for="name">Your Name *</label>
                    <input type="text" id="name" name="name" required 
                           placeholder="Your full name or business name">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required 
                           placeholder="reseller@example.com">
                    <small>This will be your login email</small>
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required minlength="8"
                           placeholder="Create a strong password">
                    <small>Minimum 8 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8"
                           placeholder="Confirm your password">
                </div>
                
                <button type="submit" class="btn-register">Complete Registration</button>
            </form>
            <?php endif; ?>
            
            <div class="register-footer">
                <p>Already have an account? <a href="login.php">Login here</a></p>
                <p><a href="../index.php">← Back to Home</a></p>
            </div>
        </div>
    </div>
</body>
</html>

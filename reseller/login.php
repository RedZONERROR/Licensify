<?php
/**
 * Reseller Login Page
 */

session_start();
require_once '../backend/config/app.php';

$error = '';

// Redirect if already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['reseller_code'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $totpCode = $_POST['totp_code'] ?? null;
    
    $authController = new AuthController();
    $result = $authController->login($email, $password, $totpCode);
    
    if ($result['success']) {
        // Check if user is a reseller
        if ($result['user']['role'] === 'user') {
            // Get reseller info from vendor database
            // We need to find which vendor this reseller belongs to
            $db = new Database('owner');
            $stmt = $db->query("SELECT vendor_id FROM users WHERE id = ?", [$result['user']['id']]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($userData && $userData['vendor_id']) {
                // This is a reseller, get their reseller code
                $vendorDb = new Database($userData['vendor_id']);
                $stmt = $vendorDb->query("SELECT reseller_code FROM resellers WHERE user_id = ?", [$result['user']['id']]);
                $resellerData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($resellerData) {
                    $_SESSION['reseller_code'] = $resellerData['reseller_code'];
                    $_SESSION['vendor_code'] = $userData['vendor_id'];
                    header('Location: index.php');
                    exit;
                }
            }
            
            $error = 'Access denied. Reseller account required.';
            session_destroy();
        } else {
            $error = 'Access denied. Reseller account required.';
            session_destroy();
        }
    } else {
        $error = $result['message'];
        $requires2FA = $result['requires_2fa'] ?? false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reseller Login - Licensify</title>
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
        }
        
        body {
            background: var(--primary-bg);
            color: var(--text-primary);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            margin: 0;
            padding: 0;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-box {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 48px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .login-logo img {
            width: 48px;
            height: 48px;
            margin-bottom: 16px;
        }
        
        .login-logo h1 {
            font-size: 28px;
            margin-bottom: 8px;
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .login-logo p {
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
        
        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-size: 14px;
        }
        
        .btn-login {
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
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(74, 158, 255, 0.4);
        }
        
        .login-footer {
            text-align: center;
            margin-top: 24px;
            color: var(--text-secondary);
            font-size: 14px;
        }
        
        .login-footer a {
            color: var(--accent-blue);
            text-decoration: none;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-logo">
                <img src="../assets/icons/logo.svg" alt="Licensify">
                <h1>LICENSIFY</h1>
                <p>Reseller Portal</p>
            </div>
            
            <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           placeholder="reseller@example.com">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required
                           placeholder="Enter your password">
                </div>
                
                <?php if (isset($requires2FA) && $requires2FA): ?>
                <div class="form-group">
                    <label for="totp_code">2FA Code</label>
                    <input type="text" id="totp_code" name="totp_code" 
                           placeholder="Enter 6-digit code" maxlength="6" required>
                </div>
                <?php endif; ?>
                
                <button type="submit" class="btn-login">Sign In</button>
            </form>
            
            <div class="login-footer">
                <p>Don't have an account? <a href="register.php">Register with token</a></p>
                <p><a href="../index.php">← Back to Home</a></p>
            </div>
        </div>
    </div>
</body>
</html>

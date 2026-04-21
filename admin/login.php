<?php
/**
 * Admin Login Page
 */

session_start();
require_once '../backend/config/app.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $totpCode = $_POST['totp_code'] ?? null;
    
    $authController = new AuthController();
    $result = $authController->login($email, $password, $totpCode);
    
    if ($result['success']) {
        header('Location: index.php');
        exit;
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
    <title>Admin Login - Licensify</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-bg);
        }
        
        .login-box {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 48px;
            width: 100%;
            max-width: 450px;
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
            transition: transform 0.3s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
        }
        
        .login-footer {
            text-align: center;
            margin-top: 24px;
            color: var(--text-secondary);
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-logo">
                <img src="../assets/icons/logo.svg" alt="Licensify">
                <h1>LICENSIFY</h1>
                <p>Admin Dashboard</p>
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
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
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
                <p>Default: admin@licensify.local / admin123</p>
            </div>
        </div>
    </div>
</body>
</html>

<?php
/**
 * Vendor Settings Page - Profile, Security, Payment Gateways
 */

session_start();
require_once '../backend/config/app.php';

// Check authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['vendor_code'])) {
    header('Location: login.php');
    exit;
}

$db = new Database('owner');
$stmt = $db->query("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['role'] !== 'vendor') {
    header('Location: login.php');
    exit;
}

// Get vendor info
$vendorModel = new Vendor();
$vendor = $vendorModel->getByCode($_SESSION['vendor_code']);

$authController = new AuthController();
$message = '';
$messageType = '';
$qrCodeUrl = '';
$totpSecret = '';

// Get vendor database for payment settings
$vendorDb = new Database($_SESSION['vendor_code']);

// Get payment gateway settings
$stmt = $vendorDb->query("SELECT key, value FROM settings WHERE key LIKE 'payment_%'");
$paymentSettings = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $paymentSettings[$row['key']] = $row['value'];
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $companyName = $_POST['company_name'] ?? '';
        $email = $_POST['email'] ?? '';
        
        try {
            // Update company name
            if ($companyName && $companyName !== $vendor['company_name']) {
                $vendorModel->update($vendor['id'], ['company_name' => $companyName]);
                $vendor['company_name'] = $companyName;
            }
            
            // Update email
            if ($email && $email !== $user['email']) {
                $stmt = $db->query("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?", [$email, $user['id']]);
                if ($stmt->fetchColumn() > 0) {
                    throw new Exception('Email already in use');
                }
                $db->query("UPDATE users SET email = ?, updated_at = ? WHERE id = ?", [$email, time(), $user['id']]);
                $user['email'] = $email;
            }
            
            $message = "Profile updated successfully";
            $messageType = 'success';
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        $encryption = new Encryption();
        
        if (!$encryption->verifyPassword($currentPassword, $user['password'])) {
            $message = "Current password is incorrect";
            $messageType = 'error';
        } elseif (strlen($newPassword) < 8) {
            $message = "New password must be at least 8 characters";
            $messageType = 'error';
        } elseif ($newPassword !== $confirmPassword) {
            $message = "Passwords do not match";
            $messageType = 'error';
        } else {
            $hashedPassword = $encryption->hashPassword($newPassword);
            $db->query("UPDATE users SET password = ?, updated_at = ? WHERE id = ?", [$hashedPassword, time(), $user['id']]);
            $message = "Password changed successfully";
            $messageType = 'success';
        }
    } elseif ($action === 'enable_2fa') {
        $result = $authController->enable2FA($user['id']);
        
        if ($result['success']) {
            $totpSecret = $result['secret'];
            $qrCodeUrl = $result['qr_code_url'];
            $message = "Scan the QR code with Google Authenticator and verify below";
            $messageType = 'info';
        } else {
            $message = $result['message'];
            $messageType = 'error';
        }
    } elseif ($action === 'verify_2fa') {
        $code = $_POST['totp_code'] ?? '';
        $result = $authController->verify2FA($user['id'], $code);
        
        if ($result['success']) {
            $message = "2FA enabled successfully";
            $messageType = 'success';
            $user['totp_enabled'] = 1;
        } else {
            $message = $result['message'];
            $messageType = 'error';
        }
    } elseif ($action === 'disable_2fa') {
        $password = $_POST['password'] ?? '';
        $result = $authController->disable2FA($user['id'], $password);
        
        if ($result['success']) {
            $message = "2FA disabled successfully";
            $messageType = 'success';
            $user['totp_enabled'] = 0;
        } else {
            $message = $result['message'];
            $messageType = 'error';
        }
    } elseif ($action === 'update_payment' && $vendor['payment_access']) {
        try {
            $settings = [
                'payment_cryptomus_enabled' => isset($_POST['cryptomus_enabled']) ? 1 : 0,
                'payment_cryptomus_api_key' => $_POST['cryptomus_api_key'] ?? '',
                'payment_cryptomus_merchant_id' => $_POST['cryptomus_merchant_id'] ?? '',
                'payment_razorpay_enabled' => isset($_POST['razorpay_enabled']) ? 1 : 0,
                'payment_razorpay_key_id' => $_POST['razorpay_key_id'] ?? '',
                'payment_razorpay_key_secret' => $_POST['razorpay_key_secret'] ?? ''
            ];
            
            foreach ($settings as $key => $value) {
                $vendorDb->query(
                    "INSERT OR REPLACE INTO settings (key, value, updated_at) VALUES (?, ?, ?)",
                    [$key, $value, time()]
                );
            }
            
            $message = "Payment gateway settings updated successfully";
            $messageType = 'success';
            
            // Refresh payment settings
            $stmt = $vendorDb->query("SELECT key, value FROM settings WHERE key LIKE 'payment_%'");
            $paymentSettings = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $paymentSettings[$row['key']] = $row['value'];
            }
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = 'error';
        }
    }
    
    // Refresh user data
    $stmt = $db->query("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $vendor = $vendorModel->getByCode($_SESSION['vendor_code']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Vendor Portal</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../admin/admin-styles.css">
    <style>
        .settings-container {
            padding: 32px 40px;
        }
        
        .settings-tabs {
            display: flex;
            gap: 8px;
            border-bottom: 2px solid var(--border-color);
            margin-bottom: 32px;
        }
        
        .tab-btn {
            padding: 14px 24px;
            background: transparent;
            border: none;
            color: var(--text-secondary);
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
            position: relative;
            bottom: -2px;
        }
        
        .tab-btn:hover {
            color: var(--text-primary);
        }
        
        .tab-btn.active {
            color: var(--accent-blue);
            border-bottom-color: var(--accent-blue);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease-out;
        }
        
        .settings-section {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 24px;
        }
        
        .settings-section h3 {
            font-size: 18px;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            background: var(--secondary-bg);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 15px;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--accent-blue);
        }
        
        .form-group small {
            display: block;
            margin-top: 6px;
            color: var(--text-secondary);
            font-size: 13px;
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
        
        .warning-box {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
            color: var(--accent-orange);
            font-size: 14px;
            line-height: 1.6;
        }
        
        .qr-code-container {
            text-align: center;
            padding: 24px;
            background: white;
            border-radius: 12px;
            margin: 24px 0;
        }
        
        .qr-code-container img {
            max-width: 200px;
            height: auto;
        }
        
        .secret-code {
            font-family: 'Courier New', monospace;
            background: var(--secondary-bg);
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 16px;
            letter-spacing: 2px;
            margin: 16px 0;
            text-align: center;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .checkbox-group label {
            margin: 0;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <h1>Settings</h1>
            </header>
            
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>" style="margin: 24px 40px;">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>
            
            <div class="settings-container">
                <!-- Tabs -->
                <div class="settings-tabs">
                    <button class="tab-btn active" onclick="switchTab('profile')">
                        👤 Profile
                    </button>
                    <button class="tab-btn" onclick="switchTab('security')">
                        🔒 Security
                    </button>
                    <?php if ($vendor['payment_access']): ?>
                    <button class="tab-btn" onclick="switchTab('payment')">
                        💳 Payment Gateways
                    </button>
                    <?php endif; ?>
                    <button class="tab-btn" onclick="switchTab('account')">
                        📊 Account Info
                    </button>
                </div>
                
                <!-- Profile Tab -->
                <div id="profile-tab" class="tab-content active">
                    <div class="settings-section">
                        <h3>Company Information</h3>
                        
                        <form method="POST">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="form-group">
                                <label>Company Name</label>
                                <input type="text" name="company_name" value="<?php echo htmlspecialchars($vendor['company_name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                <small>Your email address for login and notifications</small>
                            </div>
                            
                            <div class="form-group">
                                <label>Vendor Code</label>
                                <input type="text" value="<?php echo htmlspecialchars($vendor['vendor_code']); ?>" readonly style="background: var(--primary-bg); cursor: not-allowed; font-family: monospace;">
                                <small>Your unique vendor identifier</small>
                            </div>
                            
                            <button type="submit" class="btn-primary">Update Profile</button>
                        </form>
                    </div>
                </div>
                
                <!-- Security Tab -->
                <div id="security-tab" class="tab-content">
                    <!-- Change Password -->
                    <div class="settings-section">
                        <h3>Change Password</h3>
                        
                        <form method="POST">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="form-group">
                                <label>Current Password</label>
                                <input type="password" name="current_password" required>
                            </div>
                            
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="new_password" required minlength="8">
                                <small>Minimum 8 characters</small>
                            </div>
                            
                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <input type="password" name="confirm_password" required minlength="8">
                            </div>
                            
                            <button type="submit" class="btn-primary">Change Password</button>
                        </form>
                    </div>
                    
                    <!-- Two-Factor Authentication -->
                    <div class="settings-section">
                        <h3>Two-Factor Authentication (2FA)</h3>
                        
                        <?php if ($user['totp_enabled']): ?>
                            <div class="info-box">
                                ✓ Two-factor authentication is currently <strong>enabled</strong> for your account.
                            </div>
                            
                            <form method="POST" onsubmit="return confirm('Are you sure you want to disable 2FA? This will make your account less secure.');">
                                <input type="hidden" name="action" value="disable_2fa">
                                
                                <div class="form-group">
                                    <label>Enter Your Password to Disable 2FA</label>
                                    <input type="password" name="password" required>
                                </div>
                                
                                <button type="submit" class="btn-secondary" style="background: var(--accent-red); border-color: var(--accent-red);">
                                    Disable 2FA
                                </button>
                            </form>
                        <?php else: ?>
                            <?php if ($qrCodeUrl): ?>
                                <div class="info-box">
                                    Scan this QR code with Google Authenticator or any TOTP-compatible app.
                                </div>
                                
                                <div class="qr-code-container">
                                    <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code">
                                </div>
                                
                                <div class="secret-code">
                                    <?php echo $totpSecret; ?>
                                </div>
                                
                                <form method="POST">
                                    <input type="hidden" name="action" value="verify_2fa">
                                    
                                    <div class="form-group">
                                        <label>Enter 6-Digit Code from App</label>
                                        <input type="text" name="totp_code" required maxlength="6" pattern="[0-9]{6}" 
                                               placeholder="000000" style="text-align: center; font-size: 24px; letter-spacing: 8px;">
                                    </div>
                                    
                                    <button type="submit" class="btn-primary">Verify and Enable 2FA</button>
                                </form>
                            <?php else: ?>
                                <div class="warning-box">
                                    ⚠ Two-factor authentication is currently <strong>disabled</strong>. Enable it to add an extra layer of security to your account.
                                </div>
                                
                                <form method="POST">
                                    <input type="hidden" name="action" value="enable_2fa">
                                    <button type="submit" class="btn-primary">Enable 2FA</button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Payment Gateways Tab -->
                <?php if ($vendor['payment_access']): ?>
                <div id="payment-tab" class="tab-content">
                    <div class="settings-section">
                        <h3>Payment Gateway Configuration</h3>
                        
                        <div class="info-box">
                            Configure your payment gateways to accept payments from customers. These settings are specific to your vendor account.
                        </div>
                        
                        <form method="POST">
                            <input type="hidden" name="action" value="update_payment">
                            
                            <!-- Cryptomus -->
                            <h4 style="font-size: 16px; margin: 32px 0 20px; padding-bottom: 12px; border-bottom: 1px solid var(--border-color);">
                                💎 Cryptomus (Cryptocurrency)
                            </h4>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" name="cryptomus_enabled" id="cryptomus_enabled" 
                                       <?php echo ($paymentSettings['payment_cryptomus_enabled'] ?? 0) ? 'checked' : ''; ?>>
                                <label for="cryptomus_enabled">Enable Cryptomus Payment Gateway</label>
                            </div>
                            
                            <div class="form-group">
                                <label>Cryptomus API Key</label>
                                <input type="text" name="cryptomus_api_key" 
                                       value="<?php echo htmlspecialchars($paymentSettings['payment_cryptomus_api_key'] ?? ''); ?>"
                                       placeholder="Enter your Cryptomus API key">
                            </div>
                            
                            <div class="form-group">
                                <label>Cryptomus Merchant ID</label>
                                <input type="text" name="cryptomus_merchant_id" 
                                       value="<?php echo htmlspecialchars($paymentSettings['payment_cryptomus_merchant_id'] ?? ''); ?>"
                                       placeholder="Enter your Cryptomus Merchant ID">
                            </div>
                            
                            <!-- Razorpay -->
                            <h4 style="font-size: 16px; margin: 32px 0 20px; padding-bottom: 12px; border-bottom: 1px solid var(--border-color);">
                                💳 Razorpay (Credit/Debit Cards, UPI)
                            </h4>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" name="razorpay_enabled" id="razorpay_enabled"
                                       <?php echo ($paymentSettings['payment_razorpay_enabled'] ?? 0) ? 'checked' : ''; ?>>
                                <label for="razorpay_enabled">Enable Razorpay Payment Gateway</label>
                            </div>
                            
                            <div class="form-group">
                                <label>Razorpay Key ID</label>
                                <input type="text" name="razorpay_key_id" 
                                       value="<?php echo htmlspecialchars($paymentSettings['payment_razorpay_key_id'] ?? ''); ?>"
                                       placeholder="Enter your Razorpay Key ID">
                            </div>
                            
                            <div class="form-group">
                                <label>Razorpay Key Secret</label>
                                <input type="password" name="razorpay_key_secret" 
                                       value="<?php echo htmlspecialchars($paymentSettings['payment_razorpay_key_secret'] ?? ''); ?>"
                                       placeholder="Enter your Razorpay Key Secret">
                            </div>
                            
                            <button type="submit" class="btn-primary">Save Payment Settings</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Account Info Tab -->
                <div id="account-tab" class="tab-content">
                    <div class="settings-section">
                        <h3>Account Information</h3>
                        
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px;">
                            <div>
                                <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 4px;">Company Name</div>
                                <div style="font-size: 16px; font-weight: 600;"><?php echo htmlspecialchars($vendor['company_name']); ?></div>
                            </div>
                            
                            <div>
                                <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 4px;">Vendor Code</div>
                                <div style="font-size: 16px; font-weight: 600; font-family: monospace; color: var(--accent-cyan);">
                                    <?php echo htmlspecialchars($vendor['vendor_code']); ?>
                                </div>
                            </div>
                            
                            <div>
                                <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 4px;">Balance Type</div>
                                <div style="font-size: 16px; font-weight: 600;">
                                    <?php echo $vendor['is_unlimited'] ? 'Unlimited' : 'Limited'; ?>
                                </div>
                            </div>
                            
                            <div>
                                <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 4px;">Current Balance</div>
                                <div style="font-size: 16px; font-weight: 600; color: var(--accent-green);">
                                    <?php if ($vendor['is_unlimited']): ?>
                                        ∞
                                    <?php else: ?>
                                        $<?php echo number_format($vendor['balance'], 2); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div>
                                <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 4px;">Discount Rate</div>
                                <div style="font-size: 16px; font-weight: 600;"><?php echo $vendor['discount_rate']; ?>%</div>
                            </div>
                            
                            <div>
                                <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 4px;">Expiry Date</div>
                                <div style="font-size: 16px; font-weight: 600;">
                                    <?php 
                                    $daysLeft = floor(($vendor['expiry_date'] - time()) / 86400);
                                    if ($daysLeft < 0) {
                                        echo '<span style="color: #ef4444;">Expired</span>';
                                    } elseif ($daysLeft < 30) {
                                        echo '<span style="color: #F59E0B;">' . date('Y-m-d', $vendor['expiry_date']) . ' (' . $daysLeft . ' days left)</span>';
                                    } else {
                                        echo date('Y-m-d', $vendor['expiry_date']);
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <div>
                                <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 4px;">Can Create Products</div>
                                <div style="font-size: 16px; font-weight: 600;">
                                    <?php echo $vendor['can_create_products'] ? '✓ Yes' : '✗ No'; ?>
                                </div>
                            </div>
                            
                            <div>
                                <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 4px;">Payment Access</div>
                                <div style="font-size: 16px; font-weight: 600;">
                                    <?php echo $vendor['payment_access'] ? '✓ Yes' : '✗ No'; ?>
                                </div>
                            </div>
                            
                            <div>
                                <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 4px;">Account Status</div>
                                <div style="font-size: 16px;">
                                    <?php if ($vendor['is_active']): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactive</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div>
                                <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 4px;">2FA Status</div>
                                <div style="font-size: 16px; font-weight: 600;">
                                    <?php if ($user['totp_enabled']): ?>
                                        <span style="color: var(--accent-green);">✓ Enabled</span>
                                    <?php else: ?>
                                        <span style="color: var(--accent-red);">✗ Disabled</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div>
                                <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 4px;">Account Created</div>
                                <div style="font-size: 16px; font-weight: 600;">
                                    <?php echo date('Y-m-d H:i:s', $vendor['created_at']); ?>
                                </div>
                            </div>
                            
                            <div>
                                <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 4px;">Last Updated</div>
                                <div style="font-size: 16px; font-weight: 600;">
                                    <?php echo date('Y-m-d H:i:s', $vendor['updated_at']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/script.js"></script>
    <script src="../admin/admin-script.js"></script>
    <script>
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Add active class to clicked button
            event.target.classList.add('active');
        }
    </script>
</body>
</html>

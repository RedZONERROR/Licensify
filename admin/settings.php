<?php
/**
 * Settings Page - System Settings, Profile, 2FA
 */

session_start();
require_once '../backend/config/app.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$db = new Database('owner');
$stmt = $db->query("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['role'] !== 'owner') {
    header('Location: ../index.php');
    exit;
}

$authController = new AuthController();
$message = '';
$messageType = '';
$qrCodeUrl = '';
$totpSecret = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        // Update email
        $newEmail = $_POST['email'] ?? '';
        
        if ($newEmail && $newEmail !== $user['email']) {
            // Check if email exists
            $stmt = $db->query("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?", [$newEmail, $user['id']]);
            if ($stmt->fetchColumn() > 0) {
                $message = "Email already in use";
                $messageType = 'error';
            } else {
                $db->query("UPDATE users SET email = ?, updated_at = ? WHERE id = ?", [$newEmail, time(), $user['id']]);
                $user['email'] = $newEmail;
                $message = "Profile updated successfully";
                $messageType = 'success';
            }
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
    } elseif ($action === 'update_system') {
        // Update system settings
        $settings = [
            'app_name' => $_POST['app_name'] ?? 'Licensify',
            'timezone' => $_POST['timezone'] ?? 'UTC',
            'currency' => $_POST['currency'] ?? 'USD'
        ];
        
        foreach ($settings as $key => $value) {
            $db->query(
                "INSERT OR REPLACE INTO settings (key, value, updated_at) VALUES (?, ?, ?)",
                [$key, $value, time()]
            );
        }
        
        $message = "System settings updated successfully";
        $messageType = 'success';
    }
    
    // Refresh user data
    $stmt = $db->query("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get system settings
$stmt = $db->query("SELECT key, value FROM settings");
$systemSettings = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $systemSettings[$row['key']] = $row['value'];
}

// Get statistics
$vendorModel = new Vendor();
$tokenModel = new Token();
$productModel = new Product();

$vendorStats = $vendorModel->getStats();
$tokenStats = $tokenModel->getStats();
$products = $productModel->list();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Licensify Admin</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="admin-styles.css">
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
        
        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }
        
        .stat-box {
            background: var(--secondary-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        
        .stat-box-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .stat-box-label {
            font-size: 13px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
                    <button class="tab-btn" onclick="switchTab('system')">
                        ⚙️ System
                    </button>
                    <button class="tab-btn" onclick="switchTab('overview')">
                        📊 Overview
                    </button>
                </div>
                
                <!-- Profile Tab -->
                <div id="profile-tab" class="tab-content active">
                    <div class="settings-section">
                        <h3>Profile Information</h3>
                        
                        <form method="POST">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                <small>Your email address for login and notifications</small>
                            </div>
                            
                            <div class="form-group">
                                <label>Role</label>
                                <input type="text" value="Owner" readonly style="background: var(--primary-bg); cursor: not-allowed;">
                                <small>Your role in the system</small>
                            </div>
                            
                            <div class="form-group">
                                <label>Account Created</label>
                                <input type="text" value="<?php echo date('F j, Y', $user['created_at']); ?>" readonly style="background: var(--primary-bg); cursor: not-allowed;">
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
                
                <!-- System Tab -->
                <div id="system-tab" class="tab-content">
                    <div class="settings-section">
                        <h3>System Settings</h3>
                        
                        <form method="POST">
                            <input type="hidden" name="action" value="update_system">
                            
                            <div class="form-group">
                                <label>Application Name</label>
                                <input type="text" name="app_name" value="<?php echo htmlspecialchars($systemSettings['app_name'] ?? 'Licensify'); ?>">
                                <small>The name displayed throughout the application</small>
                            </div>
                            
                            <div class="form-group">
                                <label>Timezone</label>
                                <select name="timezone">
                                    <option value="UTC" <?php echo ($systemSettings['timezone'] ?? 'UTC') === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                                    <option value="America/New_York" <?php echo ($systemSettings['timezone'] ?? '') === 'America/New_York' ? 'selected' : ''; ?>>America/New York</option>
                                    <option value="America/Los_Angeles" <?php echo ($systemSettings['timezone'] ?? '') === 'America/Los_Angeles' ? 'selected' : ''; ?>>America/Los Angeles</option>
                                    <option value="Europe/London" <?php echo ($systemSettings['timezone'] ?? '') === 'Europe/London' ? 'selected' : ''; ?>>Europe/London</option>
                                    <option value="Europe/Paris" <?php echo ($systemSettings['timezone'] ?? '') === 'Europe/Paris' ? 'selected' : ''; ?>>Europe/Paris</option>
                                    <option value="Asia/Tokyo" <?php echo ($systemSettings['timezone'] ?? '') === 'Asia/Tokyo' ? 'selected' : ''; ?>>Asia/Tokyo</option>
                                    <option value="Asia/Dubai" <?php echo ($systemSettings['timezone'] ?? '') === 'Asia/Dubai' ? 'selected' : ''; ?>>Asia/Dubai</option>
                                    <option value="Asia/Kolkata" <?php echo ($systemSettings['timezone'] ?? '') === 'Asia/Kolkata' ? 'selected' : ''; ?>>Asia/Kolkata</option>
                                </select>
                                <small>Default timezone for the application</small>
                            </div>
                            
                            <div class="form-group">
                                <label>Currency</label>
                                <select name="currency">
                                    <option value="USD" <?php echo ($systemSettings['currency'] ?? 'USD') === 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                                    <option value="EUR" <?php echo ($systemSettings['currency'] ?? '') === 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                                    <option value="GBP" <?php echo ($systemSettings['currency'] ?? '') === 'GBP' ? 'selected' : ''; ?>>GBP (£)</option>
                                    <option value="INR" <?php echo ($systemSettings['currency'] ?? '') === 'INR' ? 'selected' : ''; ?>>INR (₹)</option>
                                    <option value="AED" <?php echo ($systemSettings['currency'] ?? '') === 'AED' ? 'selected' : ''; ?>>AED (د.إ)</option>
                                </select>
                                <small>Default currency for pricing</small>
                            </div>
                            
                            <button type="submit" class="btn-primary">Save System Settings</button>
                        </form>
                    </div>
                    
                    <div class="settings-section">
                        <h3>Database Information</h3>
                        
                        <div class="form-group">
                            <label>Owner Database</label>
                            <input type="text" value="<?php echo OWNER_DB_PATH . '/owner.db'; ?>" readonly style="background: var(--primary-bg); cursor: not-allowed; font-family: monospace; font-size: 13px;">
                        </div>
                        
                        <div class="form-group">
                            <label>Vendor Databases</label>
                            <input type="text" value="<?php echo VENDOR_DB_PATH; ?>" readonly style="background: var(--primary-bg); cursor: not-allowed; font-family: monospace; font-size: 13px;">
                        </div>
                        
                        <div class="form-group">
                            <label>Encryption</label>
                            <input type="text" value="XTEA (Pure PHP, No OpenSSL)" readonly style="background: var(--primary-bg); cursor: not-allowed;">
                        </div>
                    </div>
                </div>
                
                <!-- Overview Tab -->
                <div id="overview-tab" class="tab-content">
                    <div class="settings-section">
                        <h3>System Overview</h3>
                        
                        <div class="stats-overview">
                            <div class="stat-box">
                                <div class="stat-box-value"><?php echo $vendorStats['total'] ?? 0; ?></div>
                                <div class="stat-box-label">Total Vendors</div>
                            </div>
                            
                            <div class="stat-box">
                                <div class="stat-box-value"><?php echo $vendorStats['active'] ?? 0; ?></div>
                                <div class="stat-box-label">Active Vendors</div>
                            </div>
                            
                            <div class="stat-box">
                                <div class="stat-box-value"><?php echo $tokenStats['total'] ?? 0; ?></div>
                                <div class="stat-box-label">Total Tokens</div>
                            </div>
                            
                            <div class="stat-box">
                                <div class="stat-box-value"><?php echo count($products); ?></div>
                                <div class="stat-box-label">Products</div>
                            </div>
                            
                            <div class="stat-box">
                                <div class="stat-box-value">$<?php echo number_format($vendorStats['total_balance'] ?? 0, 2); ?></div>
                                <div class="stat-box-label">Total Balance</div>
                            </div>
                            
                            <div class="stat-box">
                                <div class="stat-box-value"><?php echo $vendorStats['unlimited'] ?? 0; ?></div>
                                <div class="stat-box-label">Unlimited Vendors</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="settings-section">
                        <h3>System Information</h3>
                        
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                            <div>
                                <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 4px;">PHP Version</div>
                                <div style="font-size: 16px; font-weight: 600;"><?php echo PHP_VERSION; ?></div>
                            </div>
                            
                            <div>
                                <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 4px;">Application Version</div>
                                <div style="font-size: 16px; font-weight: 600;"><?php echo APP_VERSION; ?></div>
                            </div>
                            
                            <div>
                                <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 4px;">Database Type</div>
                                <div style="font-size: 16px; font-weight: 600;">SQLite with WAL</div>
                            </div>
                            
                            <div>
                                <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 4px;">Encryption</div>
                                <div style="font-size: 16px; font-weight: 600;">XTEA (Pure PHP)</div>
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
                                <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 4px;">Server Time</div>
                                <div style="font-size: 16px; font-weight: 600;"><?php echo date('Y-m-d H:i:s'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/script.js"></script>
    <script src="admin-script.js"></script>
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

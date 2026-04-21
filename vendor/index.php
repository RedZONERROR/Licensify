<?php
/**
 * Vendor Dashboard - Main Overview
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

if (!$vendor) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Get vendor database
$vendorDb = new Database($_SESSION['vendor_code']);

// Get statistics
$resellerModel = new Reseller($_SESSION['vendor_code']);
$licenseModel = new License($_SESSION['vendor_code']);

$resellerStats = $resellerModel->getStats();
$licenseStats = $licenseModel->getStats();

// Get recent resellers
$resellers = $resellerModel->list(['limit' => 5]);

// Get recent licenses
$licenses = $licenseModel->list(['limit' => 10]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Dashboard - Licensify</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../admin/admin-styles.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <h1>Dashboard</h1>
                <div class="header-actions">
                    <button class="btn-primary" onclick="location.href='licenses.php?action=generate'">
                        + Generate License
                    </button>
                </div>
            </header>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4A9EFF, #7B61FF);">
                        👥
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total Resellers</div>
                        <div class="stat-value"><?php echo $resellerStats['total'] ?? 0; ?></div>
                        <div class="stat-detail">
                            <?php echo $resellerStats['active'] ?? 0; ?> active
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #00D4AA, #00A8CC);">
                        🔑
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total Licenses</div>
                        <div class="stat-value"><?php echo $licenseStats['total'] ?? 0; ?></div>
                        <div class="stat-detail">
                            <?php echo $licenseStats['active'] ?? 0; ?> active
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #A855F7, #7C3AED);">
                        💰
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Balance</div>
                        <div class="stat-value">
                            <?php if ($vendor['is_unlimited']): ?>
                                ∞
                            <?php else: ?>
                                $<?php echo number_format($vendor['balance'], 2); ?>
                            <?php endif; ?>
                        </div>
                        <div class="stat-detail">
                            <?php echo $vendor['is_unlimited'] ? 'Unlimited' : 'Available'; ?>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #F59E0B, #D97706);">
                        📈
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Revenue</div>
                        <div class="stat-value">$<?php echo number_format($licenseStats['total_revenue'] ?? 0, 2); ?></div>
                        <div class="stat-detail">Total generated</div>
                    </div>
                </div>
            </div>
            
            <!-- Account Status -->
            <div class="admin-section">
                <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 16px; padding: 28px; margin-bottom: 24px;">
                    <h3 style="font-size: 18px; margin-bottom: 20px;">Account Status</h3>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                        <div>
                            <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 4px;">Company</div>
                            <div style="font-size: 16px; font-weight: 600;"><?php echo htmlspecialchars($vendor['company_name']); ?></div>
                        </div>
                        
                        <div>
                            <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 4px;">Vendor Code</div>
                            <div style="font-size: 16px; font-weight: 600; font-family: monospace; color: var(--accent-cyan);">
                                <?php echo htmlspecialchars($vendor['vendor_code']); ?>
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
                                    echo '<span style="color: #F59E0B;">' . $daysLeft . ' days left</span>';
                                } else {
                                    echo date('Y-m-d', $vendor['expiry_date']);
                                }
                                ?>
                            </div>
                        </div>
                        
                        <div>
                            <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 4px;">Permissions</div>
                            <div style="font-size: 16px;">
                                <?php if ($vendor['can_create_products']): ?>
                                    <span title="Can create products">📦</span>
                                <?php endif; ?>
                                <?php if ($vendor['payment_access']): ?>
                                    <span title="Payment access">💳</span>
                                <?php endif; ?>
                                <?php if (!$vendor['can_create_products'] && !$vendor['payment_access']): ?>
                                    <span style="color: var(--text-secondary);">None</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div>
                            <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 4px;">Status</div>
                            <div style="font-size: 16px;">
                                <?php if ($vendor['is_active']): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Inactive</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Resellers -->
            <div class="admin-section">
                <div class="section-header">
                    <h2>Recent Resellers</h2>
                    <a href="resellers.php" class="btn-secondary">View All</a>
                </div>
                
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Code</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($resellers)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px;">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">👥</div>
                                        <div class="empty-state-title">No resellers yet</div>
                                        <div class="empty-state-text">Generate a reseller token to create your first reseller</div>
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($resellers as $reseller): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($reseller['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($reseller['email']); ?></td>
                                <td><code><?php echo htmlspecialchars($reseller['reseller_code']); ?></code></td>
                                <td>
                                    <?php if ($reseller['is_unlimited']): ?>
                                        <span class="badge badge-success">Unlimited</span>
                                    <?php else: ?>
                                        $<?php echo number_format($reseller['balance'], 2); ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($reseller['is_active']): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('Y-m-d', $reseller['created_at']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Recent Licenses -->
            <div class="admin-section">
                <div class="section-header">
                    <h2>Recent Licenses</h2>
                    <a href="licenses.php" class="btn-secondary">View All</a>
                </div>
                
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>License Key</th>
                                <th>Product</th>
                                <th>Customer</th>
                                <th>Devices</th>
                                <th>Price</th>
                                <th>Expires</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($licenses)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px;">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">🔑</div>
                                        <div class="empty-state-title">No licenses yet</div>
                                        <div class="empty-state-text">Generate your first license to get started</div>
                                        <button class="btn-primary" onclick="location.href='licenses.php?action=generate'" style="margin-top: 16px;">
                                            Generate License
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($licenses as $license): ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars($license['license_key']); ?></code></td>
                                <td><?php echo htmlspecialchars($license['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($license['customer_email'] ?? 'N/A'); ?></td>
                                <td><?php echo $license['max_devices']; ?></td>
                                <td>$<?php echo number_format($license['price'], 2); ?></td>
                                <td>
                                    <?php 
                                    if ($license['expires_at'] < time()) {
                                        echo '<span class="badge badge-danger">Expired</span>';
                                    } else {
                                        echo date('Y-m-d', $license['expires_at']);
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if ($license['is_suspended']): ?>
                                        <span class="badge badge-danger">Suspended</span>
                                    <?php elseif ($license['is_active']): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Inactive</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="admin-section">
                <h2>Quick Actions</h2>
                <div class="quick-actions">
                    <a href="licenses.php?action=generate" class="action-card">
                        <div class="action-icon">🔑</div>
                        <div class="action-title">Generate License</div>
                        <div class="action-desc">Create a new license key</div>
                    </a>
                    
                    <a href="resellers.php?action=create" class="action-card">
                        <div class="action-icon">👥</div>
                        <div class="action-title">Add Reseller</div>
                        <div class="action-desc">Generate reseller token</div>
                    </a>
                    
                    <a href="products.php" class="action-card">
                        <div class="action-icon">📦</div>
                        <div class="action-title">Manage Products</div>
                        <div class="action-desc">View and manage products</div>
                    </a>
                    
                    <a href="balance.php" class="action-card">
                        <div class="action-icon">💰</div>
                        <div class="action-title">View Balance</div>
                        <div class="action-desc">Check balance and transactions</div>
                    </a>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/script.js"></script>
    <script src="../admin/admin-script.js"></script>
</body>
</html>

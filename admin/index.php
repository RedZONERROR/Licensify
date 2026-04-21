<?php
/**
 * Admin Dashboard - Owner Panel
 * Simple starter dashboard for system management
 */

session_start();
require_once '../backend/config/app.php';

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user info
$db = new Database('owner');
$stmt = $db->query("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['role'] !== 'owner') {
    header('Location: ../index.php');
    exit;
}

// Get statistics
$vendorModel = new Vendor();
$tokenModel = new Token();
$productModel = new Product();

$vendorStats = $vendorModel->getStats();
$tokenStats = $tokenModel->getStats();
$products = $productModel->list();
$vendors = $vendorModel->list(['limit' => 10]);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Dashboard - Licensify</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="admin-styles.css">
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
                    <button class="btn-primary" onclick="location.href='tokens.php?action=create'">
                        + Generate Token
                    </button>
                </div>
            </header>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4A9EFF, #7B61FF);">
                        🏢
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total Vendors</div>
                        <div class="stat-value"><?php echo $vendorStats['total'] ?? 0; ?></div>
                        <div class="stat-detail">
                            <?php echo $vendorStats['active'] ?? 0; ?> active
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #00D4AA, #00A8CC);">
                        🎫
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Access Tokens</div>
                        <div class="stat-value"><?php echo $tokenStats['total'] ?? 0; ?></div>
                        <div class="stat-detail">
                            <?php echo $tokenStats['unused'] ?? 0; ?> unused
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #A855F7, #7C3AED);">
                        📦
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Products</div>
                        <div class="stat-value"><?php echo count($products); ?></div>
                        <div class="stat-detail">Global catalog</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #F59E0B, #D97706);">
                        💰
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total Balance</div>
                        <div class="stat-value">$<?php echo number_format($vendorStats['total_balance'] ?? 0, 2); ?></div>
                        <div class="stat-detail">Allocated to vendors</div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Vendors -->
            <div class="admin-section">
                <div class="section-header">
                    <h2>Recent Vendors</h2>
                    <a href="vendors.php" class="btn-secondary">View All</a>
                </div>
                
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Company</th>
                                <th>Email</th>
                                <th>Code</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($vendors)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px;">
                                    No vendors yet. Generate a token to create the first vendor.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($vendors as $vendor): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($vendor['company_name']); ?></td>
                                <td><?php echo htmlspecialchars($vendor['email']); ?></td>
                                <td><code><?php echo htmlspecialchars($vendor['vendor_code']); ?></code></td>
                                <td>
                                    <?php if ($vendor['is_unlimited']): ?>
                                        <span class="badge badge-success">Unlimited</span>
                                    <?php else: ?>
                                        $<?php echo number_format($vendor['balance'], 2); ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($vendor['is_active']): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('Y-m-d', $vendor['created_at']); ?></td>
                                <td>
                                    <button class="btn-icon" title="View Details">👁️</button>
                                    <button class="btn-icon" title="Edit">✏️</button>
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
                    <a href="tokens.php?action=create" class="action-card">
                        <div class="action-icon">🎫</div>
                        <div class="action-title">Generate Token</div>
                        <div class="action-desc">Create vendor or reseller access token</div>
                    </a>
                    
                    <a href="products.php?action=create" class="action-card">
                        <div class="action-icon">📦</div>
                        <div class="action-title">Add Product</div>
                        <div class="action-desc">Create new global product</div>
                    </a>
                    
                    <a href="vendors.php" class="action-card">
                        <div class="action-icon">🏢</div>
                        <div class="action-title">Manage Vendors</div>
                        <div class="action-desc">View and manage all vendors</div>
                    </a>
                    
                    <a href="settings.php" class="action-card">
                        <div class="action-icon">⚙️</div>
                        <div class="action-title">System Settings</div>
                        <div class="action-desc">Configure system preferences</div>
                    </a>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/script.js"></script>
    <script src="admin-script.js"></script>
</body>
</html>

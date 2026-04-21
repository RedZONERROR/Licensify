<?php
/**
 * Vendors Management Page
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

// Get vendors
$vendorModel = new Vendor();
$search = $_GET['search'] ?? '';
$filters = [
    'search' => $search,
    'is_active' => $_GET['status'] ?? null
];
$vendors = $vendorModel->list($filters);
$stats = $vendorModel->getStats();

// Handle actions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_balance') {
        $vendorId = $_POST['vendor_id'] ?? 0;
        $amount = floatval($_POST['amount'] ?? 0);
        
        if ($amount > 0) {
            $currency = new Currency($db);
            try {
                $newBalance = $currency->addVendorBalance($vendorId, $amount, 'Balance added by owner');
                $message = "Balance added successfully. New balance: $" . number_format($newBalance, 2);
                $messageType = 'success';
            } catch (Exception $e) {
                $message = "Error: " . $e->getMessage();
                $messageType = 'error';
            }
        }
    } elseif ($action === 'toggle_status') {
        $vendorId = $_POST['vendor_id'] ?? 0;
        $vendor = $vendorModel->getById($vendorId);
        
        if ($vendor) {
            $newStatus = $vendor['is_active'] ? 0 : 1;
            $vendorModel->update($vendorId, ['is_active' => $newStatus]);
            $message = $newStatus ? "Vendor activated" : "Vendor deactivated";
            $messageType = 'success';
        }
    }
    
    // Refresh data
    $vendors = $vendorModel->list($filters);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendors - Licensify Admin</title>
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
                <h1>Vendors Management</h1>
                <div class="header-actions">
                    <button class="btn-primary" onclick="location.href='tokens.php?role=vendor'">
                        + Generate Vendor Token
                    </button>
                </div>
            </header>
            
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>" style="margin: 24px 40px;">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>
            
            <!-- Stats -->
            <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4A9EFF, #7B61FF);">
                        🏢
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total Vendors</div>
                        <div class="stat-value"><?php echo $stats['total'] ?? 0; ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #00D4AA, #00A8CC);">
                        ✓
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Active</div>
                        <div class="stat-value"><?php echo $stats['active'] ?? 0; ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #A855F7, #7C3AED);">
                        ∞
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Unlimited</div>
                        <div class="stat-value"><?php echo $stats['unlimited'] ?? 0; ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #F59E0B, #D97706);">
                        💰
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total Balance</div>
                        <div class="stat-value">$<?php echo number_format($stats['total_balance'] ?? 0, 2); ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="admin-section">
                <form method="GET" class="filters-bar">
                    <input type="text" name="search" placeholder="Search vendors..." 
                           value="<?php echo htmlspecialchars($search); ?>"
                           style="flex: 1; padding: 12px 16px; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary);">
                    
                    <select name="status" style="padding: 12px 16px; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary);">
                        <option value="">All Status</option>
                        <option value="1" <?php echo ($_GET['status'] ?? '') === '1' ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo ($_GET['status'] ?? '') === '0' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                    
                    <button type="submit" class="btn-secondary">Filter</button>
                    <?php if ($search || isset($_GET['status'])): ?>
                    <a href="vendors.php" class="btn-secondary">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Vendors Table -->
            <div class="admin-section">
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Company</th>
                                <th>Email</th>
                                <th>Vendor Code</th>
                                <th>Balance</th>
                                <th>Discount</th>
                                <th>Permissions</th>
                                <th>Status</th>
                                <th>Expiry</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($vendors)): ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 60px;">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">🏢</div>
                                        <div class="empty-state-title">No vendors found</div>
                                        <div class="empty-state-text">Generate a vendor token to create your first vendor</div>
                                        <button class="btn-primary" onclick="location.href='tokens.php?role=vendor'" style="margin-top: 16px;">
                                            Generate Token
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($vendors as $vendor): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($vendor['company_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($vendor['email']); ?></td>
                                <td><code><?php echo htmlspecialchars($vendor['vendor_code']); ?></code></td>
                                <td>
                                    <?php if ($vendor['is_unlimited']): ?>
                                        <span class="badge badge-success">Unlimited</span>
                                    <?php else: ?>
                                        $<?php echo number_format($vendor['balance'], 2); ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $vendor['discount_rate']; ?>%</td>
                                <td>
                                    <?php if ($vendor['can_create_products']): ?>
                                        <span class="badge badge-info" title="Can create products">📦</span>
                                    <?php endif; ?>
                                    <?php if ($vendor['payment_access']): ?>
                                        <span class="badge badge-info" title="Payment access">💳</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($vendor['is_active']): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $daysLeft = floor(($vendor['expiry_date'] - time()) / 86400);
                                    if ($daysLeft < 0) {
                                        echo '<span class="badge badge-danger">Expired</span>';
                                    } elseif ($daysLeft < 30) {
                                        echo '<span class="badge badge-warning">' . $daysLeft . ' days</span>';
                                    } else {
                                        echo date('Y-m-d', $vendor['expiry_date']);
                                    }
                                    ?>
                                </td>
                                <td>
                                    <button class="btn-icon" onclick="viewVendor(<?php echo $vendor['id']; ?>)" title="View Details">👁️</button>
                                    <button class="btn-icon" onclick="addBalance(<?php echo $vendor['id']; ?>, '<?php echo htmlspecialchars($vendor['company_name']); ?>')" title="Add Balance">💰</button>
                                    <button class="btn-icon" onclick="toggleStatus(<?php echo $vendor['id']; ?>)" title="Toggle Status">🔄</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Add Balance Modal -->
    <div id="balanceModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 16px; padding: 32px; max-width: 500px; width: 90%;">
            <h2 style="margin-bottom: 24px;">Add Balance</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add_balance">
                <input type="hidden" name="vendor_id" id="modal_vendor_id">
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Vendor</label>
                    <input type="text" id="modal_vendor_name" readonly 
                           style="width: 100%; padding: 12px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary);">
                </div>
                
                <div style="margin-bottom: 24px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Amount ($)</label>
                    <input type="number" name="amount" step="0.01" min="0.01" required
                           style="width: 100%; padding: 12px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary);">
                </div>
                
                <div style="display: flex; gap: 12px;">
                    <button type="submit" class="btn-primary" style="flex: 1;">Add Balance</button>
                    <button type="button" class="btn-secondary" onclick="closeModal()" style="flex: 1;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Toggle Status Form -->
    <form id="toggleForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="toggle_status">
        <input type="hidden" name="vendor_id" id="toggle_vendor_id">
    </form>
    
    <script src="../assets/js/script.js"></script>
    <script src="admin-script.js"></script>
    <script>
        function addBalance(vendorId, vendorName) {
            document.getElementById('modal_vendor_id').value = vendorId;
            document.getElementById('modal_vendor_name').value = vendorName;
            document.getElementById('balanceModal').style.display = 'flex';
        }
        
        function closeModal() {
            document.getElementById('balanceModal').style.display = 'none';
        }
        
        function toggleStatus(vendorId) {
            if (confirm('Are you sure you want to toggle this vendor\'s status?')) {
                document.getElementById('toggle_vendor_id').value = vendorId;
                document.getElementById('toggleForm').submit();
            }
        }
        
        function viewVendor(vendorId) {
            window.location.href = 'vendor-details.php?id=' + vendorId;
        }
        
        // Close modal on escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
        
        // Close modal on outside click
        document.getElementById('balanceModal').addEventListener('click', (e) => {
            if (e.target.id === 'balanceModal') {
                closeModal();
            }
        });
    </script>
</body>
</html>

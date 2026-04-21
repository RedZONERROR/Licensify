<?php
/**
 * Resellers Management Page
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

$tokenModel = new Token();
$resellerModel = new Reseller($_SESSION['vendor_code']);
$currency = new Currency(new Database($_SESSION['vendor_code']));

$message = '';
$messageType = '';
$generatedToken = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'generate_token') {
        // Check if vendor can create resellers
        if ($vendor['is_unlimited'] || $vendor['balance'] >= floatval($_POST['initial_balance'] ?? 0)) {
            $data = [
                'role' => 'reseller',
                'parent_id' => $user['id'],
                'expiry_date' => strtotime($_POST['expiry_date']),
                'is_unlimited' => 0, // Resellers cannot be unlimited
                'initial_balance' => floatval($_POST['initial_balance'] ?? 0),
                'discount_rate' => min(floatval($_POST['discount_rate'] ?? 0), $vendor['discount_rate']),
                'can_create_products' => 0,
                'payment_access' => 0
            ];
            
            try {
                $generatedToken = $tokenModel->generate($data);
                
                // Deduct balance if not unlimited
                if (!$vendor['is_unlimited'] && $data['initial_balance'] > 0) {
                    $currency->deductVendorBalance(
                        $vendor['id'],
                        $data['initial_balance'],
                        "Reseller token generated"
                    );
                }
                
                $message = "Reseller token generated successfully!";
                $messageType = 'success';
            } catch (Exception $e) {
                $message = "Error: " . $e->getMessage();
                $messageType = 'error';
            }
        } else {
            $message = "Insufficient balance";
            $messageType = 'error';
        }
    } elseif ($action === 'add_balance') {
        $resellerId = intval($_POST['reseller_id']);
        $amount = floatval($_POST['amount']);
        
        try {
            // Check vendor balance
            if (!$vendor['is_unlimited'] && $vendor['balance'] < $amount) {
                throw new Exception('Insufficient balance');
            }
            
            // Add to reseller
            $currency->addResellerBalance($resellerId, $amount, 'Balance added by vendor');
            
            // Deduct from vendor if not unlimited
            if (!$vendor['is_unlimited']) {
                $vendorCurrency = new Currency($db);
                $vendorCurrency->deductVendorBalance($vendor['id'], $amount, 'Balance transferred to reseller');
            }
            
            $message = "Balance added successfully";
            $messageType = 'success';
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'toggle_status') {
        $resellerId = intval($_POST['reseller_id']);
        $reseller = $resellerModel->getById($resellerId);
        
        if ($reseller) {
            $newStatus = $reseller['is_active'] ? 0 : 1;
            $resellerModel->update($resellerId, ['is_active' => $newStatus]);
            $message = $newStatus ? "Reseller activated" : "Reseller deactivated";
            $messageType = 'success';
        }
    }
}

// Get resellers
$search = $_GET['search'] ?? '';
$filters = [
    'search' => $search,
    'is_active' => $_GET['status'] ?? null
];
$resellers = $resellerModel->list($filters);
$stats = $resellerModel->getStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resellers - Vendor Portal</title>
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
                <h1>Resellers Management</h1>
                <div class="header-actions">
                    <button class="btn-primary" onclick="showGenerateForm()">
                        + Generate Reseller Token
                    </button>
                </div>
            </header>
            
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>" style="margin: 24px 40px;">
                <?php echo htmlspecialchars($message); ?>
                <?php if ($generatedToken): ?>
                <div style="margin-top: 12px; padding: 12px; background: var(--secondary-bg); border-radius: 8px; font-family: monospace; word-break: break-all;">
                    <?php echo $generatedToken; ?>
                </div>
                <button onclick="copyToken('<?php echo $generatedToken; ?>')" class="btn-secondary" style="margin-top: 12px;">
                    📋 Copy Token
                </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Stats -->
            <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4A9EFF, #7B61FF);">
                        👥
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total Resellers</div>
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
            
            <!-- Generate Token Form -->
            <div id="generateForm" style="display: none; padding: 0 40px 32px;">
                <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 16px; padding: 32px;">
                    <h2 style="margin-bottom: 24px;">Generate Reseller Token</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="generate_token">
                        
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px;">
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Expiry Date *</label>
                                <input type="date" name="expiry_date" required min="<?php echo date('Y-m-d'); ?>"
                                       value="<?php echo date('Y-m-d', min($vendor['expiry_date'], strtotime('+1 year'))); ?>"
                                       max="<?php echo date('Y-m-d', $vendor['expiry_date']); ?>"
                                       style="width: 100%; padding: 12px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary);">
                                <small style="color: var(--text-secondary); font-size: 12px;">Cannot exceed your expiry: <?php echo date('Y-m-d', $vendor['expiry_date']); ?></small>
                            </div>
                            
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Initial Balance ($)</label>
                                <input type="number" name="initial_balance" step="0.01" min="0" value="0"
                                       <?php if (!$vendor['is_unlimited']): ?>
                                       max="<?php echo $vendor['balance']; ?>"
                                       <?php endif; ?>
                                       style="width: 100%; padding: 12px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary);">
                                <?php if (!$vendor['is_unlimited']): ?>
                                <small style="color: var(--text-secondary); font-size: 12px;">Your balance: $<?php echo number_format($vendor['balance'], 2); ?></small>
                                <?php endif; ?>
                            </div>
                            
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Discount Rate (%)</label>
                                <input type="number" name="discount_rate" step="0.01" min="0" max="<?php echo $vendor['discount_rate']; ?>" value="0"
                                       style="width: 100%; padding: 12px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary);">
                                <small style="color: var(--text-secondary); font-size: 12px;">Max: <?php echo $vendor['discount_rate']; ?>%</small>
                            </div>
                        </div>
                        
                        <div style="margin-top: 24px; display: flex; gap: 12px;">
                            <button type="submit" class="btn-primary">Generate Token</button>
                            <button type="button" class="btn-secondary" onclick="hideGenerateForm()">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="admin-section">
                <form method="GET" class="filters-bar">
                    <input type="text" name="search" placeholder="Search resellers..." 
                           value="<?php echo htmlspecialchars($search); ?>"
                           style="flex: 1; padding: 12px 16px; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary);">
                    
                    <select name="status" style="padding: 12px 16px; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary);">
                        <option value="">All Status</option>
                        <option value="1" <?php echo ($_GET['status'] ?? '') === '1' ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo ($_GET['status'] ?? '') === '0' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                    
                    <button type="submit" class="btn-secondary">Filter</button>
                    <?php if ($search || isset($_GET['status'])): ?>
                    <a href="resellers.php" class="btn-secondary">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Resellers Table -->
            <div class="admin-section">
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Code</th>
                                <th>Balance</th>
                                <th>Discount</th>
                                <th>Status</th>
                                <th>Expiry</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($resellers)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 60px;">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">👥</div>
                                        <div class="empty-state-title">No resellers found</div>
                                        <div class="empty-state-text">Generate a reseller token to create your first reseller</div>
                                        <button class="btn-primary" onclick="showGenerateForm()" style="margin-top: 16px;">
                                            Generate Token
                                        </button>
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
                                <td><?php echo $reseller['discount_rate']; ?>%</td>
                                <td>
                                    <?php if ($reseller['is_active']): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $daysLeft = floor(($reseller['expiry_date'] - time()) / 86400);
                                    if ($daysLeft < 0) {
                                        echo '<span class="badge badge-danger">Expired</span>';
                                    } elseif ($daysLeft < 30) {
                                        echo '<span class="badge badge-warning">' . $daysLeft . ' days</span>';
                                    } else {
                                        echo date('Y-m-d', $reseller['expiry_date']);
                                    }
                                    ?>
                                </td>
                                <td>
                                    <button class="btn-icon" onclick="addBalance(<?php echo $reseller['id']; ?>, '<?php echo htmlspecialchars($reseller['name']); ?>')" title="Add Balance">💰</button>
                                    <button class="btn-icon" onclick="toggleStatus(<?php echo $reseller['id']; ?>)" title="Toggle Status">🔄</button>
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
                <input type="hidden" name="reseller_id" id="modal_reseller_id">
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Reseller</label>
                    <input type="text" id="modal_reseller_name" readonly 
                           style="width: 100%; padding: 12px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary);">
                </div>
                
                <div style="margin-bottom: 24px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Amount ($)</label>
                    <input type="number" name="amount" step="0.01" min="0.01" required
                           <?php if (!$vendor['is_unlimited']): ?>
                           max="<?php echo $vendor['balance']; ?>"
                           <?php endif; ?>
                           style="width: 100%; padding: 12px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary);">
                    <?php if (!$vendor['is_unlimited']): ?>
                    <small style="color: var(--text-secondary); font-size: 12px;">Your balance: $<?php echo number_format($vendor['balance'], 2); ?></small>
                    <?php endif; ?>
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
        <input type="hidden" name="reseller_id" id="toggle_reseller_id">
    </form>
    
    <script src="../assets/js/script.js"></script>
    <script src="../admin/admin-script.js"></script>
    <script>
        function showGenerateForm() {
            document.getElementById('generateForm').style.display = 'block';
            document.getElementById('generateForm').scrollIntoView({ behavior: 'smooth' });
        }
        
        function hideGenerateForm() {
            document.getElementById('generateForm').style.display = 'none';
        }
        
        function copyToken(token) {
            navigator.clipboard.writeText(token).then(() => {
                alert('Token copied to clipboard!');
            });
        }
        
        function addBalance(resellerId, resellerName) {
            document.getElementById('modal_reseller_id').value = resellerId;
            document.getElementById('modal_reseller_name').value = resellerName;
            document.getElementById('balanceModal').style.display = 'flex';
        }
        
        function closeModal() {
            document.getElementById('balanceModal').style.display = 'none';
        }
        
        function toggleStatus(resellerId) {
            if (confirm('Are you sure you want to toggle this reseller\'s status?')) {
                document.getElementById('toggle_reseller_id').value = resellerId;
                document.getElementById('toggleForm').submit();
            }
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

<?php
/**
 * Token Management Page
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

$tokenModel = new Token();
$message = '';
$messageType = '';
$generatedToken = '';

// Handle token generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate') {
    $data = [
        'role' => $_POST['role'],
        'parent_id' => $user['id'],
        'expiry_date' => strtotime($_POST['expiry_date']),
        'is_unlimited' => isset($_POST['is_unlimited']) ? 1 : 0,
        'initial_balance' => floatval($_POST['initial_balance'] ?? 0),
        'discount_rate' => floatval($_POST['discount_rate'] ?? 0),
        'can_create_products' => isset($_POST['can_create_products']) ? 1 : 0,
        'payment_access' => isset($_POST['payment_access']) ? 1 : 0
    ];
    
    try {
        $generatedToken = $tokenModel->generate($data);
        $message = "Token generated successfully!";
        $messageType = 'success';
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Get tokens
$tokens = $tokenModel->listByParent($user['id'], true);
$stats = $tokenModel->getStats($user['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tokens - Licensify Admin</title>
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
                <h1>Access Tokens</h1>
                <div class="header-actions">
                    <button class="btn-primary" onclick="showGenerateForm()">
                        + Generate Token
                    </button>
                </div>
            </header>
            
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>" style="margin: 24px 40px; padding: 16px 20px; border-radius: 12px; background: <?php echo $messageType === 'success' ? 'rgba(0, 212, 170, 0.1)' : 'rgba(239, 68, 68, 0.1)'; ?>; border: 1px solid <?php echo $messageType === 'success' ? 'rgba(0, 212, 170, 0.3)' : 'rgba(239, 68, 68, 0.3)'; ?>; color: <?php echo $messageType === 'success' ? '#00D4AA' : '#ef4444'; ?>;">
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
                        🎫
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total Tokens</div>
                        <div class="stat-value"><?php echo $stats['total'] ?? 0; ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #00D4AA, #00A8CC);">
                        ✓
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Unused</div>
                        <div class="stat-value"><?php echo $stats['unused'] ?? 0; ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #A855F7, #7C3AED);">
                        ✓
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Used</div>
                        <div class="stat-value"><?php echo $stats['used'] ?? 0; ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #F59E0B, #D97706);">
                        ⏰
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Expired</div>
                        <div class="stat-value"><?php echo $stats['expired'] ?? 0; ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Generate Form (Hidden by default) -->
            <div id="generateForm" style="display: none; padding: 0 40px 32px;">
                <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 16px; padding: 32px;">
                    <h2 style="margin-bottom: 24px;">Generate New Token</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="generate">
                        
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px;">
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Role *</label>
                                <select name="role" required style="width: 100%; padding: 12px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary);">
                                    <option value="vendor">Vendor</option>
                                    <option value="reseller">Reseller</option>
                                </select>
                            </div>
                            
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Expiry Date *</label>
                                <input type="date" name="expiry_date" required min="<?php echo date('Y-m-d'); ?>"
                                       value="<?php echo date('Y-m-d', strtotime('+1 year')); ?>"
                                       style="width: 100%; padding: 12px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary);">
                            </div>
                            
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Initial Balance ($)</label>
                                <input type="number" name="initial_balance" step="0.01" min="0" value="0"
                                       style="width: 100%; padding: 12px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary);">
                            </div>
                            
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Discount Rate (%)</label>
                                <input type="number" name="discount_rate" step="0.01" min="0" max="100" value="0"
                                       style="width: 100%; padding: 12px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary);">
                            </div>
                        </div>
                        
                        <div style="margin-top: 24px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                            <label style="display: flex; align-items: center; gap: 12px; padding: 16px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 10px; cursor: pointer;">
                                <input type="checkbox" name="is_unlimited" style="width: 20px; height: 20px;">
                                <span>Unlimited Currency</span>
                            </label>
                            
                            <label style="display: flex; align-items: center; gap: 12px; padding: 16px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 10px; cursor: pointer;">
                                <input type="checkbox" name="can_create_products" style="width: 20px; height: 20px;">
                                <span>Can Create Products</span>
                            </label>
                            
                            <label style="display: flex; align-items: center; gap: 12px; padding: 16px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 10px; cursor: pointer;">
                                <input type="checkbox" name="payment_access" style="width: 20px; height: 20px;">
                                <span>Payment Access</span>
                            </label>
                        </div>
                        
                        <div style="margin-top: 24px; display: flex; gap: 12px;">
                            <button type="submit" class="btn-primary">Generate Token</button>
                            <button type="button" class="btn-secondary" onclick="hideGenerateForm()">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Tokens Table -->
            <div class="admin-section">
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Token</th>
                                <th>Role</th>
                                <th>Balance</th>
                                <th>Discount</th>
                                <th>Permissions</th>
                                <th>Expiry</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tokens)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 60px;">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">🎫</div>
                                        <div class="empty-state-title">No tokens yet</div>
                                        <div class="empty-state-text">Generate your first access token</div>
                                        <button class="btn-primary" onclick="showGenerateForm()" style="margin-top: 16px;">
                                            Generate Token
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($tokens as $token): ?>
                            <tr>
                                <td>
                                    <code style="font-size: 11px;"><?php echo substr($token['token'], 0, 16); ?>...</code>
                                    <button onclick="copyToken('<?php echo $token['token']; ?>')" class="btn-icon" style="margin-left: 8px;" title="Copy">📋</button>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $token['role'] === 'vendor' ? 'info' : 'warning'; ?>">
                                        <?php echo ucfirst($token['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($token['is_unlimited']): ?>
                                        <span class="badge badge-success">Unlimited</span>
                                    <?php else: ?>
                                        $<?php echo number_format($token['initial_balance'], 2); ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $token['discount_rate']; ?>%</td>
                                <td>
                                    <?php if ($token['can_create_products']): ?>
                                        <span title="Can create products">📦</span>
                                    <?php endif; ?>
                                    <?php if ($token['payment_access']): ?>
                                        <span title="Payment access">💳</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('Y-m-d', $token['expiry_date']); ?></td>
                                <td>
                                    <?php if ($token['consumed_at']): ?>
                                        <span class="badge badge-success">Used</span>
                                    <?php elseif ($token['expiry_date'] < time()): ?>
                                        <span class="badge badge-danger">Expired</span>
                                    <?php else: ?>
                                        <span class="badge badge-info">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('Y-m-d', $token['created_at']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/script.js"></script>
    <script src="admin-script.js"></script>
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
    </script>
</body>
</html>

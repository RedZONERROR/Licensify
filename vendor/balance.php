<?php
/**
 * Balance & Transactions Page
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

// Get transactions
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$stmt = $db->query(
    "SELECT * FROM currency_transactions 
     WHERE vendor_id = ? 
     ORDER BY created_at DESC 
     LIMIT ? OFFSET ?",
    [$vendor['id'], $perPage, $offset]
);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total transactions count
$stmt = $db->query(
    "SELECT COUNT(*) FROM currency_transactions WHERE vendor_id = ?",
    [$vendor['id']]
);
$totalTransactions = $stmt->fetchColumn();
$totalPages = ceil($totalTransactions / $perPage);

// Calculate statistics
$stmt = $db->query(
    "SELECT 
        SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) as total_credits,
        SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) as total_debits,
        COUNT(*) as transaction_count
     FROM currency_transactions 
     WHERE vendor_id = ?",
    [$vendor['id']]
);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Get monthly breakdown
$stmt = $db->query(
    "SELECT 
        strftime('%Y-%m', datetime(created_at, 'unixepoch')) as month,
        SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) as credits,
        SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) as debits
     FROM currency_transactions 
     WHERE vendor_id = ?
     GROUP BY month
     ORDER BY month DESC
     LIMIT 12",
    [$vendor['id']]
);
$monthlyStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Balance & Transactions - Vendor Portal</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../admin/admin-styles.css">
    <style>
        .balance-overview {
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple));
            border-radius: 20px;
            padding: 40px;
            margin: 32px 40px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .balance-overview::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }
        
        .balance-amount {
            font-size: 56px;
            font-weight: 700;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }
        
        .balance-label {
            font-size: 16px;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        .balance-details {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            margin-top: 32px;
            position: relative;
            z-index: 1;
        }
        
        .balance-detail-item {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 20px;
        }
        
        .balance-detail-value {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        
        .balance-detail-label {
            font-size: 13px;
            opacity: 0.9;
        }
        
        .chart-container {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 32px;
        }
        
        .chart-bar {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 16px;
        }
        
        .chart-label {
            width: 80px;
            font-size: 13px;
            color: var(--text-secondary);
            text-align: right;
        }
        
        .chart-bars {
            flex: 1;
            display: flex;
            gap: 4px;
            height: 32px;
        }
        
        .chart-bar-credit {
            background: linear-gradient(135deg, var(--accent-green), var(--accent-cyan));
            border-radius: 4px;
            transition: all 0.3s;
        }
        
        .chart-bar-debit {
            background: linear-gradient(135deg, var(--accent-red), var(--accent-orange));
            border-radius: 4px;
            transition: all 0.3s;
        }
        
        .chart-bar-credit:hover,
        .chart-bar-debit:hover {
            opacity: 0.8;
            transform: scaleY(1.1);
        }
        
        .chart-values {
            width: 120px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 24px;
        }
        
        .pagination a,
        .pagination span {
            padding: 8px 16px;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .pagination a:hover {
            border-color: var(--accent-blue);
            color: var(--accent-blue);
        }
        
        .pagination .active {
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple));
            border-color: transparent;
            color: white;
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
                <h1>Balance & Transactions</h1>
                <div class="header-actions">
                    <button class="btn-secondary" onclick="window.print()">
                        🖨️ Print Report
                    </button>
                </div>
            </header>
            
            <!-- Balance Overview -->
            <div class="balance-overview">
                <div class="balance-label">Current Balance</div>
                <div class="balance-amount">
                    <?php if ($vendor['is_unlimited']): ?>
                        ∞
                    <?php else: ?>
                        $<?php echo number_format($vendor['balance'], 2); ?>
                    <?php endif; ?>
                </div>
                
                <div class="balance-details">
                    <div class="balance-detail-item">
                        <div class="balance-detail-value">$<?php echo number_format($stats['total_credits'] ?? 0, 2); ?></div>
                        <div class="balance-detail-label">Total Credits</div>
                    </div>
                    
                    <div class="balance-detail-item">
                        <div class="balance-detail-value">$<?php echo number_format($stats['total_debits'] ?? 0, 2); ?></div>
                        <div class="balance-detail-label">Total Debits</div>
                    </div>
                    
                    <div class="balance-detail-item">
                        <div class="balance-detail-value"><?php echo $stats['transaction_count'] ?? 0; ?></div>
                        <div class="balance-detail-label">Total Transactions</div>
                    </div>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #00D4AA, #00A8CC);">
                        ↑
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Credits</div>
                        <div class="stat-value">$<?php echo number_format($stats['total_credits'] ?? 0, 2); ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                        ↓
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Debits</div>
                        <div class="stat-value">$<?php echo number_format($stats['total_debits'] ?? 0, 2); ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4A9EFF, #7B61FF);">
                        📊
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Net Flow</div>
                        <div class="stat-value">$<?php echo number_format(($stats['total_credits'] ?? 0) - ($stats['total_debits'] ?? 0), 2); ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #A855F7, #7C3AED);">
                        🔄
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Transactions</div>
                        <div class="stat-value"><?php echo $stats['transaction_count'] ?? 0; ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Monthly Chart -->
            <?php if (!empty($monthlyStats)): ?>
            <div class="admin-section">
                <div class="chart-container">
                    <h3 style="margin-bottom: 24px;">Monthly Activity</h3>
                    
                    <?php 
                    $maxAmount = 0;
                    foreach ($monthlyStats as $month) {
                        $maxAmount = max($maxAmount, $month['credits'], $month['debits']);
                    }
                    $maxAmount = max($maxAmount, 1); // Avoid division by zero
                    ?>
                    
                    <?php foreach ($monthlyStats as $month): ?>
                    <div class="chart-bar">
                        <div class="chart-label"><?php echo date('M Y', strtotime($month['month'] . '-01')); ?></div>
                        <div class="chart-bars">
                            <div class="chart-bar-credit" 
                                 style="width: <?php echo ($month['credits'] / $maxAmount * 100); ?>%;"
                                 title="Credits: $<?php echo number_format($month['credits'], 2); ?>">
                            </div>
                            <div class="chart-bar-debit" 
                                 style="width: <?php echo ($month['debits'] / $maxAmount * 100); ?>%;"
                                 title="Debits: $<?php echo number_format($month['debits'], 2); ?>">
                            </div>
                        </div>
                        <div class="chart-values">
                            <span style="color: var(--accent-green);">+$<?php echo number_format($month['credits'], 0); ?></span>
                            <span style="color: var(--accent-red); margin-left: 8px;">-$<?php echo number_format($month['debits'], 0); ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border-color); display: flex; gap: 24px; font-size: 13px;">
                        <div>
                            <span style="display: inline-block; width: 12px; height: 12px; background: linear-gradient(135deg, var(--accent-green), var(--accent-cyan)); border-radius: 2px; margin-right: 6px;"></span>
                            Credits
                        </div>
                        <div>
                            <span style="display: inline-block; width: 12px; height: 12px; background: linear-gradient(135deg, var(--accent-red), var(--accent-orange)); border-radius: 2px; margin-right: 6px;"></span>
                            Debits
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Transaction History -->
            <div class="admin-section">
                <h2 style="margin-bottom: 24px;">Transaction History</h2>
                
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Balance After</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($transactions)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 60px;">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">💰</div>
                                        <div class="empty-state-title">No transactions yet</div>
                                        <div class="empty-state-text">Your transaction history will appear here</div>
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td><?php echo date('Y-m-d H:i:s', $transaction['created_at']); ?></td>
                                <td>
                                    <?php if ($transaction['type'] === 'credit'): ?>
                                        <span class="badge badge-success">Credit</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Debit</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                                <td>
                                    <?php if ($transaction['type'] === 'credit'): ?>
                                        <span style="color: var(--accent-green); font-weight: 600;">
                                            +$<?php echo number_format($transaction['amount'], 2); ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: var(--accent-red); font-weight: 600;">
                                            -$<?php echo number_format($transaction['amount'], 2); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong>$<?php echo number_format($transaction['balance_after'], 2); ?></strong>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>">← Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>">Next →</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/script.js"></script>
    <script src="../admin/admin-script.js"></script>
</body>
</html>

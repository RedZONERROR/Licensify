<?php
/**
 * Licenses Management Page
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

$licenseModel = new License($_SESSION['vendor_code']);
$resellerModel = new Reseller($_SESSION['vendor_code']);
$productModel = new Product();
$currency = new Currency(new Database($_SESSION['vendor_code']));

$message = '';
$messageType = '';
$generatedLicense = null;

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'generate') {
        $data = [
            'product_id' => intval($_POST['product_id']),
            'product_name' => $_POST['product_name'],
            'base_price' => floatval($_POST['base_price']),
            'reseller_id' => !empty($_POST['reseller_id']) ? intval($_POST['reseller_id']) : null,
            'customer_email' => $_POST['customer_email'] ?? null,
            'customer_name' => $_POST['customer_name'] ?? null,
            'max_devices' => intval($_POST['max_devices'] ?? 1),
            'duration' => $_POST['duration'],
            'discount_rate' => floatval($_POST['discount_rate'] ?? 0)
        ];
        
        try {
            // Calculate price
            $price = $currency->calculateLicensePrice(
                $data['base_price'],
                $data['max_devices'],
                $data['duration'],
                $data['discount_rate']
            );
            
            // Check balance
            if ($data['reseller_id']) {
                $balance = $currency->getResellerBalance($data['reseller_id']);
                if (!$balance['is_unlimited'] && $balance['balance'] < $price) {
                    throw new Exception('Reseller has insufficient balance');
                }
            }
            
            $generatedLicense = $licenseModel->generate($data);
            $message = "License generated successfully!";
            $messageType = 'success';
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'suspend') {
        $licenseKey = $_POST['license_key'];
        try {
            $licenseModel->suspend($licenseModel->getByKey($licenseKey)['id']);
            $message = "License suspended successfully";
            $messageType = 'success';
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'unsuspend') {
        $licenseKey = $_POST['license_key'];
        try {
            $licenseModel->unsuspend($licenseModel->getByKey($licenseKey)['id']);
            $message = "License unsuspended successfully";
            $messageType = 'success';
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'suspend_device') {
        $licenseKey = $_POST['license_key'];
        $hwid = $_POST['hwid'];
        try {
            $licenseModel->suspendDevice($licenseModel->getByKey($licenseKey)['id'], $hwid);
            $message = "Device suspended successfully";
            $messageType = 'success';
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'unsuspend_device') {
        $licenseKey = $_POST['license_key'];
        $hwid = $_POST['hwid'];
        try {
            $licenseModel->unsuspendDevice($licenseModel->getByKey($licenseKey)['id'], $hwid);
            $message = "Device unsuspended successfully";
            $messageType = 'success';
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get licenses
$search = $_GET['search'] ?? '';
$filters = [
    'search' => $search,
    'is_active' => $_GET['status'] ?? null,
    'reseller_id' => $_GET['reseller_id'] ?? null
];
$licenses = $licenseModel->list($filters);
$stats = $licenseModel->getStats();

// Get products and resellers for form
$products = $productModel->getVendorProducts($vendor['id']);
$resellers = $resellerModel->list(['is_active' => 1]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Licenses - Vendor Portal</title>
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
                <h1>License Management</h1>
                <div class="header-actions">
                    <button class="btn-primary" onclick="showGenerateForm()">
                        + Generate License
                    </button>
                </div>
            </header>
            
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>" style="margin: 24px 40px;">
                <?php echo htmlspecialchars($message); ?>
                <?php if ($generatedLicense): ?>
                <div style="margin-top: 16px;">
                    <div style="font-weight: 600; margin-bottom: 8px;">License Details:</div>
                    <div style="background: var(--secondary-bg); padding: 16px; border-radius: 8px;">
                        <div style="margin-bottom: 8px;">
                            <strong>License Key:</strong>
                            <code style="margin-left: 8px; font-size: 14px;"><?php echo $generatedLicense['license_key']; ?></code>
                            <button onclick="copyText('<?php echo $generatedLicense['license_key']; ?>')" class="btn-icon" style="margin-left: 8px;">📋</button>
                        </div>
                        <div style="margin-bottom: 8px;">
                            <strong>Price:</strong> $<?php echo number_format($generatedLicense['price'], 2); ?>
                        </div>
                        <div>
                            <strong>Expires:</strong> <?php echo date('Y-m-d H:i:s', $generatedLicense['expires_at']); ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Stats -->
            <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4A9EFF, #7B61FF);">
                        🔑
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total Licenses</div>
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
                    <div class="stat-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                        ⏸
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Suspended</div>
                        <div class="stat-value"><?php echo $stats['suspended'] ?? 0; ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #F59E0B, #D97706);">
                        💰
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Revenue</div>
                        <div class="stat-value">$<?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Generate License Form -->
            <div id="generateForm" style="display: none; padding: 0 40px 32px;">
                <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 16px; padding: 32px;">
                    <h2 style="margin-bottom: 24px;">Generate New License</h2>
                    <form method="POST" id="licenseForm">
                        <input type="hidden" name="action" value="generate">
                        
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px;">
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Product *</label>
                                <select name="product_id" id="product_select" required onchange="updateProductInfo()"
                                        style="width: 100%; padding: 12px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary);">
                                    <option value="">Select Product</option>
                                    <?php foreach ($products as $product): ?>
                                    <option value="<?php echo $product['id']; ?>" 
                                            data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                            data-price="<?php echo $product['base_price']; ?>">
                                        <?php echo htmlspecialchars($product['name']); ?> - $<?php echo number_format($product['base_price'], 2); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="product_name" id="product_name">
                                <input type="hidden" name="base_price" id="base_price">
                            </div>
                            
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Reseller (Optional)</label>
                                <select name="reseller_id" id="reseller_select" onchange="updateResellerInfo()"
                                        style="width: 100%; padding: 12px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary);">
                                    <option value="">Direct Sale (No Reseller)</option>
                                    <?php foreach ($resellers as $reseller): ?>
                                    <option value="<?php echo $reseller['id']; ?>"
                                            data-discount="<?php echo $reseller['discount_rate']; ?>"
                                            data-balance="<?php echo $reseller['balance']; ?>"
                                            data-unlimited="<?php echo $reseller['is_unlimited']; ?>">
                                        <?php echo htmlspecialchars($reseller['name']); ?> 
                                        (<?php echo $reseller['is_unlimited'] ? '∞' : '$' . number_format($reseller['balance'], 2); ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <small style="color: var(--text-secondary); font-size: 12px;">Leave empty for direct vendor sale</small>
                            </div>
                            
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Duration *</label>
                                <select name="duration" id="duration_select" required onchange="calculatePrice()"
                                        style="width: 100%; padding: 12px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary);">
                                    <option value="1hr">1 Hour</option>
                                    <option value="1d" selected>1 Day</option>
                                    <option value="7d">7 Days</option>
                                    <option value="15d">15 Days</option>
                                    <option value="30d">30 Days</option>
                                    <option value="60d">60 Days</option>
                                </select>
                            </div>
                            
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Max Devices *</label>
                                <input type="number" name="max_devices" id="max_devices" value="1" min="1" max="100" required onchange="calculatePrice()"
                                       style="width: 100%; padding: 12px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary);">
                            </div>
                            
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Customer Email</label>
                                <input type="email" name="customer_email"
                                       style="width: 100%; padding: 12px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary);">
                            </div>
                            
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Customer Name</label>
                                <input type="text" name="customer_name"
                                       style="width: 100%; padding: 12px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary);">
                            </div>
                            
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Discount Rate (%)</label>
                                <input type="number" name="discount_rate" id="discount_rate" value="0" min="0" max="<?php echo $vendor['discount_rate']; ?>" step="0.01" onchange="calculatePrice()"
                                       style="width: 100%; padding: 12px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary);">
                                <small style="color: var(--text-secondary); font-size: 12px;">Max: <?php echo $vendor['discount_rate']; ?>%</small>
                            </div>
                            
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Calculated Price</label>
                                <input type="text" id="calculated_price" value="$0.00" readonly
                                       style="width: 100%; padding: 12px; background: var(--primary-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--accent-green); font-size: 18px; font-weight: 700; cursor: not-allowed;">
                            </div>
                        </div>
                        
                        <div style="margin-top: 24px; display: flex; gap: 12px;">
                            <button type="submit" class="btn-primary">Generate License</button>
                            <button type="button" class="btn-secondary" onclick="hideGenerateForm()">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="admin-section">
                <form method="GET" class="filters-bar">
                    <input type="text" name="search" placeholder="Search licenses..." 
                           value="<?php echo htmlspecialchars($search); ?>"
                           style="flex: 1; padding: 12px 16px; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary);">
                    
                    <select name="status" style="padding: 12px 16px; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary);">
                        <option value="">All Status</option>
                        <option value="1" <?php echo ($_GET['status'] ?? '') === '1' ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo ($_GET['status'] ?? '') === '0' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                    
                    <select name="reseller_id" style="padding: 12px 16px; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary);">
                        <option value="">All Resellers</option>
                        <option value="0" <?php echo ($_GET['reseller_id'] ?? '') === '0' ? 'selected' : ''; ?>>Direct Sales</option>
                        <?php foreach ($resellers as $reseller): ?>
                        <option value="<?php echo $reseller['id']; ?>" <?php echo ($_GET['reseller_id'] ?? '') == $reseller['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($reseller['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button type="submit" class="btn-secondary">Filter</button>
                    <?php if ($search || isset($_GET['status']) || isset($_GET['reseller_id'])): ?>
                    <a href="licenses.php" class="btn-secondary">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Licenses Table -->
            <div class="admin-section">
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
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($licenses)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 60px;">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">🔑</div>
                                        <div class="empty-state-title">No licenses found</div>
                                        <div class="empty-state-text">Generate your first license to get started</div>
                                        <button class="btn-primary" onclick="showGenerateForm()" style="margin-top: 16px;">
                                            Generate License
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($licenses as $license): ?>
                            <tr>
                                <td>
                                    <code><?php echo htmlspecialchars(substr($license['license_key'], 0, 20)); ?>...</code>
                                    <button onclick="copyText('<?php echo $license['license_key']; ?>')" class="btn-icon" title="Copy">📋</button>
                                </td>
                                <td><strong><?php echo htmlspecialchars($license['product_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($license['customer_email'] ?? 'N/A'); ?></td>
                                <td><?php echo $license['max_devices']; ?></td>
                                <td>$<?php echo number_format($license['price'], 2); ?></td>
                                <td>
                                    <?php 
                                    if ($license['expires_at'] < time()) {
                                        echo '<span class="badge badge-danger">Expired</span>';
                                    } else {
                                        $daysLeft = floor(($license['expires_at'] - time()) / 86400);
                                        if ($daysLeft < 7) {
                                            echo '<span class="badge badge-warning">' . $daysLeft . ' days</span>';
                                        } else {
                                            echo date('Y-m-d', $license['expires_at']);
                                        }
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
                                <td>
                                    <button class="btn-icon" onclick="viewDevices('<?php echo $license['license_key']; ?>')" title="View Devices">👁️</button>
                                    <?php if ($license['is_suspended']): ?>
                                    <button class="btn-icon" onclick="unsuspendLicense('<?php echo $license['license_key']; ?>')" title="Unsuspend">▶️</button>
                                    <?php else: ?>
                                    <button class="btn-icon" onclick="suspendLicense('<?php echo $license['license_key']; ?>')" title="Suspend">⏸</button>
                                    <?php endif; ?>
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
    
    <!-- Hidden Forms -->
    <form id="suspendForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="suspend">
        <input type="hidden" name="license_key" id="suspend_license_key">
    </form>
    
    <form id="unsuspendForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="unsuspend">
        <input type="hidden" name="license_key" id="unsuspend_license_key">
    </form>
    
    <script src="../assets/js/script.js"></script>
    <script src="../admin/admin-script.js"></script>
    <script>
        const durations = <?php echo json_encode(LICENSE_DURATIONS); ?>;
        
        function showGenerateForm() {
            document.getElementById('generateForm').style.display = 'block';
            document.getElementById('generateForm').scrollIntoView({ behavior: 'smooth' });
        }
        
        function hideGenerateForm() {
            document.getElementById('generateForm').style.display = 'none';
        }
        
        function updateProductInfo() {
            const select = document.getElementById('product_select');
            const option = select.options[select.selectedIndex];
            
            if (option.value) {
                document.getElementById('product_name').value = option.dataset.name;
                document.getElementById('base_price').value = option.dataset.price;
                calculatePrice();
            }
        }
        
        function updateResellerInfo() {
            const select = document.getElementById('reseller_select');
            const option = select.options[select.selectedIndex];
            
            if (option.value && option.dataset.discount) {
                document.getElementById('discount_rate').value = option.dataset.discount;
                calculatePrice();
            }
        }
        
        function calculatePrice() {
            const basePrice = parseFloat(document.getElementById('base_price').value) || 0;
            const devices = parseInt(document.getElementById('max_devices').value) || 1;
            const duration = document.getElementById('duration_select').value;
            const discount = parseFloat(document.getElementById('discount_rate').value) || 0;
            
            if (basePrice > 0 && duration) {
                const durationSeconds = durations[duration] || 86400;
                const durationDays = durationSeconds / 86400;
                
                let price = basePrice * devices * durationDays;
                
                if (discount > 0) {
                    price = price * (1 - (discount / 100));
                }
                
                document.getElementById('calculated_price').value = '$' + price.toFixed(2);
            }
        }
        
        function copyText(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Copied to clipboard!');
            });
        }
        
        function viewDevices(licenseKey) {
            window.location.href = 'licenses.php?view=' + licenseKey;
        }
        
        function suspendLicense(licenseKey) {
            if (confirm('Are you sure you want to suspend this license?')) {
                document.getElementById('suspend_license_key').value = licenseKey;
                document.getElementById('suspendForm').submit();
            }
        }
        
        function unsuspendLicense(licenseKey) {
            if (confirm('Are you sure you want to unsuspend this license?')) {
                document.getElementById('unsuspend_license_key').value = licenseKey;
                document.getElementById('unsuspendForm').submit();
            }
        }
    </script>
</body>
</html>

<?php
/**
 * Products Management Page
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

$productModel = new Product();

$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_custom' && $vendor['can_create_products']) {
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'] ?? '',
            'base_price' => floatval($_POST['base_price'])
        ];
        
        try {
            $productModel->createVendorProduct($vendor['id'], $data);
            $message = "Custom product created successfully";
            $messageType = 'success';
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'sync_all') {
        try {
            $synced = $productModel->syncAllToVendor($vendor['id']);
            $message = "Synced {$synced} products from global catalog";
            $messageType = 'success';
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get products
$products = $productModel->getVendorProducts($vendor['id']);
$globalProducts = $productModel->list(true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Vendor Portal</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../admin/admin-styles.css">
    <style>
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
            padding: 0 40px 40px;
        }
        
        .product-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 24px;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .product-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-blue), var(--accent-purple));
            transform: scaleX(0);
            transition: transform 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
            border-color: var(--accent-blue);
        }
        
        .product-card:hover::before {
            transform: scaleX(1);
        }
        
        .product-icon {
            width: 64px;
            height: 64px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 16px;
        }
        
        .product-name {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .product-description {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 16px;
            line-height: 1.5;
            min-height: 42px;
        }
        
        .product-price {
            font-size: 28px;
            font-weight: 700;
            color: var(--accent-green);
            margin-bottom: 12px;
        }
        
        .product-meta {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .product-badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-custom {
            background: rgba(123, 97, 255, 0.15);
            color: var(--accent-purple);
        }
        
        .badge-global {
            background: rgba(0, 212, 170, 0.15);
            color: var(--accent-green);
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
                <h1>Products</h1>
                <div class="header-actions">
                    <?php if ($vendor['can_create_products']): ?>
                    <button class="btn-primary" onclick="showCreateForm()">
                        + Create Custom Product
                    </button>
                    <?php endif; ?>
                    <button class="btn-secondary" onclick="showSyncModal()">
                        🔄 Sync Global Products
                    </button>
                </div>
            </header>
            
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>" style="margin: 24px 40px;">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>
            
            <!-- Stats -->
            <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4A9EFF, #7B61FF);">
                        📦
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total Products</div>
                        <div class="stat-value"><?php echo count($products); ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #00D4AA, #00A8CC);">
                        🌍
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Global Products</div>
                        <div class="stat-value"><?php echo count(array_filter($products, fn($p) => !$p['is_custom'])); ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #A855F7, #7C3AED);">
                        ⭐
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Custom Products</div>
                        <div class="stat-value"><?php echo count(array_filter($products, fn($p) => $p['is_custom'])); ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Create Custom Product Form -->
            <?php if ($vendor['can_create_products']): ?>
            <div id="createForm" style="display: none; padding: 0 40px 32px;">
                <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 16px; padding: 32px;">
                    <h2 style="margin-bottom: 24px;">Create Custom Product</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="create_custom">
                        
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px;">
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Product Name *</label>
                                <input type="text" name="name" required
                                       style="width: 100%; padding: 12px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary);">
                            </div>
                            
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Base Price ($) *</label>
                                <input type="number" name="base_price" step="0.01" min="0.01" required
                                       style="width: 100%; padding: 12px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary);">
                                <small style="color: var(--text-secondary); font-size: 12px;">Price per device per day</small>
                            </div>
                        </div>
                        
                        <div style="margin-top: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Description</label>
                            <textarea name="description" rows="3"
                                      style="width: 100%; padding: 12px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary); resize: vertical;"></textarea>
                        </div>
                        
                        <div style="margin-top: 24px; display: flex; gap: 12px;">
                            <button type="submit" class="btn-primary">Create Product</button>
                            <button type="button" class="btn-secondary" onclick="hideCreateForm()">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Products Grid -->
            <?php if (empty($products)): ?>
            <div class="admin-section">
                <div style="text-align: center; padding: 80px 40px;">
                    <div class="empty-state">
                        <div class="empty-state-icon">📦</div>
                        <div class="empty-state-title">No products available</div>
                        <div class="empty-state-text">Sync global products or create custom products to get started</div>
                        <div style="margin-top: 24px; display: flex; gap: 12px; justify-content: center;">
                            <button class="btn-primary" onclick="showSyncModal()">
                                🔄 Sync Global Products
                            </button>
                            <?php if ($vendor['can_create_products']): ?>
                            <button class="btn-secondary" onclick="showCreateForm()">
                                + Create Custom Product
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-icon">📦</div>
                    <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                    <div class="product-description">
                        <?php echo htmlspecialchars($product['description'] ?: 'No description available'); ?>
                    </div>
                    <div class="product-price">$<?php echo number_format($product['base_price'], 2); ?></div>
                    <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 12px;">
                        per device per day
                    </div>
                    <div class="product-meta">
                        <?php if ($product['is_custom']): ?>
                        <span class="product-badge badge-custom">Custom</span>
                        <?php else: ?>
                        <span class="product-badge badge-global">Global</span>
                        <?php endif; ?>
                        
                        <?php if ($product['is_active']): ?>
                        <span class="badge badge-success">Active</span>
                        <?php else: ?>
                        <span class="badge badge-danger">Inactive</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <!-- Available Global Products -->
            <?php if (!empty($globalProducts)): ?>
            <div class="admin-section">
                <h2 style="margin-bottom: 24px;">Available Global Products</h2>
                <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 16px; padding: 24px;">
                    <p style="color: var(--text-secondary); margin-bottom: 16px;">
                        These products are available in the global catalog. Click "Sync Global Products" to add them to your product list.
                    </p>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 16px;">
                        <?php foreach ($globalProducts as $gProduct): ?>
                        <div style="background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 16px;">
                            <div style="font-weight: 600; margin-bottom: 4px;"><?php echo htmlspecialchars($gProduct['name']); ?></div>
                            <div style="font-size: 20px; font-weight: 700; color: var(--accent-green);">
                                $<?php echo number_format($gProduct['base_price'], 2); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
    
    <!-- Sync Modal -->
    <div id="syncModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 16px; padding: 32px; max-width: 500px; width: 90%;">
            <h2 style="margin-bottom: 16px;">Sync Global Products</h2>
            <p style="color: var(--text-secondary); margin-bottom: 24px;">
                This will sync all global products from the owner catalog to your product list. Existing products will be updated.
            </p>
            <form method="POST">
                <input type="hidden" name="action" value="sync_all">
                <div style="display: flex; gap: 12px;">
                    <button type="submit" class="btn-primary" style="flex: 1;">Sync Products</button>
                    <button type="button" class="btn-secondary" onclick="closeSyncModal()" style="flex: 1;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
    <script src="../admin/admin-script.js"></script>
    <script>
        function showCreateForm() {
            document.getElementById('createForm').style.display = 'block';
            document.getElementById('createForm').scrollIntoView({ behavior: 'smooth' });
        }
        
        function hideCreateForm() {
            document.getElementById('createForm').style.display = 'none';
        }
        
        function showSyncModal() {
            document.getElementById('syncModal').style.display = 'flex';
        }
        
        function closeSyncModal() {
            document.getElementById('syncModal').style.display = 'none';
        }
        
        // Close modal on escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeSyncModal();
            }
        });
        
        // Close modal on outside click
        document.getElementById('syncModal').addEventListener('click', (e) => {
            if (e.target.id === 'syncModal') {
                closeSyncModal();
            }
        });
    </script>
</body>
</html>

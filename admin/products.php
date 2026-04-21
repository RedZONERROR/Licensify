<?php
/**
 * Products Management Page
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

$productModel = new Product();
$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'] ?? '',
            'base_price' => floatval($_POST['base_price'])
        ];
        
        try {
            $productModel->create($data);
            $message = "Product created successfully!";
            $messageType = 'success';
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'update') {
        $productId = intval($_POST['product_id']);
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'] ?? '',
            'base_price' => floatval($_POST['base_price']),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        try {
            $productModel->update($productId, $data);
            $message = "Product updated successfully!";
            $messageType = 'success';
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'delete') {
        $productId = intval($_POST['product_id']);
        
        try {
            $productModel->delete($productId);
            $message = "Product deleted successfully!";
            $messageType = 'success';
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get products
$products = $productModel->list(false); // Include inactive
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Licensify Admin</title>
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
                <h1>Products Management</h1>
                <div class="header-actions">
                    <button class="btn-primary" onclick="showCreateForm()">
                        + Add Product
                    </button>
                </div>
            </header>
            
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>" style="margin: 24px 40px; padding: 16px 20px; border-radius: 12px; background: <?php echo $messageType === 'success' ? 'rgba(0, 212, 170, 0.1)' : 'rgba(239, 68, 68, 0.1)'; ?>; border: 1px solid <?php echo $messageType === 'success' ? 'rgba(0, 212, 170, 0.3)' : 'rgba(239, 68, 68, 0.3)'; ?>; color: <?php echo $messageType === 'success' ? '#00D4AA' : '#ef4444'; ?>;">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>
            
            <!-- Create Form (Hidden by default) -->
            <div id="createForm" style="display: none; padding: 0 40px 32px;">
                <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 16px; padding: 32px;">
                    <h2 style="margin-bottom: 24px;">Add New Product</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="create">
                        
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Product Name *</label>
                            <input type="text" name="name" required
                                   style="width: 100%; padding: 12px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary);">
                        </div>
                        
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Description</label>
                            <textarea name="description" rows="3"
                                      style="width: 100%; padding: 12px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary); resize: vertical;"></textarea>
                        </div>
                        
                        <div style="margin-bottom: 24px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Base Price ($) *</label>
                            <input type="number" name="base_price" step="0.01" min="0" required
                                   style="width: 100%; padding: 12px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary);">
                            <small style="color: var(--text-secondary); font-size: 12px;">Price per device per day</small>
                        </div>
                        
                        <div style="display: flex; gap: 12px;">
                            <button type="submit" class="btn-primary">Create Product</button>
                            <button type="button" class="btn-secondary" onclick="hideCreateForm()">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="admin-section">
                <?php if (empty($products)): ?>
                <div class="empty-state" style="padding: 80px 20px;">
                    <div class="empty-state-icon">📦</div>
                    <div class="empty-state-title">No products yet</div>
                    <div class="empty-state-text">Create your first product to get started</div>
                    <button class="btn-primary" onclick="showCreateForm()" style="margin-top: 16px;">
                        Add Product
                    </button>
                </div>
                <?php else: ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px;">
                    <?php foreach ($products as $product): ?>
                    <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 16px; padding: 28px; position: relative;">
                        <?php if (!$product['is_active']): ?>
                        <div style="position: absolute; top: 16px; right: 16px;">
                            <span class="badge badge-danger">Inactive</span>
                        </div>
                        <?php endif; ?>
                        
                        <div style="width: 56px; height: 56px; border-radius: 12px; background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple)); display: flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 16px;">
                            📦
                        </div>
                        
                        <h3 style="font-size: 18px; margin-bottom: 8px;"><?php echo htmlspecialchars($product['name']); ?></h3>
                        
                        <?php if ($product['description']): ?>
                        <p style="color: var(--text-secondary); font-size: 14px; margin-bottom: 16px; line-height: 1.5;">
                            <?php echo htmlspecialchars($product['description']); ?>
                        </p>
                        <?php endif; ?>
                        
                        <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 16px; border-top: 1px solid var(--border-color);">
                            <div>
                                <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 4px;">Base Price</div>
                                <div style="font-size: 24px; font-weight: 700;">$<?php echo number_format($product['base_price'], 2); ?></div>
                                <div style="font-size: 11px; color: var(--text-secondary);">per device/day</div>
                            </div>
                            
                            <div style="display: flex; gap: 8px;">
                                <button class="btn-icon" onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)" title="Edit">✏️</button>
                                <button class="btn-icon" onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')" title="Delete">🗑️</button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <!-- Edit Modal -->
    <div id="editModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 16px; padding: 32px; max-width: 600px; width: 90%;">
            <h2 style="margin-bottom: 24px;">Edit Product</h2>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="product_id" id="edit_product_id">
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Product Name *</label>
                    <input type="text" name="name" id="edit_name" required
                           style="width: 100%; padding: 12px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary);">
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Description</label>
                    <textarea name="description" id="edit_description" rows="3"
                              style="width: 100%; padding: 12px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary); resize: vertical;"></textarea>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Base Price ($) *</label>
                    <input type="number" name="base_price" id="edit_base_price" step="0.01" min="0" required
                           style="width: 100%; padding: 12px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary);">
                </div>
                
                <div style="margin-bottom: 24px;">
                    <label style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
                        <input type="checkbox" name="is_active" id="edit_is_active" style="width: 20px; height: 20px;">
                        <span>Active</span>
                    </label>
                </div>
                
                <div style="display: flex; gap: 12px;">
                    <button type="submit" class="btn-primary">Update Product</button>
                    <button type="button" class="btn-secondary" onclick="closeEditModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="product_id" id="delete_product_id">
    </form>
    
    <script src="../assets/js/script.js"></script>
    <script src="admin-script.js"></script>
    <script>
        function showCreateForm() {
            document.getElementById('createForm').style.display = 'block';
            document.getElementById('createForm').scrollIntoView({ behavior: 'smooth' });
        }
        
        function hideCreateForm() {
            document.getElementById('createForm').style.display = 'none';
        }
        
        function editProduct(product) {
            document.getElementById('edit_product_id').value = product.id;
            document.getElementById('edit_name').value = product.name;
            document.getElementById('edit_description').value = product.description || '';
            document.getElementById('edit_base_price').value = product.base_price;
            document.getElementById('edit_is_active').checked = product.is_active == 1;
            document.getElementById('editModal').style.display = 'flex';
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        function deleteProduct(productId, productName) {
            if (confirm(`Are you sure you want to delete "${productName}"? This will deactivate the product.`)) {
                document.getElementById('delete_product_id').value = productId;
                document.getElementById('deleteForm').submit();
            }
        }
        
        // Close modal on escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeEditModal();
            }
        });
        
        // Close modal on outside click
        document.getElementById('editModal')?.addEventListener('click', (e) => {
            if (e.target.id === 'editModal') {
                closeEditModal();
            }
        });
    </script>
</body>
</html>

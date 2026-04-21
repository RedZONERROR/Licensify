<?php
// Sidebar component for vendor dashboard
$currentPage = basename($_SERVER['PHP_SELF']);

// Get vendor info
$vendorModel = new Vendor();
$vendor = $vendorModel->getByCode($_SESSION['vendor_code']);
?>
<aside class="admin-sidebar">
    <div class="admin-logo">
        <img src="../assets/icons/logo.svg" alt="Licensify">
        <span>VENDOR PORTAL</span>
    </div>
    
    <nav class="admin-nav">
        <a href="index.php" class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">
            <span>📊</span> Dashboard
        </a>
        <a href="resellers.php" class="<?php echo $currentPage === 'resellers.php' ? 'active' : ''; ?>">
            <span>👥</span> Resellers
        </a>
        <a href="licenses.php" class="<?php echo $currentPage === 'licenses.php' ? 'active' : ''; ?>">
            <span>🔑</span> Licenses
        </a>
        <a href="products.php" class="<?php echo $currentPage === 'products.php' ? 'active' : ''; ?>">
            <span>📦</span> Products
        </a>
        <a href="balance.php" class="<?php echo $currentPage === 'balance.php' ? 'active' : ''; ?>">
            <span>💰</span> Balance
        </a>
        <a href="settings.php" class="<?php echo $currentPage === 'settings.php' ? 'active' : ''; ?>">
            <span>⚙️</span> Settings
        </a>
        <a href="logout.php">
            <span>🚪</span> Logout
        </a>
    </nav>
    
    <div class="admin-user">
        <div class="user-avatar">🏢</div>
        <div class="user-info">
            <div class="user-name"><?php echo htmlspecialchars($vendor['company_name'] ?? 'Vendor'); ?></div>
            <div class="user-role">
                <?php if ($vendor['is_unlimited']): ?>
                    <span style="color: #00D4AA;">∞ Unlimited</span>
                <?php else: ?>
                    $<?php echo number_format($vendor['balance'] ?? 0, 2); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</aside>

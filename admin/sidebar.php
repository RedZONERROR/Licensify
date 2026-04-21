<?php
// Sidebar component for admin dashboard
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar">
    <div class="admin-logo">
        <img src="../assets/icons/logo.svg" alt="Licensify">
        <span>LICENSIFY</span>
    </div>
    
    <nav class="admin-nav">
        <a href="index.php" class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">
            <span>📊</span> Dashboard
        </a>
        <a href="vendors.php" class="<?php echo $currentPage === 'vendors.php' ? 'active' : ''; ?>">
            <span>🏢</span> Vendors
        </a>
        <a href="tokens.php" class="<?php echo $currentPage === 'tokens.php' ? 'active' : ''; ?>">
            <span>🎫</span> Tokens
        </a>
        <a href="products.php" class="<?php echo $currentPage === 'products.php' ? 'active' : ''; ?>">
            <span>📦</span> Products
        </a>
        <a href="settings.php" class="<?php echo $currentPage === 'settings.php' ? 'active' : ''; ?>">
            <span>⚙️</span> Settings
        </a>
        <a href="logout.php">
            <span>🚪</span> Logout
        </a>
    </nav>
    
    <div class="admin-user">
        <div class="user-avatar">👤</div>
        <div class="user-info">
            <div class="user-name"><?php echo htmlspecialchars($user['email']); ?></div>
            <div class="user-role">Owner</div>
        </div>
    </div>
</aside>

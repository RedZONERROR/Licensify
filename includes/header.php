<?php
if (!defined('SITE_NAME')) {
    require_once 'config.php';
}
$current_page = getCurrentPage();
$page_config = getPageConfig($current_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_config['title']; ?></title>
    <meta name="description" content="<?php echo $page_config['description']; ?>">
    <link rel="stylesheet" href="styles.css">
    <?php if ($current_page !== 'index'): ?>
    <link rel="stylesheet" href="pages.css">
    <?php endif; ?>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-content">
                <a href="index.php" class="logo">
                    <img src="<?php echo ICONS_PATH; ?>/logo.svg" alt="<?php echo SITE_NAME; ?>" class="logo-icon">
                    <span class="logo-text"><?php echo SITE_NAME; ?></span>
                </a>
                
                <ul class="nav-menu">
                    <li><a href="features.php" class="<?php echo isActive('features'); ?>">Features</a></li>
                    <li><a href="vendors.php" class="<?php echo isActive('vendors'); ?>">Vendors</a></li>
                    <li><a href="pricing.php" class="<?php echo isActive('pricing'); ?>">Pricing</a></li>
                    <li><a href="documentation.php" class="<?php echo isActive('documentation'); ?>">Documentation</a></li>
                </ul>
                
                <div class="nav-right">
                    <div class="search-box">
                        <img src="<?php echo ICONS_PATH; ?>/search.svg" alt="Search" class="search-icon">
                        <?php
                        $search_placeholder = 'Search Licenses...';
                        if ($current_page === 'vendors') {
                            $search_placeholder = 'Search Vendors...';
                        } elseif ($current_page === 'documentation') {
                            $search_placeholder = 'Search Docs...';
                        } elseif ($current_page === 'support') {
                            $search_placeholder = 'Search Help...';
                        }
                        ?>
                        <input type="text" placeholder="<?php echo $search_placeholder; ?>">
                    </div>
                    <a href="support.php" class="nav-link <?php echo isActive('support'); ?>">Support</a>
                    <button class="btn-signin">
                        <img src="<?php echo ICONS_PATH; ?>/google-signin.svg" alt="Sign In">
                        Sign In
                    </button>
                    <button class="btn-register">Register</button>
                </div>
                
                <button class="mobile-menu-btn">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </nav>

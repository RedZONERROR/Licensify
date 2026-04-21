<?php
// Site Configuration
define('SITE_NAME', 'LICENSIFY');
define('SITE_URL', 'http://localhost');
define('ASSETS_PATH', '/assets');
define('ICONS_PATH', ASSETS_PATH . '/icons');

// Page titles and meta descriptions
$page_config = [
    'index' => [
        'title' => 'LICENSIFY - Centralized License Management & Secure Distribution',
        'description' => 'Effortlessly buy, manage, and sell software licenses. Secure, multi-vendor, and developer-friendly.',
        'active_nav' => 'home'
    ],
    'features' => [
        'title' => 'Features - LICENSIFY',
        'description' => 'Powerful features for license management including multi-vendor support, secure distribution, and automated renewals.',
        'active_nav' => 'features'
    ],
    'vendors' => [
        'title' => 'Vendors - LICENSIFY',
        'description' => 'Browse and purchase licenses from authorized vendors and resellers.',
        'active_nav' => 'vendors'
    ],
    'pricing' => [
        'title' => 'Pricing - LICENSIFY',
        'description' => 'Simple, transparent pricing. Choose the plan that fits your needs.',
        'active_nav' => 'pricing'
    ],
    'documentation' => [
        'title' => 'Documentation - LICENSIFY',
        'description' => 'Complete documentation, API reference, and guides for Licensify.',
        'active_nav' => 'documentation'
    ],
    'support' => [
        'title' => 'Support - LICENSIFY',
        'description' => 'Get the support you need with our help center, documentation, and contact options.',
        'active_nav' => 'support'
    ]
];

// Get current page
function getCurrentPage() {
    $page = basename($_SERVER['PHP_SELF'], '.php');
    return $page;
}

// Get page config
function getPageConfig($page) {
    global $page_config;
    return isset($page_config[$page]) ? $page_config[$page] : $page_config['index'];
}

// Check if nav item is active
function isActive($nav_item) {
    $current_page = getCurrentPage();
    $config = getPageConfig($current_page);
    return $config['active_nav'] === $nav_item ? 'active' : '';
}
?>

<?php require_once 'includes/config.php'; ?>
<?php require_once 'includes/header.php'; ?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="breadcrumb">
                <a href="index.php">Home</a>
                <span>/</span>
                <span>Vendors</span>
            </div>
            <h1 class="page-title">Trusted <span class="gradient-text">Software Vendors</span></h1>
            <p class="page-subtitle">Browse and purchase licenses from authorized vendors and resellers</p>
            
            <div class="vendor-search-bar">
                <input type="text" placeholder="Search for vendors, products, or categories...">
                <button class="btn-primary">Search</button>
            </div>
        </div>
    </section>

    <!-- Vendor Categories -->
    <section class="vendor-categories">
        <div class="container">
            <h2 class="section-title">Browse by Category</h2>
            <div class="categories-grid">
                <?php
                $categories = [
                    ['icon' => '💻', 'title' => 'Development Tools', 'desc' => 'IDEs, Code Editors, Version Control', 'count' => 245],
                    ['icon' => '☁️', 'title' => 'Cloud Services', 'desc' => 'Hosting, Storage, Infrastructure', 'count' => 189],
                    ['icon' => '🎨', 'title' => 'Design Software', 'desc' => 'Graphics, UI/UX, Video Editing', 'count' => 156],
                    ['icon' => '🔒', 'title' => 'Security Tools', 'desc' => 'Antivirus, VPN, Encryption', 'count' => 98],
                    ['icon' => '📊', 'title' => 'Analytics', 'desc' => 'Data Analysis, BI, Monitoring', 'count' => 134],
                    ['icon' => '🤖', 'title' => 'AI & ML', 'desc' => 'Machine Learning, AI APIs', 'count' => 87]
                ];
                
                foreach ($categories as $cat): ?>
                <div class="category-card">
                    <div class="category-icon"><?php echo $cat['icon']; ?></div>
                    <h3><?php echo $cat['title']; ?></h3>
                    <p><?php echo $cat['desc']; ?></p>
                    <span class="vendor-count"><?php echo $cat['count']; ?> vendors</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Vendors -->
    <section class="featured-vendors">
        <div class="container">
            <h2 class="section-title">Featured Vendors</h2>
            <div class="vendors-grid">
                <?php
                $vendors = [
                    ['name' => 'CodeX Studio', 'desc' => 'Professional development environment for modern developers', 'rating' => '4.9', 'users' => '12K+', 'products' => 45, 'gradient' => 'linear-gradient(135deg, #4A9EFF, #7B61FF)'],
                    ['name' => 'DevTools Pro', 'desc' => 'Advanced debugging and testing tools for developers', 'rating' => '4.8', 'users' => '8K+', 'products' => 28, 'gradient' => 'linear-gradient(135deg, #00D4AA, #00A8CC)'],
                    ['name' => 'StudioPro', 'desc' => 'Complete suite for creative professionals', 'rating' => '4.7', 'users' => '15K+', 'products' => 62, 'gradient' => 'linear-gradient(135deg, #A855F7, #7C3AED)'],
                    ['name' => 'CloudSec', 'desc' => 'Enterprise security and cloud infrastructure', 'rating' => '4.9', 'users' => '20K+', 'products' => 38, 'gradient' => 'linear-gradient(135deg, #F59E0B, #D97706)'],
                    ['name' => 'AppGo', 'desc' => 'Mobile and web application development platform', 'rating' => '4.6', 'users' => '10K+', 'products' => 52, 'gradient' => 'linear-gradient(135deg, #EC4899, #BE185D)'],
                    ['name' => 'DataFlow', 'desc' => 'Big data analytics and visualization tools', 'rating' => '4.8', 'users' => '18K+', 'products' => 41, 'gradient' => 'linear-gradient(135deg, #10B981, #059669)']
                ];
                
                foreach ($vendors as $vendor): ?>
                <div class="vendor-card">
                    <div class="vendor-header">
                        <div class="vendor-logo" style="background: <?php echo $vendor['gradient']; ?>;">
                            <svg viewBox="0 0 40 40" fill="white">
                                <path d="M20 5L35 12.5V27.5L20 35L5 27.5V12.5L20 5Z"/>
                            </svg>
                        </div>
                        <div class="vendor-badge">Verified</div>
                    </div>
                    <h3><?php echo $vendor['name']; ?></h3>
                    <p><?php echo $vendor['desc']; ?></p>
                    <div class="vendor-stats">
                        <div class="stat">
                            <span class="stat-value"><?php echo $vendor['rating']; ?></span>
                            <span class="stat-label">Rating</span>
                        </div>
                        <div class="stat">
                            <span class="stat-value"><?php echo $vendor['users']; ?></span>
                            <span class="stat-label">Users</span>
                        </div>
                        <div class="stat">
                            <span class="stat-value"><?php echo $vendor['products']; ?></span>
                            <span class="stat-label">Products</span>
                        </div>
                    </div>
                    <button class="btn-vendor">View Products</button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Become a Vendor -->
    <section class="become-vendor">
        <div class="container">
            <div class="become-vendor-content">
                <div class="become-vendor-left">
                    <h2>Become a <span class="gradient-text">Vendor</span></h2>
                    <p>Join our marketplace and reach thousands of potential customers</p>
                    <ul class="benefits-list">
                        <li>
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="#00D4AA" stroke-width="2"/>
                                <path d="M8 12L11 15L16 9" stroke="#00D4AA" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Reach global audience</span>
                        </li>
                        <li>
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="#00D4AA" stroke-width="2"/>
                                <path d="M8 12L11 15L16 9" stroke="#00D4AA" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Automated license distribution</span>
                        </li>
                        <li>
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="#00D4AA" stroke-width="2"/>
                                <path d="M8 12L11 15L16 9" stroke="#00D4AA" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Secure payment processing</span>
                        </li>
                        <li>
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="#00D4AA" stroke-width="2"/>
                                <path d="M8 12L11 15L16 9" stroke="#00D4AA" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Analytics and insights</span>
                        </li>
                    </ul>
                    <button class="btn-primary">Apply as Vendor</button>
                </div>
                <div class="become-vendor-right">
                    <div class="vendor-illustration">
                        <div class="stats-card">
                            <h4>$2.5M+</h4>
                            <p>Monthly Revenue</p>
                        </div>
                        <div class="stats-card">
                            <h4>500+</h4>
                            <p>Active Vendors</p>
                        </div>
                        <div class="stats-card">
                            <h4>50K+</h4>
                            <p>Happy Customers</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php require_once 'includes/footer.php'; ?>

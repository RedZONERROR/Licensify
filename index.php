<?php require_once 'includes/config.php'; ?>
<?php require_once 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-left">
                    <h1 class="hero-title">
                        Centralized <span class="gradient-text">License</span> Management<br>
                        & Secure Distribution.
                    </h1>
                    <p class="hero-subtitle">
                        Effortlessly buy, manage, and sell software licenses.<br>
                        Secure, multi-vendor, and developer-friendly.
                    </p>
                    <div class="hero-buttons">
                        <button class="btn-primary">
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                                <path d="M9 1L11.5 6L17 7L13 11L14 17L9 14L4 17L5 11L1 7L6.5 6L9 1Z" fill="white"/>
                            </svg>
                            Get Started (Sign In with Google)
                        </button>
                        <button class="btn-secondary">Explore Vendors</button>
                    </div>
                </div>
                
                <div class="hero-right">
                    <div class="hero-illustration">
                        <div class="floating-card card-1">
                            <div class="card-icon">
                                <svg viewBox="0 0 40 40" fill="none">
                                    <rect width="40" height="40" rx="8" fill="url(#card1-gradient)"/>
                                    <path d="M12 15L20 10L28 15V25L20 30L12 25V15Z" stroke="white" stroke-width="2"/>
                                    <defs>
                                        <linearGradient id="card1-gradient">
                                            <stop offset="0%" stop-color="#4A9EFF"/>
                                            <stop offset="100%" stop-color="#7B61FF"/>
                                        </linearGradient>
                                    </defs>
                                </svg>
                            </div>
                            <span>CodeX</span>
                        </div>
                        
                        <div class="floating-card card-2">
                            <div class="card-icon">
                                <svg viewBox="0 0 40 40" fill="none">
                                    <rect width="40" height="40" rx="8" fill="url(#card2-gradient)"/>
                                    <circle cx="20" cy="20" r="8" stroke="white" stroke-width="2"/>
                                    <defs>
                                        <linearGradient id="card2-gradient">
                                            <stop offset="0%" stop-color="#00D4AA"/>
                                            <stop offset="100%" stop-color="#00A8CC"/>
                                        </linearGradient>
                                    </defs>
                                </svg>
                            </div>
                            <span>DevTools</span>
                        </div>
                        
                        <div class="floating-card card-3">
                            <div class="card-icon">
                                <svg viewBox="0 0 40 40" fill="none">
                                    <rect width="40" height="40" rx="8" fill="url(#card3-gradient)"/>
                                    <path d="M15 20L18 23L25 16" stroke="white" stroke-width="2" stroke-linecap="round"/>
                                    <defs>
                                        <linearGradient id="card3-gradient">
                                            <stop offset="0%" stop-color="#A855F7"/>
                                            <stop offset="100%" stop-color="#7C3AED"/>
                                        </linearGradient>
                                    </defs>
                                </svg>
                            </div>
                            <span>StudioPro</span>
                        </div>
                        
                        <div class="license-document">
                            <div class="doc-header">
                                <div class="doc-lines"></div>
                            </div>
                            <div class="doc-content">
                                <div class="doc-line"></div>
                                <div class="doc-line"></div>
                                <div class="doc-line short"></div>
                            </div>
                        </div>
                        
                        <div class="key-icon key-1">
                            <svg viewBox="0 0 30 30" fill="none">
                                <circle cx="10" cy="10" r="6" stroke="#00D4AA" stroke-width="2"/>
                                <path d="M14 14L24 24M20 24H24V20" stroke="#00D4AA" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                        
                        <div class="key-icon key-2">
                            <svg viewBox="0 0 30 30" fill="none">
                                <circle cx="10" cy="10" r="6" stroke="#4A9EFF" stroke-width="2"/>
                                <path d="M14 14L24 24M20 24H24V20" stroke="#4A9EFF" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Licensify Section -->
    <section class="why-section">
        <div class="container">
            <h2 class="section-title">Why Licensify?</h2>
            
            <div class="features-grid">
                <div class="feature-card">
                    <h3>Multi-Vendor Support</h3>
                    <p>Manage diverse licenses from one dashboard.</p>
                    <div class="vendor-icons">
                        <div class="vendor-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">A</div>
                        <div class="vendor-icon" style="background: linear-gradient(135deg, #4A9EFF 0%, #2563EB 100%);">
                            <svg viewBox="0 0 24 24" fill="white">
                                <circle cx="12" cy="12" r="10"/>
                            </svg>
                        </div>
                        <div class="vendor-icon" style="background: linear-gradient(135deg, #00D4AA 0%, #00A8CC 100%);">
                            <svg viewBox="0 0 24 24" fill="white">
                                <path d="M12 2L2 7L12 12L22 7L12 2Z"/>
                                <path d="M2 17L12 22L22 17"/>
                            </svg>
                        </div>
                        <div class="vendor-icon" style="background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);">
                            <svg viewBox="0 0 24 24" fill="white">
                                <rect x="6" y="6" width="12" height="12" rx="2"/>
                            </svg>
                        </div>
                    </div>
                    <button class="feature-btn">Search fn</button>
                </div>
                
                <div class="feature-card">
                    <h3>Reseller Network</h3>
                    <p>Connect with authorized sellers, licensers, and solutions.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon crypto-icon">
                        <svg viewBox="0 0 40 40" fill="none">
                            <circle cx="20" cy="20" r="15" fill="url(#crypto-gradient)"/>
                            <path d="M20 10V30M15 15H22.5C23.88 15 25 16.12 25 17.5C25 18.88 23.88 20 22.5 20H15M15 20H23C24.38 20 25.5 21.12 25.5 22.5C25.5 23.88 24.38 25 23 25H15M15 15V25" stroke="white" stroke-width="2"/>
                            <defs>
                                <linearGradient id="crypto-gradient">
                                    <stop offset="0%" stop-color="#00D4AA"/>
                                    <stop offset="100%" stop-color="#00A8CC"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                    <h3>Instant Cryptomus Payments</h3>
                    <p>Pay securely using cryptocurrencies.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon google-icon">
                        <svg viewBox="0 0 40 40" fill="none">
                            <circle cx="20" cy="20" r="15" fill="#4285F4"/>
                            <path d="M20 15C21.66 15 23 16.34 23 18C23 19.66 21.66 21 20 21C18.34 21 17 19.66 17 18C17 16.34 18.34 15 20 15Z" fill="white"/>
                            <path d="M26 26C26 23.5 23 22 20 22C17 22 14 23.5 14 26" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <h3>Easy Google Login & 2FA</h3>
                    <p>Quick, secure access for developer systems.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Banner -->
    <section class="cta-banner">
        <div class="container">
            <div class="cta-content">
                <div class="cta-left">
                    <h3>Start Managing Your Licenses Today.</h3>
                    <p>Quick setup, secure platform.</p>
                </div>
                <div class="cta-center">
                    <div class="cta-icon">
                        <svg viewBox="0 0 50 50" fill="none">
                            <path d="M25 5L45 15V35L25 45L5 35V15L25 5Z" fill="url(#cta-gradient)"/>
                            <defs>
                                <linearGradient id="cta-gradient">
                                    <stop offset="0%" stop-color="#4A9EFF"/>
                                    <stop offset="100%" stop-color="#7B61FF"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                    <div class="cta-products">
                        <div>AppGo · CloudSec · StudioPro</div>
                        <div class="view-products">View Products</div>
                    </div>
                </div>
                <button class="btn-cta">Sign Up Now</button>
            </div>
        </div>
    </section>

<?php require_once 'includes/footer.php'; ?>

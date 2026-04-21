<?php require_once 'includes/config.php'; ?>
<?php require_once 'includes/header.php'; ?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="breadcrumb">
                <a href="index.php">Home</a>
                <span>/</span>
                <span>Pricing</span>
            </div>
            <h1 class="page-title">Simple, <span class="gradient-text">Transparent Pricing</span></h1>
            <p class="page-subtitle">Choose the plan that fits your needs. No hidden fees.</p>
            
            <div class="pricing-toggle">
                <span class="toggle-label">Monthly</span>
                <label class="switch">
                    <input type="checkbox" id="billingToggle">
                    <span class="slider"></span>
                </label>
                <span class="toggle-label">Yearly <span class="save-badge">Save 20%</span></span>
            </div>
        </div>
    </section>

    <!-- Pricing Plans -->
    <section class="pricing-section">
        <div class="container">
            <div class="pricing-grid">
                <?php
                $plans = [
                    [
                        'name' => 'Free',
                        'desc' => 'Perfect for individuals',
                        'price_monthly' => 0,
                        'price_yearly' => 0,
                        'features' => ['Up to 5 licenses', 'Basic dashboard', 'Email support', 'Community access'],
                        'featured' => false
                    ],
                    [
                        'name' => 'Pro',
                        'desc' => 'For growing teams',
                        'price_monthly' => 29,
                        'price_yearly' => 23,
                        'features' => ['Unlimited licenses', 'Advanced analytics', 'Priority support', 'API access', 'Team collaboration', 'Custom integrations'],
                        'featured' => true
                    ],
                    [
                        'name' => 'Enterprise',
                        'desc' => 'For large organizations',
                        'price_monthly' => 'Custom',
                        'price_yearly' => 'Custom',
                        'features' => ['Everything in Pro', 'Dedicated account manager', '24/7 phone support', 'Custom SLA', 'On-premise deployment', 'Advanced security'],
                        'featured' => false
                    ]
                ];
                
                foreach ($plans as $plan): ?>
                <div class="pricing-card <?php echo $plan['featured'] ? 'featured' : ''; ?>">
                    <?php if ($plan['featured']): ?>
                    <div class="popular-badge">Most Popular</div>
                    <?php endif; ?>
                    <div class="plan-header">
                        <h3><?php echo $plan['name']; ?></h3>
                        <p><?php echo $plan['desc']; ?></p>
                    </div>
                    <div class="plan-price">
                        <?php if (is_numeric($plan['price_monthly'])): ?>
                        <span class="price monthly-price">$<?php echo $plan['price_monthly']; ?></span>
                        <span class="price yearly-price" style="display: none;">$<?php echo $plan['price_yearly']; ?></span>
                        <span class="period">/month</span>
                        <?php else: ?>
                        <span class="price"><?php echo $plan['price_monthly']; ?></span>
                        <?php endif; ?>
                    </div>
                    <ul class="plan-features">
                        <?php foreach ($plan['features'] as $feature): ?>
                        <li>
                            <img src="<?php echo ICONS_PATH; ?>/checkmark.svg" alt="Check">
                            <span><?php echo $feature; ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <button class="btn-plan <?php echo $plan['featured'] ? 'primary' : ''; ?>">
                        <?php echo $plan['name'] === 'Free' ? 'Get Started' : ($plan['name'] === 'Enterprise' ? 'Contact Sales' : 'Start Free Trial'); ?>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <h2 class="section-title">Frequently Asked Questions</h2>
            <div class="faq-grid">
                <?php
                $faqs = [
                    ['q' => 'Can I change plans later?', 'a' => 'Yes, you can upgrade or downgrade your plan at any time. Changes will be reflected in your next billing cycle.'],
                    ['q' => 'What payment methods do you accept?', 'a' => 'We accept all major credit cards, PayPal, and cryptocurrency payments through Cryptomus.'],
                    ['q' => 'Is there a free trial?', 'a' => 'Yes, Pro and Enterprise plans come with a 14-day free trial. No credit card required.'],
                    ['q' => 'Do you offer refunds?', 'a' => 'Yes, we offer a 30-day money-back guarantee for all paid plans.'],
                    ['q' => 'Can I cancel anytime?', 'a' => 'Absolutely. You can cancel your subscription at any time with no cancellation fees.'],
                    ['q' => 'Do you offer discounts for nonprofits?', 'a' => 'Yes, we offer special pricing for nonprofits and educational institutions. Contact us for details.']
                ];
                
                foreach ($faqs as $faq): ?>
                <div class="faq-item">
                    <h3><?php echo $faq['q']; ?></h3>
                    <p><?php echo $faq['a']; ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

<?php require_once 'includes/footer.php'; ?>
    <script>
        // Pricing toggle functionality
        const billingToggle = document.getElementById('billingToggle');
        const monthlyPrices = document.querySelectorAll('.monthly-price');
        const yearlyPrices = document.querySelectorAll('.yearly-price');
        
        billingToggle.addEventListener('change', function() {
            if (this.checked) {
                monthlyPrices.forEach(price => price.style.display = 'none');
                yearlyPrices.forEach(price => price.style.display = 'inline');
            } else {
                monthlyPrices.forEach(price => price.style.display = 'inline');
                yearlyPrices.forEach(price => price.style.display = 'none');
            }
        });
    </script>

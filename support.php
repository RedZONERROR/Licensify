<?php require_once 'includes/config.php'; ?>
<?php require_once 'includes/header.php'; ?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="breadcrumb">
                <a href="index.php">Home</a>
                <span>/</span>
                <span>Support</span>
            </div>
            <h1 class="page-title">How Can We <span class="gradient-text">Help You?</span></h1>
            <p class="page-subtitle">Get the support you need, when you need it</p>
        </div>
    </section>

    <!-- Support Options -->
    <section class="support-options">
        <div class="container">
            <div class="support-grid">
                <?php
                $support_options = [
                    ['icon' => '📚', 'title' => 'Documentation', 'desc' => 'Browse our comprehensive guides and API references', 'link' => 'documentation.php', 'link_text' => 'View Docs'],
                    ['icon' => '❓', 'title' => 'Help Center', 'desc' => 'Find answers to frequently asked questions', 'link' => '#faq', 'link_text' => 'Browse FAQs'],
                    ['icon' => '👥', 'title' => 'Community', 'desc' => 'Connect with other users and share experiences', 'link' => '#', 'link_text' => 'Join Community'],
                    ['icon' => '📧', 'title' => 'Email Support', 'desc' => 'Send us an email and we\'ll respond within 24 hours', 'link' => 'mailto:support@licensify.com', 'link_text' => 'Email Us']
                ];
                
                foreach ($support_options as $option): ?>
                <div class="support-card">
                    <div class="support-icon">
                        <div style="font-size: 48px;"><?php echo $option['icon']; ?></div>
                    </div>
                    <h3><?php echo $option['title']; ?></h3>
                    <p><?php echo $option['desc']; ?></p>
                    <a href="<?php echo $option['link']; ?>" class="support-link"><?php echo $option['link_text']; ?> →</a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Contact Form -->
    <section class="contact-section">
        <div class="container">
            <div class="contact-layout">
                <div class="contact-left">
                    <h2>Send Us a Message</h2>
                    <p>Fill out the form and our team will get back to you within 24 hours.</p>
                    
                    <div class="contact-info">
                        <div class="contact-item">
                            <div class="contact-item-icon">📧</div>
                            <div>
                                <h4>Email</h4>
                                <p>support@licensify.com</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-item-icon">💬</div>
                            <div>
                                <h4>Live Chat</h4>
                                <p>Available Mon-Fri, 9am-5pm EST</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-item-icon">📞</div>
                            <div>
                                <h4>Phone</h4>
                                <p>+1 (555) 123-4567</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="contact-right">
                    <form class="contact-form" method="POST" action="">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" placeholder="John Doe" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" placeholder="john@example.com" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <select id="subject" name="subject" required>
                                <option value="">Select a topic</option>
                                <option value="billing">Billing & Payments</option>
                                <option value="technical">Technical Support</option>
                                <option value="account">Account Issues</option>
                                <option value="feature">Feature Request</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="5" placeholder="Describe your issue or question..." required></textarea>
                        </div>
                        
                        <button type="submit" class="btn-primary">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section" id="faq">
        <div class="container">
            <h2 class="section-title">Frequently Asked Questions</h2>
            <div class="faq-accordion">
                <?php
                $faqs = [
                    ['q' => 'How do I reset my password?', 'a' => 'Click on "Forgot Password" on the login page, enter your email address, and follow the instructions sent to your email to reset your password.'],
                    ['q' => 'How do I add a new license?', 'a' => 'Navigate to your dashboard, click the "Add License" button, fill in the license details including product name, key, and expiration date, then save.'],
                    ['q' => 'Can I transfer licenses between accounts?', 'a' => 'Yes, license transfers are supported. Go to the license details page and click "Transfer License". You\'ll need the recipient\'s email address.'],
                    ['q' => 'What payment methods are accepted?', 'a' => 'We accept all major credit cards (Visa, MasterCard, American Express), PayPal, and cryptocurrency payments through Cryptomus.'],
                    ['q' => 'How do I cancel my subscription?', 'a' => 'Go to Settings → Billing → Subscription, and click "Cancel Subscription". Your access will continue until the end of your billing period.'],
                    ['q' => 'Is my data secure?', 'a' => 'Yes, we use 256-bit encryption for all data, are SOC 2 compliant, and follow industry best practices for security. Your data is encrypted both at rest and in transit.']
                ];
                
                foreach ($faqs as $index => $faq): ?>
                <div class="faq-item">
                    <button class="faq-question">
                        <span><?php echo $faq['q']; ?></span>
                        <img src="<?php echo ICONS_PATH; ?>/arrow-down.svg" alt="Toggle">
                    </button>
                    <div class="faq-answer">
                        <p><?php echo $faq['a']; ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

<?php require_once 'includes/footer.php'; ?>
    <script>
        // FAQ Accordion
        document.querySelectorAll('.faq-question').forEach(button => {
            button.addEventListener('click', () => {
                const faqItem = button.parentElement;
                const isActive = faqItem.classList.contains('active');
                
                // Close all items
                document.querySelectorAll('.faq-item').forEach(item => {
                    item.classList.remove('active');
                });
                
                // Open clicked item if it wasn't active
                if (!isActive) {
                    faqItem.classList.add('active');
                }
            });
        });

        // Contact form submission
        document.querySelector('.contact-form').addEventListener('submit', (e) => {
            e.preventDefault();
            alert('Thank you for your message! We will get back to you within 24 hours.');
            e.target.reset();
        });
    </script>

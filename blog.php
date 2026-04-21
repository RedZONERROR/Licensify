<?php require_once 'includes/config.php'; ?>
<?php require_once 'includes/header.php'; ?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="breadcrumb">
                <a href="index.php">Home</a>
                <span>/</span>
                <span>Blog</span>
            </div>
            <h1 class="page-title">Licensify <span class="gradient-text">Blog</span></h1>
            <p class="page-subtitle">Latest news, updates, and insights</p>
        </div>
    </section>

    <!-- Blog Posts -->
    <section class="blog-section">
        <div class="container">
            <div class="blog-grid">
                <?php
                // Sample blog posts (in production, these would come from a database)
                $blog_posts = [
                    [
                        'title' => 'Introducing Licensify 2.0: The Future of License Management',
                        'excerpt' => 'We\'re excited to announce the launch of Licensify 2.0 with powerful new features including AI-powered license optimization, advanced analytics, and more.',
                        'date' => 'April 15, 2026',
                        'category' => 'Product Updates',
                        'image_gradient' => 'linear-gradient(135deg, #4A9EFF, #7B61FF)',
                        'read_time' => '5 min read'
                    ],
                    [
                        'title' => '10 Best Practices for Software License Management',
                        'excerpt' => 'Learn the essential strategies for managing software licenses effectively, reducing costs, and ensuring compliance across your organization.',
                        'date' => 'April 10, 2026',
                        'category' => 'Best Practices',
                        'image_gradient' => 'linear-gradient(135deg, #00D4AA, #00A8CC)',
                        'read_time' => '8 min read'
                    ],
                    [
                        'title' => 'How to Optimize Your Software Spending',
                        'excerpt' => 'Discover proven techniques to reduce software costs while maintaining productivity. Save up to 30% on your software budget.',
                        'date' => 'April 5, 2026',
                        'category' => 'Cost Optimization',
                        'image_gradient' => 'linear-gradient(135deg, #A855F7, #7C3AED)',
                        'read_time' => '6 min read'
                    ],
                    [
                        'title' => 'Understanding Software License Compliance',
                        'excerpt' => 'A comprehensive guide to staying compliant with software licenses, avoiding audits, and managing vendor relationships.',
                        'date' => 'March 28, 2026',
                        'category' => 'Compliance',
                        'image_gradient' => 'linear-gradient(135deg, #F59E0B, #D97706)',
                        'read_time' => '10 min read'
                    ],
                    [
                        'title' => 'The Rise of Cryptocurrency Payments in SaaS',
                        'excerpt' => 'Explore how cryptocurrency is transforming software payments and why more companies are adopting crypto payment options.',
                        'date' => 'March 20, 2026',
                        'category' => 'Industry Trends',
                        'image_gradient' => 'linear-gradient(135deg, #EC4899, #BE185D)',
                        'read_time' => '7 min read'
                    ],
                    [
                        'title' => 'Security Best Practices for License Management',
                        'excerpt' => 'Protect your software licenses from unauthorized access and fraud with these essential security measures.',
                        'date' => 'March 15, 2026',
                        'category' => 'Security',
                        'image_gradient' => 'linear-gradient(135deg, #10B981, #059669)',
                        'read_time' => '9 min read'
                    ]
                ];
                
                foreach ($blog_posts as $post): ?>
                <article class="blog-card">
                    <div class="blog-image" style="background: <?php echo $post['image_gradient']; ?>;">
                        <div class="blog-category"><?php echo $post['category']; ?></div>
                    </div>
                    <div class="blog-content">
                        <div class="blog-meta">
                            <span class="blog-date"><?php echo $post['date']; ?></span>
                            <span class="blog-read-time"><?php echo $post['read_time']; ?></span>
                        </div>
                        <h3><?php echo $post['title']; ?></h3>
                        <p><?php echo $post['excerpt']; ?></p>
                        <a href="#" class="blog-read-more">Read More →</a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <div class="blog-pagination">
                <button class="pagination-btn" disabled>← Previous</button>
                <div class="pagination-numbers">
                    <span class="pagination-number active">1</span>
                    <span class="pagination-number">2</span>
                    <span class="pagination-number">3</span>
                </div>
                <button class="pagination-btn">Next →</button>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter-section">
        <div class="container">
            <div class="newsletter-box">
                <h2>Subscribe to Our Newsletter</h2>
                <p>Get the latest updates, articles, and insights delivered to your inbox</p>
                <form class="newsletter-form">
                    <input type="email" placeholder="Enter your email" required>
                    <button type="submit" class="btn-primary">Subscribe</button>
                </form>
            </div>
        </div>
    </section>

<?php require_once 'includes/footer.php'; ?>

<style>
.blog-section {
    padding: 80px 0;
}

.blog-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 32px;
    margin-bottom: 60px;
}

.blog-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.3s;
}

.blog-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.4);
    border-color: var(--accent-blue);
}

.blog-image {
    height: 200px;
    display: flex;
    align-items: flex-start;
    justify-content: flex-end;
    padding: 16px;
}

.blog-category {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: white;
}

.blog-content {
    padding: 24px;
}

.blog-meta {
    display: flex;
    gap: 16px;
    margin-bottom: 12px;
    font-size: 13px;
    color: var(--text-secondary);
}

.blog-card h3 {
    font-size: 20px;
    margin-bottom: 12px;
    line-height: 1.4;
}

.blog-card p {
    color: var(--text-secondary);
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 16px;
}

.blog-read-more {
    color: var(--accent-blue);
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: color 0.3s;
}

.blog-read-more:hover {
    color: var(--accent-purple);
}

.blog-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
}

.pagination-btn {
    padding: 10px 20px;
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    color: var(--text-primary);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
}

.pagination-btn:hover:not(:disabled) {
    background: var(--accent-blue);
    border-color: var(--accent-blue);
}

.pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.pagination-numbers {
    display: flex;
    gap: 8px;
}

.pagination-number {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
}

.pagination-number:hover,
.pagination-number.active {
    background: var(--accent-blue);
    border-color: var(--accent-blue);
}

.newsletter-section {
    padding: 80px 0;
    background: var(--secondary-bg);
}

.newsletter-box {
    text-align: center;
    max-width: 600px;
    margin: 0 auto;
}

.newsletter-box h2 {
    font-size: 32px;
    margin-bottom: 12px;
}

.newsletter-box p {
    color: var(--text-secondary);
    margin-bottom: 32px;
}

.newsletter-form {
    display: flex;
    gap: 12px;
}

.newsletter-form input {
    flex: 1;
    padding: 14px 20px;
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    color: var(--text-primary);
    font-size: 15px;
}

.newsletter-form input:focus {
    outline: none;
    border-color: var(--accent-blue);
}

@media (max-width: 768px) {
    .blog-grid {
        grid-template-columns: 1fr;
    }
    
    .newsletter-form {
        flex-direction: column;
    }
    
    .newsletter-form button {
        width: 100%;
    }
}
</style>

<script>
document.querySelector('.newsletter-form').addEventListener('submit', function(e) {
    e.preventDefault();
    alert('Thank you for subscribing! You will receive our latest updates.');
    this.reset();
});
</script>

<?php require_once 'includes/config.php'; ?>
<?php require_once 'includes/header.php'; ?>

    <!-- Documentation Layout -->
    <section class="docs-section">
        <div class="container">
            <div class="docs-layout">
                <!-- Sidebar -->
                <aside class="docs-sidebar">
                    <div class="sidebar-section">
                        <h3>Getting Started</h3>
                        <ul>
                            <li><a href="#introduction" class="active">Introduction</a></li>
                            <li><a href="#quick-start">Quick Start</a></li>
                            <li><a href="#installation">Installation</a></li>
                            <li><a href="#authentication">Authentication</a></li>
                        </ul>
                    </div>
                    <div class="sidebar-section">
                        <h3>Core Concepts</h3>
                        <ul>
                            <li><a href="#licenses">Licenses</a></li>
                            <li><a href="#vendors">Vendors</a></li>
                            <li><a href="#users">Users & Teams</a></li>
                            <li><a href="#billing">Billing</a></li>
                        </ul>
                    </div>
                    <div class="sidebar-section">
                        <h3>API Reference</h3>
                        <ul>
                            <li><a href="#rest-api">REST API</a></li>
                            <li><a href="#webhooks">Webhooks</a></li>
                            <li><a href="#sdks">SDKs</a></li>
                            <li><a href="#rate-limits">Rate Limits</a></li>
                        </ul>
                    </div>
                    <div class="sidebar-section">
                        <h3>Guides</h3>
                        <ul>
                            <li><a href="#integration">Integration Guide</a></li>
                            <li><a href="#security">Security Best Practices</a></li>
                            <li><a href="#migration">Migration Guide</a></li>
                        </ul>
                    </div>
                </aside>

                <!-- Main Content -->
                <main class="docs-content">
                    <div class="breadcrumb">
                        <a href="index.php">Home</a>
                        <span>/</span>
                        <a href="documentation.php">Documentation</a>
                        <span>/</span>
                        <span>Getting Started</span>
                    </div>

                    <article id="introduction">
                        <h1>Introduction to Licensify</h1>
                        <p class="lead">Welcome to Licensify! This documentation will help you get started with managing your software licenses efficiently.</p>
                        
                        <div class="info-box">
                            <div class="info-icon">ℹ️</div>
                            <div>
                                <h4>What is Licensify?</h4>
                                <p>Licensify is a centralized platform for buying, selling, and managing software licenses. It provides a secure, multi-vendor dashboard with automated renewals and comprehensive analytics.</p>
                            </div>
                        </div>

                        <h2>Key Features</h2>
                        <ul class="feature-list-docs">
                            <li>Multi-vendor license management</li>
                            <li>Secure license distribution</li>
                            <li>Automated renewal reminders</li>
                            <li>Team collaboration tools</li>
                            <li>Usage analytics and reporting</li>
                            <li>RESTful API integration</li>
                        </ul>
                    </article>

                    <article id="quick-start">
                        <h2>Quick Start</h2>
                        <p>Get up and running with Licensify in just a few minutes.</p>

                        <h3>Step 1: Create an Account</h3>
                        <div class="code-block">
                            <pre><code>1. Visit https://licensify.com/register
2. Sign up with Google or email
3. Verify your email address
4. Complete your profile</code></pre>
                        </div>

                        <h3>Step 2: Add Your First License</h3>
                        <div class="code-block">
                            <pre><code>1. Navigate to Dashboard
2. Click "Add License"
3. Enter license details
4. Save and activate</code></pre>
                        </div>

                        <h3>Step 3: Explore Features</h3>
                        <p>Once your account is set up, explore the dashboard to:</p>
                        <ul class="feature-list-docs">
                            <li>View all your licenses in one place</li>
                            <li>Set up renewal reminders</li>
                            <li>Invite team members</li>
                            <li>Generate usage reports</li>
                        </ul>
                    </article>

                    <article id="installation">
                        <h2>Installation</h2>
                        <p>Install the Licensify SDK for your preferred programming language.</p>

                        <h3>Node.js / JavaScript</h3>
                        <div class="code-block">
                            <div class="code-header">
                                <span>npm</span>
                                <button class="copy-btn">Copy</button>
                            </div>
                            <pre><code>npm install @licensify/sdk</code></pre>
                        </div>

                        <h3>Python</h3>
                        <div class="code-block">
                            <div class="code-header">
                                <span>pip</span>
                                <button class="copy-btn">Copy</button>
                            </div>
                            <pre><code>pip install licensify-sdk</code></pre>
                        </div>

                        <h3>Ruby</h3>
                        <div class="code-block">
                            <div class="code-header">
                                <span>gem</span>
                                <button class="copy-btn">Copy</button>
                            </div>
                            <pre><code>gem install licensify</code></pre>
                        </div>
                    </article>

                    <article id="authentication">
                        <h2>Authentication</h2>
                        <p>Licensify uses API keys for authentication. You can generate API keys from your dashboard.</p>

                        <div class="warning-box">
                            <div class="warning-icon">⚠️</div>
                            <div>
                                <h4>Keep Your API Keys Secret</h4>
                                <p>Never share your API keys publicly or commit them to version control.</p>
                            </div>
                        </div>

                        <h3>Generating an API Key</h3>
                        <ol class="numbered-list">
                            <li>Go to Settings → API Keys</li>
                            <li>Click "Generate New Key"</li>
                            <li>Give your key a descriptive name</li>
                            <li>Copy and store the key securely</li>
                        </ol>

                        <h3>Using API Keys</h3>
                        <div class="code-block">
                            <div class="code-header">
                                <span>JavaScript</span>
                                <button class="copy-btn">Copy</button>
                            </div>
                            <pre><code>const Licensify = require('@licensify/sdk');

const client = new Licensify({
  apiKey: 'your_api_key_here'
});

// Fetch all licenses
const licenses = await client.licenses.list();</code></pre>
                        </div>
                    </article>

                    <article id="rest-api">
                        <h2>REST API</h2>
                        <p>The Licensify REST API allows you to programmatically manage licenses, users, and more.</p>

                        <h3>Base URL</h3>
                        <div class="code-block">
                            <pre><code>https://api.licensify.com/v1</code></pre>
                        </div>

                        <h3>Authentication</h3>
                        <p>Include your API key in the Authorization header:</p>
                        <div class="code-block">
                            <pre><code>Authorization: Bearer YOUR_API_KEY</code></pre>
                        </div>

                        <h3>Example Request</h3>
                        <div class="code-block">
                            <div class="code-header">
                                <span>curl</span>
                                <button class="copy-btn">Copy</button>
                            </div>
                            <pre><code>curl https://api.licensify.com/v1/licenses \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json"</code></pre>
                        </div>

                        <h3>Response</h3>
                        <div class="code-block">
                            <div class="code-header">
                                <span>json</span>
                                <button class="copy-btn">Copy</button>
                            </div>
                            <pre><code>{
  "data": [
    {
      "id": "lic_123456",
      "product": "CodeX Studio Pro",
      "status": "active",
      "expires_at": "2027-12-31"
    }
  ],
  "total": 1
}</code></pre>
                        </div>
                    </article>

                    <div class="docs-navigation">
                        <a href="#quick-start" class="nav-btn">
                            <span>Next</span>
                            <strong>Quick Start →</strong>
                        </a>
                    </div>
                </main>
            </div>
        </div>
    </section>

<?php require_once 'includes/footer.php'; ?>

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
                            <li><a href="#api-endpoints">API Endpoints</a></li>
                            <li><a href="#webhooks">Webhooks</a></li>
                            <li><a href="#sdks">SDKs</a></li>
                            <li><a href="#rate-limits">Rate Limits</a></li>
                        </ul>
                    </div>
                    <div class="sidebar-section">
                        <h3>Security</h3>
                        <ul>
                            <li><a href="#security-overview">Security Overview</a></li>
                            <li><a href="#encryption">Encryption</a></li>
                            <li><a href="#compliance">Compliance</a></li>
                            <li><a href="#best-practices">Best Practices</a></li>
                        </ul>
                    </div>
                    <div class="sidebar-section">
                        <h3>Guides</h3>
                        <ul>
                            <li><a href="#integration">Integration Guide</a></li>
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

                    <article id="api-endpoints">
                        <h2>API Endpoints</h2>
                        <p>Complete reference for all available API endpoints.</p>

                        <h3>Licenses</h3>
                        <div class="endpoint-block">
                            <div class="endpoint-method get">GET</div>
                            <code>/v1/licenses</code>
                            <p>List all licenses</p>
                        </div>
                        <div class="endpoint-block">
                            <div class="endpoint-method get">GET</div>
                            <code>/v1/licenses/:id</code>
                            <p>Retrieve a specific license</p>
                        </div>
                        <div class="endpoint-block">
                            <div class="endpoint-method post">POST</div>
                            <code>/v1/licenses</code>
                            <p>Create a new license</p>
                        </div>
                        <div class="endpoint-block">
                            <div class="endpoint-method put">PUT</div>
                            <code>/v1/licenses/:id</code>
                            <p>Update a license</p>
                        </div>
                        <div class="endpoint-block">
                            <div class="endpoint-method delete">DELETE</div>
                            <code>/v1/licenses/:id</code>
                            <p>Delete a license</p>
                        </div>

                        <h3>Users</h3>
                        <div class="endpoint-block">
                            <div class="endpoint-method get">GET</div>
                            <code>/v1/users</code>
                            <p>List all users in your organization</p>
                        </div>
                        <div class="endpoint-block">
                            <div class="endpoint-method post">POST</div>
                            <code>/v1/users</code>
                            <p>Invite a new user</p>
                        </div>

                        <h3>Vendors</h3>
                        <div class="endpoint-block">
                            <div class="endpoint-method get">GET</div>
                            <code>/v1/vendors</code>
                            <p>List all available vendors</p>
                        </div>
                        <div class="endpoint-block">
                            <div class="endpoint-method get">GET</div>
                            <code>/v1/vendors/:id/products</code>
                            <p>List products from a specific vendor</p>
                        </div>

                        <h3>Example: Create License</h3>
                        <div class="code-block">
                            <div class="code-header">
                                <span>curl</span>
                                <button class="copy-btn">Copy</button>
                            </div>
                            <pre><code>curl -X POST https://api.licensify.com/v1/licenses \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": "prod_123",
    "quantity": 5,
    "duration": "annual"
  }'</code></pre>
                        </div>
                    </article>

                    <article id="webhooks">
                        <h2>Webhooks</h2>
                        <p>Webhooks allow you to receive real-time notifications about events in your Licensify account.</p>

                        <h3>Setting Up Webhooks</h3>
                        <ol class="numbered-list">
                            <li>Go to Settings → Webhooks</li>
                            <li>Click "Add Webhook Endpoint"</li>
                            <li>Enter your endpoint URL</li>
                            <li>Select events to subscribe to</li>
                            <li>Save and test your webhook</li>
                        </ol>

                        <h3>Available Events</h3>
                        <ul class="feature-list-docs">
                            <li><code>license.created</code> - New license created</li>
                            <li><code>license.updated</code> - License updated</li>
                            <li><code>license.expired</code> - License expired</li>
                            <li><code>license.renewed</code> - License renewed</li>
                            <li><code>payment.succeeded</code> - Payment successful</li>
                            <li><code>payment.failed</code> - Payment failed</li>
                        </ul>

                        <h3>Webhook Payload</h3>
                        <div class="code-block">
                            <div class="code-header">
                                <span>json</span>
                                <button class="copy-btn">Copy</button>
                            </div>
                            <pre><code>{
  "id": "evt_123456",
  "type": "license.created",
  "created": 1714521600,
  "data": {
    "object": {
      "id": "lic_123456",
      "product": "CodeX Studio Pro",
      "status": "active"
    }
  }
}</code></pre>
                        </div>
                    </article>

                    <article id="sdks">
                        <h2>SDKs</h2>
                        <p>Official SDKs for popular programming languages.</p>

                        <h3>JavaScript / Node.js</h3>
                        <div class="code-block">
                            <pre><code>npm install @licensify/sdk</code></pre>
                        </div>
                        <div class="code-block">
                            <div class="code-header">
                                <span>JavaScript</span>
                                <button class="copy-btn">Copy</button>
                            </div>
                            <pre><code>const Licensify = require('@licensify/sdk');
const client = new Licensify({ apiKey: 'YOUR_API_KEY' });

// List licenses
const licenses = await client.licenses.list();

// Create license
const license = await client.licenses.create({
  product_id: 'prod_123',
  quantity: 5
});</code></pre>
                        </div>

                        <h3>Python</h3>
                        <div class="code-block">
                            <pre><code>pip install licensify-sdk</code></pre>
                        </div>
                        <div class="code-block">
                            <div class="code-header">
                                <span>Python</span>
                                <button class="copy-btn">Copy</button>
                            </div>
                            <pre><code>import licensify

client = licensify.Client(api_key='YOUR_API_KEY')

# List licenses
licenses = client.licenses.list()

# Create license
license = client.licenses.create(
    product_id='prod_123',
    quantity=5
)</code></pre>
                        </div>

                        <h3>Ruby</h3>
                        <div class="code-block">
                            <pre><code>gem install licensify</code></pre>
                        </div>
                        <div class="code-block">
                            <div class="code-header">
                                <span>Ruby</span>
                                <button class="copy-btn">Copy</button>
                            </div>
                            <pre><code>require 'licensify'

Licensify.api_key = 'YOUR_API_KEY'

# List licenses
licenses = Licensify::License.list

# Create license
license = Licensify::License.create(
  product_id: 'prod_123',
  quantity: 5
)</code></pre>
                        </div>
                    </article>

                    <article id="rate-limits">
                        <h2>Rate Limits</h2>
                        <p>To ensure fair usage and system stability, API requests are rate-limited.</p>

                        <h3>Current Limits</h3>
                        <ul class="feature-list-docs">
                            <li><strong>Free Plan:</strong> 100 requests per hour</li>
                            <li><strong>Starter Plan:</strong> 1,000 requests per hour</li>
                            <li><strong>Professional Plan:</strong> 10,000 requests per hour</li>
                            <li><strong>Enterprise Plan:</strong> Custom limits</li>
                        </ul>

                        <h3>Rate Limit Headers</h3>
                        <div class="code-block">
                            <pre><code>X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1714525200</code></pre>
                        </div>

                        <h3>Handling Rate Limits</h3>
                        <p>When you exceed the rate limit, you'll receive a 429 status code:</p>
                        <div class="code-block">
                            <div class="code-header">
                                <span>json</span>
                                <button class="copy-btn">Copy</button>
                            </div>
                            <pre><code>{
  "error": {
    "type": "rate_limit_exceeded",
    "message": "Too many requests. Please try again later."
  }
}</code></pre>
                        </div>
                    </article>

                    <article id="security-overview">
                        <h2>Security Overview</h2>
                        <p>Security is at the core of everything we do at Licensify. We implement industry-leading security practices to protect your data and licenses.</p>

                        <div class="info-box">
                            <div class="info-icon">🔒</div>
                            <div>
                                <h4>Enterprise-Grade Security</h4>
                                <p>Licensify is built with security-first architecture, ensuring your licenses and data are always protected.</p>
                            </div>
                        </div>

                        <h3>Security Features</h3>
                        <ul class="feature-list-docs">
                            <li>256-bit SSL/TLS encryption for all data in transit</li>
                            <li>AES-256 encryption for data at rest</li>
                            <li>SOC 2 Type II certified infrastructure</li>
                            <li>GDPR and CCPA compliant</li>
                            <li>Regular third-party security audits</li>
                            <li>24/7 security monitoring and threat detection</li>
                            <li>Automated vulnerability scanning</li>
                            <li>Multi-factor authentication (MFA)</li>
                        </ul>
                    </article>

                    <article id="encryption">
                        <h2>Encryption</h2>
                        <p>All data in Licensify is encrypted both in transit and at rest.</p>

                        <h3>Data in Transit</h3>
                        <p>All communication between your browser/application and our servers uses TLS 1.3 with 256-bit encryption:</p>
                        <ul class="feature-list-docs">
                            <li>TLS 1.3 protocol (latest standard)</li>
                            <li>Perfect Forward Secrecy (PFS)</li>
                            <li>HSTS (HTTP Strict Transport Security)</li>
                            <li>Certificate pinning for mobile apps</li>
                        </ul>

                        <h3>Data at Rest</h3>
                        <p>All stored data is encrypted using AES-256 encryption:</p>
                        <ul class="feature-list-docs">
                            <li>Database encryption with AES-256</li>
                            <li>Encrypted backups with separate keys</li>
                            <li>Hardware Security Modules (HSM) for key management</li>
                            <li>Regular key rotation</li>
                        </ul>

                        <h3>License Key Protection</h3>
                        <p>License keys are protected with additional security measures:</p>
                        <ul class="feature-list-docs">
                            <li>One-way hashing for license validation</li>
                            <li>Encrypted storage of license metadata</li>
                            <li>Secure key generation using cryptographic random</li>
                            <li>Rate limiting on validation endpoints</li>
                        </ul>
                    </article>

                    <article id="compliance">
                        <h2>Compliance</h2>
                        <p>Licensify maintains compliance with major security and privacy standards.</p>

                        <h3>SOC 2 Type II</h3>
                        <p>We undergo annual SOC 2 Type II audits to verify our security controls:</p>
                        <ul class="feature-list-docs">
                            <li>Security: Protection against unauthorized access</li>
                            <li>Availability: System uptime and reliability</li>
                            <li>Confidentiality: Protection of sensitive information</li>
                            <li>Processing Integrity: Accurate and complete processing</li>
                            <li>Privacy: Collection and use of personal information</li>
                        </ul>

                        <h3>GDPR Compliance</h3>
                        <p>Full compliance with EU General Data Protection Regulation:</p>
                        <ul class="feature-list-docs">
                            <li>Data portability and export</li>
                            <li>Right to be forgotten (data deletion)</li>
                            <li>Data processing agreements (DPA)</li>
                            <li>EU data residency options</li>
                            <li>Privacy by design principles</li>
                        </ul>

                        <h3>CCPA Compliance</h3>
                        <p>California Consumer Privacy Act compliance:</p>
                        <ul class="feature-list-docs">
                            <li>Transparent data collection practices</li>
                            <li>Opt-out of data selling (we don't sell data)</li>
                            <li>Access to personal information</li>
                            <li>Data deletion requests</li>
                        </ul>

                        <h3>PCI DSS</h3>
                        <p>Payment Card Industry Data Security Standard compliance:</p>
                        <ul class="feature-list-docs">
                            <li>Secure payment processing through certified providers</li>
                            <li>No storage of credit card data on our servers</li>
                            <li>Tokenization for recurring payments</li>
                        </ul>
                    </article>

                    <article id="best-practices">
                        <h2>Security Best Practices</h2>
                        <p>Follow these best practices to maximize security when using Licensify.</p>

                        <h3>API Key Security</h3>
                        <div class="warning-box">
                            <div class="warning-icon">⚠️</div>
                            <div>
                                <h4>Never Expose API Keys</h4>
                                <p>API keys should never be committed to version control or exposed in client-side code.</p>
                            </div>
                        </div>
                        <ul class="feature-list-docs">
                            <li>Store API keys in environment variables</li>
                            <li>Use separate keys for development and production</li>
                            <li>Rotate keys regularly (every 90 days recommended)</li>
                            <li>Revoke compromised keys immediately</li>
                            <li>Use read-only keys when write access isn't needed</li>
                        </ul>

                        <h3>Account Security</h3>
                        <ul class="feature-list-docs">
                            <li>Enable multi-factor authentication (MFA)</li>
                            <li>Use strong, unique passwords</li>
                            <li>Review account activity regularly</li>
                            <li>Limit user permissions to minimum required</li>
                            <li>Remove inactive users promptly</li>
                        </ul>

                        <h3>Integration Security</h3>
                        <ul class="feature-list-docs">
                            <li>Validate webhook signatures</li>
                            <li>Use HTTPS for all webhook endpoints</li>
                            <li>Implement request signing for API calls</li>
                            <li>Set up IP whitelisting when possible</li>
                            <li>Monitor API usage for anomalies</li>
                        </ul>

                        <h3>License Validation</h3>
                        <ul class="feature-list-docs">
                            <li>Validate licenses server-side, not client-side</li>
                            <li>Implement caching to reduce validation requests</li>
                            <li>Use offline validation for sensitive applications</li>
                            <li>Monitor for license abuse patterns</li>
                        </ul>

                        <h3>Incident Response</h3>
                        <p>If you suspect a security issue:</p>
                        <ol class="numbered-list">
                            <li>Immediately revoke potentially compromised API keys</li>
                            <li>Review recent account activity and API logs</li>
                            <li>Contact our security team at security@licensify.com</li>
                            <li>Document the incident for future reference</li>
                        </ol>

                        <h3>Security Reporting</h3>
                        <p>Found a security vulnerability? We appreciate responsible disclosure:</p>
                        <ul class="feature-list-docs">
                            <li>Email: security@licensify.com</li>
                            <li>PGP Key: Available on our website</li>
                            <li>Bug Bounty: Up to $10,000 for critical vulnerabilities</li>
                            <li>Response Time: Within 24 hours for critical issues</li>
                        </ul>
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

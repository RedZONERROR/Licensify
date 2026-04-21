# LICENSIFY - License Management Platform

A complete, responsive website for Licensify - a centralized platform for buying, selling, and managing software licenses.

## 🚀 Features

- **Fully Responsive Design** - Works perfectly on desktop, tablet, and mobile devices
- **Modern UI/UX** - Dark theme with gradient accents and smooth animations
- **Complete Pages** - All major pages implemented (Home, Features, Vendors, Pricing, Documentation, Support, About, Blog)
- **Interactive Elements** - Hover effects, animations, and smooth transitions
- **Custom SVG Icons** - All icons and graphics created from scratch
- **PHP Modular Architecture** - Reusable components with dynamic content
- **Modal System** - Privacy & Terms, Cookies modals

## 📁 Project Structure

```
licensify/
├── index.php               # Homepage
├── features.php            # Features page
├── vendors.php             # Vendors marketplace
├── pricing.php             # Pricing plans
├── documentation.php       # Documentation, API reference & Security
├── support.php             # Support & contact
├── about.php               # About us page
├── blog.php                # Blog with articles
├── includes/
│   ├── config.php          # Site configuration
│   ├── header.php          # Dynamic header
│   ├── footer.php          # Footer with social links
│   └── modals.php          # Modal components
├── assets/
│   ├── css/
│   │   ├── styles.css      # Main stylesheet
│   │   └── pages.css       # Page-specific styles
│   ├── js/
│   │   └── script.js       # JavaScript functionality
│   └── icons/              # SVG icons
└── README.md               # This file
```

## 🎨 Pages Overview

### 1. Homepage (index.php)
- Hero section with animated 3D illustration
- Floating license cards (CodeX, DevTools, StudioPro)
- Animated keys and document graphics
- "Why Licensify?" feature cards
- CTA banner
- Responsive navigation

### 2. Features (features.php)
- Detailed feature cards with icons
- Multi-vendor dashboard
- Secure license distribution
- Automated renewals
- Team collaboration
- Usage analytics
- API integration
- Enterprise-grade security section

### 3. Vendors (vendors.php)
- Browse by category
- Featured vendors showcase
- Vendor cards with ratings and stats
- Search functionality
- "Become a Vendor" section

### 4. Pricing (pricing.php)
- Three pricing tiers (Free, Pro, Enterprise)
- Monthly/Yearly toggle
- Feature comparison
- FAQ section
- Popular plan highlighting

### 5. Documentation (documentation.php)
- Sidebar navigation
- Getting started guide
- Complete API reference with endpoints
- Security best practices
- Code examples with syntax highlighting
- Installation instructions
- Authentication guide
- Webhooks and SDKs
- Rate limits and compliance

### 6. Support (support.php)
- Support options grid
- Contact form
- FAQ accordion
- Contact information
- Live chat availability

### 7. About (about.php)
- Company mission and values
- Statistics and achievements
- Our story
- Enhanced design with animations

### 8. Blog (blog.php)
- Article grid with categories
- Pagination
- Newsletter subscription
- Read time estimates

## 🎯 Key Features

### Design Elements
- **Color Scheme**: Dark theme with blue (#4A9EFF), purple (#7B61FF), and green (#00D4AA) accents
- **Typography**: System fonts for optimal performance
- **Animations**: Floating elements, parallax effects, hover transitions
- **Icons**: Custom SVG icons throughout

### Interactive Features
- Smooth scroll navigation
- Mobile menu toggle with hamburger animation
- Parallax mouse tracking on hero section
- Dynamic sparkle particles
- Button ripple effects
- FAQ accordion
- Pricing toggle (monthly/yearly)
- Form validation
- Modal system for legal pages

### Responsive Breakpoints
- Desktop: 1024px+
- Tablet: 768px - 1023px
- Mobile: < 768px
- Small Mobile: < 480px

## 🚀 Getting Started

### Requirements
- PHP 7.4 or higher
- Web server (Apache/Nginx)
- Modern web browser

### Installation

1. **Clone the repository**
```bash
git clone https://github.com/RedZONERROR/Licensify.git
cd Licensify
```

2. **Configure your web server**
   - Point document root to the project directory
   - Ensure PHP is enabled

3. **Access the website**
   - Open `http://localhost` in your browser
   - Navigate through pages using the menu

## 🌐 Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## 📱 Responsive Design

The website is fully responsive and optimized for:
- Desktop computers (1920px and above)
- Laptops (1024px - 1919px)
- Tablets (768px - 1023px)
- Mobile phones (320px - 767px)

## 🎨 Customization

### Colors
Edit the CSS variables in `assets/css/styles.css`:
```css
:root {
    --primary-bg: #0a0e1a;
    --secondary-bg: #1a1f36;
    --accent-blue: #4A9EFF;
    --accent-purple: #7B61FF;
    --accent-green: #00D4AA;
}
```

### Configuration
Update site settings in `includes/config.php`:
```php
define('SITE_NAME', 'LICENSIFY');
define('SITE_URL', 'http://localhost');
```

## 🔧 Technical Details

### PHP
- Modular architecture with reusable components
- Dynamic page configuration
- Active navigation state management
- Error handling and validation

### HTML
- Semantic HTML5 markup
- Accessible structure
- SEO-friendly

### CSS
- CSS Grid and Flexbox layouts
- CSS Variables for theming
- Smooth transitions and animations
- Mobile-first approach

### JavaScript
- Vanilla JavaScript (no frameworks)
- Smooth scrolling
- Intersection Observer for animations
- Parallax effects
- Form handling
- FAQ accordion
- Modal system

## 🔗 Social Links

- **GitHub**: [RedZONERROR/Licensify](https://github.com/RedZONERROR/licensify.git)
- **YouTube**: [@redzonerror](https://youtube.com/@redzonerror)
- **Telegram**: [@RedZONERROR](https://t.me/RedZONERROR)

## 📄 License

This is a demonstration project. Feel free to use and modify as needed.

## 🤝 Credits

Design inspired by modern SaaS platforms with a focus on developer-friendly interfaces.

## 📞 Support

For questions or issues, refer to the support page or contact through the provided form.

---

**Built with ❤️ using PHP, HTML, CSS, and JavaScript**

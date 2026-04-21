// Admin Dashboard JavaScript

document.addEventListener('DOMContentLoaded', () => {
    console.log('Admin Dashboard initialized');
    
    // Initialize all components
    initSidebar();
    initStats();
    initTables();
    initButtons();
    initMobileMenu();
});

// Sidebar functionality
function initSidebar() {
    const sidebar = document.querySelector('.admin-sidebar');
    const navLinks = document.querySelectorAll('.admin-nav a');
    
    // Highlight active page
    const currentPage = window.location.pathname.split('/').pop() || 'index.php';
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage) {
            link.classList.add('active');
        }
    });
}

// Stats animation
function initStats() {
    const statValues = document.querySelectorAll('.stat-value');
    
    const animateValue = (element, start, end, duration) => {
        const range = end - start;
        const increment = range / (duration / 16);
        let current = start;
        
        const timer = setInterval(() => {
            current += increment;
            if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                current = end;
                clearInterval(timer);
            }
            
            // Format the value
            const originalText = element.getAttribute('data-value') || element.textContent;
            if (originalText.includes('$')) {
                element.textContent = '$' + Math.floor(current).toLocaleString();
            } else if (originalText.includes('%')) {
                element.textContent = current.toFixed(1) + '%';
            } else {
                element.textContent = Math.floor(current).toLocaleString();
            }
        }, 16);
    };
    
    // Animate stats on page load
    statValues.forEach(stat => {
        const value = stat.textContent.replace(/[^0-9.]/g, '');
        const numericValue = parseFloat(value);
        
        if (!isNaN(numericValue) && numericValue > 0) {
            stat.setAttribute('data-value', stat.textContent);
            animateValue(stat, 0, numericValue, 1000);
        }
    });
}

// Table functionality
function initTables() {
    const tableRows = document.querySelectorAll('.admin-table tbody tr');
    
    tableRows.forEach(row => {
        row.addEventListener('click', (e) => {
            // Don't trigger if clicking on buttons
            if (e.target.closest('.btn-icon')) {
                return;
            }
            
            // Add row selection visual feedback
            tableRows.forEach(r => r.style.background = '');
            row.style.background = 'rgba(74, 158, 255, 0.1)';
        });
    });
}

// Button functionality
function initButtons() {
    const iconButtons = document.querySelectorAll('.btn-icon');
    
    iconButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const title = btn.getAttribute('title');
            console.log(`${title} clicked`);
            
            // Add ripple effect
            const ripple = document.createElement('span');
            ripple.style.cssText = `
                position: absolute;
                width: 100%;
                height: 100%;
                background: rgba(255, 255, 255, 0.5);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s ease-out;
            `;
            btn.style.position = 'relative';
            btn.style.overflow = 'hidden';
            btn.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });
    });
}

// Mobile menu
function initMobileMenu() {
    if (window.innerWidth <= 1024) {
        const sidebar = document.querySelector('.admin-sidebar');
        const header = document.querySelector('.admin-header');
        
        // Create mobile menu button
        const menuBtn = document.createElement('button');
        menuBtn.className = 'mobile-menu-btn';
        menuBtn.innerHTML = '☰';
        menuBtn.style.cssText = `
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            width: 44px;
            height: 44px;
            border-radius: 10px;
            font-size: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
        `;
        
        header.insertBefore(menuBtn, header.firstChild);
        
        menuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
        
        // Close sidebar when clicking outside
        document.addEventListener('click', (e) => {
            if (!sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        });
    }
}

// Keyboard shortcuts
document.addEventListener('keydown', (e) => {
    // Escape to close mobile menu
    if (e.key === 'Escape') {
        const sidebar = document.querySelector('.admin-sidebar');
        sidebar?.classList.remove('active');
    }
});

// Add ripple animation CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        to {
            transform: scale(2);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Auto-refresh stats (optional)
function refreshStats() {
    console.log('Refreshing stats...');
    // Add AJAX call here to refresh stats
}

// Refresh every 30 seconds
// setInterval(refreshStats, 30000);

console.log('Admin Dashboard ready');

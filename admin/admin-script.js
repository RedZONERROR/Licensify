// Admin Dashboard JavaScript

// Sidebar Toggle
const sidebar = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebarToggle');

sidebarToggle.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
});

// User Dropdown
const userMenuBtn = document.getElementById('userMenuBtn');
const userDropdown = document.getElementById('userDropdown');

userMenuBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    userDropdown.classList.toggle('active');
});

// Close dropdown when clicking outside
document.addEventListener('click', (e) => {
    if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
        userDropdown.classList.remove('active');
    }
});

// Navigation
const navItems = document.querySelectorAll('.nav-item');

navItems.forEach(item => {
    item.addEventListener('click', (e) => {
        e.preventDefault();
        
        // Remove active class from all items
        navItems.forEach(nav => nav.classList.remove('active'));
        
        // Add active class to clicked item
        item.classList.add('active');
        
        // Get page name
        const page = item.getAttribute('data-page');
        console.log('Navigating to:', page);
        
        // Here you would load different content based on the page
        // For now, we'll just log it
    });
});

// Mobile Sidebar Toggle
const mobileMenuBtn = document.createElement('button');
mobileMenuBtn.className = 'mobile-menu-btn';
mobileMenuBtn.innerHTML = `
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path d="M3 12h18M3 6h18M3 18h18" stroke-width="2" stroke-linecap="round"/>
    </svg>
`;

// Add mobile menu button to topbar on mobile
if (window.innerWidth <= 1024) {
    const topbarLeft = document.querySelector('.topbar-left');
    topbarLeft.insertBefore(mobileMenuBtn, topbarLeft.firstChild);
    
    mobileMenuBtn.addEventListener('click', () => {
        sidebar.classList.toggle('active');
    });
}

// Table Row Selection
const tableCheckboxes = document.querySelectorAll('.data-table input[type="checkbox"]');
const headerCheckbox = document.querySelector('.data-table thead input[type="checkbox"]');

if (headerCheckbox) {
    headerCheckbox.addEventListener('change', (e) => {
        const isChecked = e.target.checked;
        tableCheckboxes.forEach(checkbox => {
            if (checkbox !== headerCheckbox) {
                checkbox.checked = isChecked;
            }
        });
    });
}

// Action Buttons
const editButtons = document.querySelectorAll('.action-btn.edit');
const deleteButtons = document.querySelectorAll('.action-btn.delete');
const viewButtons = document.querySelectorAll('.action-btn.view');

editButtons.forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        console.log('Edit clicked');
        // Add edit functionality here
    });
});

deleteButtons.forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        if (confirm('Are you sure you want to delete this license?')) {
            console.log('Delete confirmed');
            // Add delete functionality here
        }
    });
});

viewButtons.forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        console.log('View clicked');
        // Add view functionality here
    });
});

// Search Functionality
const searchInput = document.querySelector('.search-box input');

if (searchInput) {
    searchInput.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        console.log('Searching for:', searchTerm);
        // Add search functionality here
    });
}

// Notification Button
const notificationBtn = document.querySelector('.notification-btn');

if (notificationBtn) {
    notificationBtn.addEventListener('click', () => {
        console.log('Notifications clicked');
        // Add notification panel functionality here
    });
}

// Add New Vendor Button
const addVendorBtn = document.querySelector('.btn-primary');

if (addVendorBtn) {
    addVendorBtn.addEventListener('click', () => {
        console.log('Add new vendor clicked');
        // Add modal or form functionality here
    });
}

// Smooth Scroll for Activity Feed
const activityList = document.querySelector('.activity-list');

if (activityList) {
    // Auto-scroll to show new activities (simulation)
    setInterval(() => {
        // This would be replaced with real-time updates
        console.log('Checking for new activities...');
    }, 30000); // Check every 30 seconds
}

// Stats Animation on Load
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
        const value = element.textContent;
        if (value.includes('$')) {
            element.textContent = '$ ' + Math.floor(current) + '↑';
        } else if (value.includes('k')) {
            element.textContent = (current / 1000).toFixed(1) + 'k';
        } else if (value.includes('.')) {
            element.textContent = current.toFixed(1) + ' ↑';
        } else {
            element.textContent = Math.floor(current);
        }
    }, 16);
};

// Animate stats on page load
window.addEventListener('load', () => {
    statValues.forEach(stat => {
        const value = stat.textContent;
        let numericValue = 0;
        
        if (value.includes('k')) {
            numericValue = parseFloat(value) * 1000;
        } else if (value.includes('$')) {
            numericValue = parseInt(value.replace(/[^0-9]/g, ''));
        } else {
            numericValue = parseFloat(value);
        }
        
        if (!isNaN(numericValue) && numericValue > 0) {
            animateValue(stat, 0, numericValue, 1000);
        }
    });
});

// Resize Handler
let resizeTimer;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
        if (window.innerWidth <= 1024) {
            sidebar.classList.remove('collapsed');
            sidebar.classList.remove('active');
        }
    }, 250);
});

// Keyboard Shortcuts
document.addEventListener('keydown', (e) => {
    // Ctrl/Cmd + K for search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        searchInput?.focus();
    }
    
    // Escape to close dropdowns
    if (e.key === 'Escape') {
        userDropdown.classList.remove('active');
        sidebar.classList.remove('active');
    }
});

// Table Row Click
const tableRows = document.querySelectorAll('.data-table tbody tr');

tableRows.forEach(row => {
    row.addEventListener('click', (e) => {
        // Don't trigger if clicking on checkbox or action buttons
        if (e.target.type === 'checkbox' || e.target.closest('.action-buttons')) {
            return;
        }
        
        console.log('Row clicked');
        // Add row click functionality here (e.g., show details)
    });
});

// Initialize tooltips (if needed)
const initTooltips = () => {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', (e) => {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = element.getAttribute('data-tooltip');
            document.body.appendChild(tooltip);
            
            const rect = element.getBoundingClientRect();
            tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
            tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        });
        
        element.addEventListener('mouseleave', () => {
            const tooltip = document.querySelector('.tooltip');
            if (tooltip) {
                tooltip.remove();
            }
        });
    });
};

// Initialize on load
initTooltips();

console.log('Admin Dashboard initialized');

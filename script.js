// Mobile Menu Toggle
const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
const navMenu = document.querySelector('.nav-menu');

if (mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', () => {
        navMenu.classList.toggle('active');
        mobileMenuBtn.classList.toggle('active');
    });
}

// Smooth Scrolling
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

// Intersection Observer for Animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observe feature cards
document.querySelectorAll('.feature-card').forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(30px)';
    card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(card);
});

// Parallax Effect for Hero Illustration
let mouseX = 0;
let mouseY = 0;
let currentX = 0;
let currentY = 0;

document.addEventListener('mousemove', (e) => {
    mouseX = (e.clientX / window.innerWidth - 0.5) * 20;
    mouseY = (e.clientY / window.innerHeight - 0.5) * 20;
});

function animateParallax() {
    currentX += (mouseX - currentX) * 0.1;
    currentY += (mouseY - currentY) * 0.1;
    
    const floatingCards = document.querySelectorAll('.floating-card');
    floatingCards.forEach((card, index) => {
        const speed = (index + 1) * 0.5;
        card.style.transform = `translate(${currentX * speed}px, ${currentY * speed}px)`;
    });
    
    const keys = document.querySelectorAll('.key-icon');
    keys.forEach((key, index) => {
        const speed = (index + 1) * 0.3;
        key.style.transform = `translate(${currentX * speed}px, ${currentY * speed}px)`;
    });
    
    const doc = document.querySelector('.license-document');
    if (doc) {
        doc.style.transform = `translate(-50%, -50%) perspective(1000px) rotateY(${-15 + currentX * 0.5}deg)`;
    }
    
    requestAnimationFrame(animateParallax);
}

animateParallax();

// Navbar Scroll Effect
let lastScroll = 0;
const navbar = document.querySelector('.navbar');

window.addEventListener('scroll', () => {
    const currentScroll = window.pageYOffset;
    
    if (currentScroll > 100) {
        navbar.style.background = 'rgba(10, 14, 26, 0.98)';
        navbar.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.3)';
    } else {
        navbar.style.background = 'rgba(10, 14, 26, 0.95)';
        navbar.style.boxShadow = 'none';
    }
    
    lastScroll = currentScroll;
});

// Dynamic Sparkles
function createSparkle() {
    const sparkle = document.createElement('div');
    sparkle.className = 'sparkle';
    sparkle.style.left = Math.random() * window.innerWidth + 'px';
    sparkle.style.top = Math.random() * window.innerHeight + 'px';
    sparkle.style.animationDuration = (Math.random() * 2 + 1) + 's';
    
    document.body.appendChild(sparkle);
    
    setTimeout(() => {
        sparkle.remove();
    }, 3000);
}

// Create sparkles periodically
setInterval(createSparkle, 2000);

// Button Ripple Effect
document.querySelectorAll('button').forEach(button => {
    button.addEventListener('click', function(e) {
        const ripple = document.createElement('span');
        const rect = this.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;
        
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.style.position = 'absolute';
        ripple.style.borderRadius = '50%';
        ripple.style.background = 'rgba(255, 255, 255, 0.5)';
        ripple.style.transform = 'scale(0)';
        ripple.style.animation = 'ripple 0.6s ease-out';
        ripple.style.pointerEvents = 'none';
        
        this.style.position = 'relative';
        this.style.overflow = 'hidden';
        this.appendChild(ripple);
        
        setTimeout(() => ripple.remove(), 600);
    });
});

// Add ripple animation to CSS dynamically
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Feature Card Hover Effect
document.querySelectorAll('.feature-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.zIndex = '10';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.zIndex = '1';
    });
});

// Typing Effect for Hero Title (Optional Enhancement)
function typeWriter(element, text, speed = 50) {
    let i = 0;
    element.innerHTML = '';
    
    function type() {
        if (i < text.length) {
            element.innerHTML += text.charAt(i);
            i++;
            setTimeout(type, speed);
        }
    }
    
    type();
}

// Search Box Focus Effect
const searchInput = document.querySelector('.search-box input');
const searchBox = document.querySelector('.search-box');

if (searchInput && searchBox) {
    searchInput.addEventListener('focus', () => {
        searchBox.style.borderColor = 'var(--accent-blue)';
        searchBox.style.boxShadow = '0 0 0 3px rgba(74, 158, 255, 0.1)';
    });
    
    searchInput.addEventListener('blur', () => {
        searchBox.style.borderColor = 'var(--border-color)';
        searchBox.style.boxShadow = 'none';
    });
}

// CTA Banner Parallax
const ctaBanner = document.querySelector('.cta-content');

if (ctaBanner) {
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const bannerTop = ctaBanner.offsetTop;
        const windowHeight = window.innerHeight;
        
        if (scrolled > bannerTop - windowHeight && scrolled < bannerTop + ctaBanner.offsetHeight) {
            const parallaxValue = (scrolled - (bannerTop - windowHeight)) * 0.1;
            ctaBanner.style.transform = `translateY(${parallaxValue}px)`;
        }
    });
}

// Loading Animation
window.addEventListener('load', () => {
    document.body.style.opacity = '0';
    setTimeout(() => {
        document.body.style.transition = 'opacity 0.5s ease';
        document.body.style.opacity = '1';
    }, 100);
});

// Console Easter Egg
console.log('%c🚀 LICENSIFY', 'font-size: 24px; font-weight: bold; color: #4A9EFF;');
console.log('%cCentralized License Management & Secure Distribution', 'font-size: 14px; color: #8b92a7;');
console.log('%cInterested in joining our team? Contact us!', 'font-size: 12px; color: #00D4AA;');

<!-- Preloader CSS -->
<link rel="stylesheet" href="assets/css/preloader.css">

<!-- Header CSS -->
<link rel="stylesheet" href="assets/css/header.css">

<!-- Preloader HTML -->
<div id="preloader" class="preloader">
    <div class="preloader-content">
        <img src="assets/images/privaShots-logo.png" alt="PrivaShots" class="preloader-logo">
        <div class="preloader-text">PrivaShots</div>
        <div class="preloader-subtitle">Your Private Photo Server</div>
        <div class="preloader-spinner"></div>
    </div>
</div>

<header class="header">
    <div class="logo">
        <a href="dashboard.php?token=<?php echo isset($_GET['token']) ? urlencode($_GET['token']) : ''; ?>">
            <img src="assets/images/privaShots-logo.png" alt="PrivaShots Logo" class="h-10 w-auto transition-opacity duration-700 opacity-0 animate-fadein" style="animation: fadein 1.2s forwards;">
        </a>
    </div>
    
    <!-- Main Navigation Menu -->
    <nav class="main-nav" id="mainNav">
        <a href="dashboard.php" class="nav-link" id="nav-dashboard" onclick="addTokenToLink(this)">
            <i class="fas fa-th-large"></i>
            <span class="nav-text">Gallery</span>
        </a>
        <a href="timeline.php" class="nav-link" id="nav-timeline" onclick="addTokenToLink(this)">
            <i class="fas fa-calendar-alt"></i>
            <span class="nav-text">Timeline</span>
        </a>
        <a href="albums.php" class="nav-link" id="nav-albums" onclick="addTokenToLink(this)">
            <i class="fas fa-folder"></i>
            <span class="nav-text">Albums</span>
        </a>
        <a href="analytics.php" class="nav-link" id="nav-analytics" onclick="addTokenToLink(this)">
            <i class="fas fa-chart-bar"></i>
            <span class="nav-text">Analytics</span>
        </a>
    </nav>
    
    <div class="nav-actions">
        <!-- Mobile Menu Toggle -->
        <button class="btn btn-secondary mobile-menu-toggle" onclick="toggleMobileMenu()">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="user-info">
            <i class="fas fa-user"></i>
            <span id="username">Loading...</span>
        </div>
        
        <button class="btn btn-secondary" onclick="logout()">
            <i class="fas fa-sign-out-alt"></i>
            <span class="btn-text">Logout</span>
        </button>
    </div>
</header>

<!-- Header JavaScript -->
<script>
// Token management
function addTokenToLink(link) {
    const token = localStorage.getItem('auth_token') || new URLSearchParams(window.location.search).get('token');
    if (token) {
        const url = new URL(link.href, window.location.origin);
        url.searchParams.set('token', token);
        link.href = url.toString();
    }
}

// Extract token from URL and store in localStorage
function extractAndStoreToken() {
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');
    if (token) {
        localStorage.setItem('auth_token', token);
    }
}

// Logout function
function logout() {
    localStorage.removeItem('jwt_token');
    window.location.href = 'login.php';
}

// Toggle mobile menu
function toggleMobileMenu() {
    const nav = document.getElementById('mainNav');
    const toggleBtn = document.querySelector('.mobile-menu-toggle');
    
    if (nav.classList.contains('mobile-open')) {
        nav.classList.remove('mobile-open');
        toggleBtn.innerHTML = '<i class="fas fa-bars"></i>';
    } else {
        nav.classList.add('mobile-open');
        toggleBtn.innerHTML = '<i class="fas fa-times"></i>';
    }
}

// Initialize token management
document.addEventListener('DOMContentLoaded', function() {
    extractAndStoreToken();
    
    // Set active nav link
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('active');
        }
    });
});

// Simple and reliable preloader
function initPreloader() {
    const preloader = document.getElementById('preloader');
    if (!preloader) {
        return;
    }
    
    // Hide preloader when page is fully loaded
    function hidePreloader() {
        preloader.classList.add('hidden');
        
        // Remove from DOM after transition
        setTimeout(() => {
            if (preloader.parentNode) {
                preloader.parentNode.removeChild(preloader);
            }
        }, 500);
    }
    
    // Check if page is already loaded
    if (document.readyState === 'complete') {
        hidePreloader();
    } else {
        // Wait for page to load
        window.addEventListener('load', () => {
            hidePreloader();
        });
        
        // Fallback: Hide after 3 seconds
        setTimeout(() => {
            hidePreloader();
        }, 3000);
    }
}

// Initialize preloader
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPreloader);
} else {
    initPreloader();
}
</script>

<style>
@keyframes fadein {
  from { opacity: 0; }
  to { opacity: 1; }
}
.animate-fadein {
  animation: fadein 1.2s forwards;
}
</style> 
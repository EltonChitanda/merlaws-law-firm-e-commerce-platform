<!-- Professional Sidebar Navigation for MerLaws -->
<div id="sidebar-overlay" class="sidebar-overlay"></div>

<!-- Sidebar Navigation -->
<nav id="sidebar" class="sidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <span class="logo-text">MerLaws</span>
        </div>
    </div>

    <!-- User Info Section -->
    <div class="sidebar-user" id="sidebarUser">
        <div class="user-avatar">
            <i class="fas fa-user"></i>
        </div>
        <div class="user-info">
            <div class="user-name" id="userName">Loading...</div>
            <div class="user-role" id="userRole">Client</div>
        </div>
        <div class="user-actions">
            <!-- Settings icon removed as requested -->
        </div>
    </div>

    <!-- Navigation Menu -->
    <div class="sidebar-menu" id="sidebarMenu">
        <!-- This will be populated by JavaScript based on authentication status -->
    </div>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <a href="/app/logout-client.php" class="sidebar-item sidebar-logout">
            <i class="fas fa-sign-out-alt"></i>
            <span class="sidebar-text">Logout</span>
        </a>
    </div>
    
    <!-- Toggle Arrow Button -->
    <button id="sidebar-toggle-arrow" class="sidebar-toggle-arrow" aria-label="Toggle Sidebar">
        <i class="fas fa-chevron-left" id="toggle-arrow-icon"></i>
    </button>
</nav>

<!-- Top Header Bar -->
<header id="top-header" class="top-header">
    <div class="header-content">
        <button id="mobile-menu-toggle" class="mobile-menu-toggle" aria-label="Toggle Menu">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="header-logo">
            <a href="/index.php">
                <img src="/image/logo.webp" alt="MerLaws" class="header-logo-img">
            </a>
        </div>
        
        <div class="header-actions">
            <div class="breadcrumbs" id="breadcrumbs">
                <span class="breadcrumb-item">Dashboard</span>
            </div>
            
            <div class="header-user" id="headerUser">
                <div class="user-menu">
                    <button class="user-menu-toggle" id="userMenuToggle">
                        <div class="user-avatar-small">
                            <i class="fas fa-user"></i>
                        </div>
                        <span class="user-name-small" id="userNameSmall">Loading...</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="user-menu-dropdown" id="userMenuDropdown">
                        <a href="/app/profile.php" class="user-menu-item">
                            <i class="fas fa-user"></i> My Profile
                        </a>
                        <div class="user-menu-divider"></div>
                        <a href="/app/logout-client.php" class="user-menu-item user-menu-logout">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Main Content Wrapper -->
<div id="main-content" class="main-content">

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeSidebarNavigation();
});

async function initializeSidebarNavigation() {
    try {
        const response = await fetch('/app/api/session.php?scope=client', {
            method: 'GET',
            credentials: 'include'
        });
        
        if (response.ok) {
            const sessionData = await response.json();
            
            if (sessionData.logged_in) {
                updateSidebarForAuthenticatedUser(sessionData);
            } else {
                updateSidebarForGuest();
            }
        } else {
            updateSidebarForGuest();
        }
    } catch (error) {
        console.log('Session check failed, showing guest sidebar');
        updateSidebarForGuest();
    }
    
    setupSidebarFunctionality();
    loadSidebarBadges();
    highlightActivePage();
}

function updateSidebarForAuthenticatedUser(userData) {
    const userName = userData.name || 'User';
    const userRole = userData.role || 'client';
    
    // Update user info in sidebar
    document.getElementById('userName').textContent = userName;
    document.getElementById('userRole').textContent = userRole.charAt(0).toUpperCase() + userRole.slice(1);
    document.getElementById('userNameSmall').textContent = userName;
    
    // Update sidebar menu based on role
    const sidebarMenu = document.getElementById('sidebarMenu');
    let menuHTML = '';
    
    if (userRole === 'client') {
        menuHTML = `
            <!-- Dashboard -->
            <a href="/app/dashboard.php" class="sidebar-item">
                <i class="fas fa-tachometer-alt"></i>
                <span class="sidebar-text">Dashboard</span>
            </a>
            
            <!-- Case Management Category -->
            <div class="sidebar-category">
                <div class="sidebar-category-header">
                    <i class="fas fa-briefcase"></i>
                    <span class="sidebar-text">Case Management</span>
                    <i class="fas fa-chevron-right sidebar-category-arrow"></i>
                </div>
                <div class="sidebar-category-menu">
                    <a href="/app/cases/" class="sidebar-item sidebar-item--badge" data-badge="cases">
                        <i class="fas fa-folder-open"></i>
                        <span class="sidebar-text">My Cases</span>
                        <span class="sidebar-badge sidebar-badge--cases" id="badge-cases">0</span>
                    </a>
                    <a href="/app/cases/create.php" class="sidebar-item">
                        <i class="fas fa-plus-circle"></i>
                        <span class="sidebar-text">Create New Case</span>
                    </a>
                    <a href="/app/progress/" class="sidebar-item">
                        <i class="fas fa-chart-line"></i>
                        <span class="sidebar-text">Case Progress</span>
                    </a>
                    <a href="/app/documents/" class="sidebar-item">
                        <i class="fas fa-file-alt"></i>
                        <span class="sidebar-text">My Documents</span>
                    </a>
                </div>
            </div>
            
            <!-- Legal Services Category -->
            <div class="sidebar-category">
                <div class="sidebar-category-header">
                    <i class="fas fa-gavel"></i>
                    <span class="sidebar-text">Legal Services</span>
                    <i class="fas fa-chevron-right sidebar-category-arrow"></i>
                </div>
                <div class="sidebar-category-menu">
                    <a href="/app/services/catalogue.php" class="sidebar-item" data-case-action="requires-selection" data-case-scope="cases-index" data-case-message="Please choose a case to continue. Select a case from &quot;My Cases&quot; and then browse the available legal services tailored to it." data-case-redirect="/app/cases/">
                        <i class="fas fa-search"></i>
                        <span class="sidebar-text">Browse Services</span>
                    </a>
                    <a href="/app/services/cart.php" class="sidebar-item sidebar-item--badge" data-badge="cart">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="sidebar-text">Service Cart</span>
                        <span class="sidebar-badge sidebar-badge--cart" id="badge-cart">0</span>
                    </a>
                </div>
            </div>
            
            <!-- Appointments Category -->
            <div class="sidebar-category">
                <div class="sidebar-category-header">
                    <i class="fas fa-calendar-alt"></i>
                    <span class="sidebar-text">Appointments</span>
                    <i class="fas fa-chevron-right sidebar-category-arrow"></i>
                </div>
                <div class="sidebar-category-menu">
                    <a href="/app/appointments/create.php" class="sidebar-item">
                        <i class="fas fa-calendar-plus"></i>
                        <span class="sidebar-text">Schedule Appointment</span>
                    </a>
                    <a href="/app/appointments/index.php?view=history" class="sidebar-item">
                        <i class="fas fa-history"></i>
                        <span class="sidebar-text">Appointment History</span>
                    </a>
                </div>
            </div>
            
            <!-- Communication Category -->
            <div class="sidebar-category">
                <div class="sidebar-category-header">
                    <i class="fas fa-comments"></i>
                    <span class="sidebar-text">Communication</span>
                    <i class="fas fa-chevron-right sidebar-category-arrow"></i>
                </div>
                <div class="sidebar-category-menu">
                    <a href="/app/messages/" class="sidebar-item sidebar-item--badge" data-badge="messages">
                        <i class="fas fa-envelope"></i>
                        <span class="sidebar-text">Messages</span>
                        <span class="sidebar-badge sidebar-badge--messages" id="badge-messages">0</span>
                    </a>
                    <a href="/app/support/contact.php" class="sidebar-item sidebar-item--badge" data-badge="support_messages">
                        <i class="fas fa-headset"></i>
                        <span class="sidebar-text">Technical Support</span>
                        <span class="sidebar-badge sidebar-badge--support" id="badge-support-messages">0</span>
                    </a>
                </div>
            </div>
            
            <!-- Financial Category -->
            <div class="sidebar-category">
                <div class="sidebar-category-header">
                    <i class="fas fa-credit-card"></i>
                    <span class="sidebar-text">Financial</span>
                    <i class="fas fa-chevron-right sidebar-category-arrow"></i>
                </div>
                <div class="sidebar-category-menu">
                    <a href="/app/cases/" class="sidebar-item" data-case-action="requires-selection" data-case-scope="always" data-case-message="Please select a case from &quot;My Cases&quot; to view invoices and payments associated with it." data-case-redirect="/app/cases/">
                        <i class="fas fa-receipt"></i>
                        <span class="sidebar-text">Invoices & Payments</span>
                    </a>
                    <a href="/app/cases/?history=1" class="sidebar-item" data-case-action="requires-selection" data-case-scope="always" data-case-message="Please choose a case first. Once a case is selected you can review its payment history." data-case-redirect="/app/cases/">
                        <i class="fas fa-chart-bar"></i>
                        <span class="sidebar-text">Payment History</span>
                    </a>
                </div>
            </div>
            
            <!-- Notifications -->
            <a href="/app/notifications/" class="sidebar-item sidebar-item--badge" data-badge="notifications">
                <i class="fas fa-bell"></i>
                <span class="sidebar-text">Notifications</span>
                <span class="sidebar-badge sidebar-badge--notifications" id="badge-notifications">0</span>
            </a>
        `;
    } else if (userRole === 'admin' || userRole === 'super_admin') {
        menuHTML = `
            <a href="/app/admin/dashboard.php" class="sidebar-item">
                <i class="fas fa-tachometer-alt"></i>
                <span class="sidebar-text">Admin Dashboard</span>
            </a>
            <a href="/app/admin/cases.php" class="sidebar-item">
                <i class="fas fa-briefcase"></i>
                <span class="sidebar-text">All Cases</span>
            </a>
            <a href="/app/admin/users.php" class="sidebar-item">
                <i class="fas fa-users"></i>
                <span class="sidebar-text">User Management</span>
            </a>
            <a href="/app/admin/appointments.php" class="sidebar-item">
                <i class="fas fa-calendar"></i>
                <span class="sidebar-text">Appointments</span>
            </a>
            <a href="/app/admin/messages.php" class="sidebar-item">
                <i class="fas fa-envelope"></i>
                <span class="sidebar-text">Messages</span>
            </a>
            <a href="/app/admin/reports.php" class="sidebar-item">
                <i class="fas fa-chart-pie"></i>
                <span class="sidebar-text">Reports</span>
            </a>
        `;
    }
    
    sidebarMenu.innerHTML = menuHTML;
    
    // Initialize sidebar functionality
    if (userRole === 'client') {
        initializeSidebarCategories();
        setupCaseDependentLinks();
    }
}

function updateSidebarForGuest() {
    // Update user info for guest
    document.getElementById('userName').textContent = 'Guest';
    document.getElementById('userRole').textContent = 'Guest';
    document.getElementById('userNameSmall').textContent = 'Guest';
    
    // Update sidebar menu for guest
    const sidebarMenu = document.getElementById('sidebarMenu');
    const menuHTML = `
        <a href="/app/login.php" class="sidebar-item">
            <i class="fas fa-sign-in-alt"></i>
            <span class="sidebar-text">Login</span>
        </a>
        <a href="/app/register.php" class="sidebar-item">
            <i class="fas fa-user-plus"></i>
            <span class="sidebar-text">Register</span>
        </a>
        <a href="/app/forgot-password.php" class="sidebar-item">
            <i class="fas fa-key"></i>
            <span class="sidebar-text">Forgot Password</span>
        </a>
        <a href="/contact-us.php" class="sidebar-item">
            <i class="fas fa-headset"></i>
            <span class="sidebar-text">Contact Support</span>
        </a>
    `;
    
    sidebarMenu.innerHTML = menuHTML;
}

function setupSidebarFunctionality() {
    // Sidebar toggle functionality
    const sidebarToggleArrow = document.getElementById('sidebar-toggle-arrow');
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const mainContent = document.getElementById('main-content');
    
    // Sidebar arrow toggle button
    if (sidebarToggleArrow) {
        sidebarToggleArrow.addEventListener('click', function() {
            toggleSidebar();
        });
    }
    
    // Mobile menu toggle (for mobile and tablet screens)
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            if (window.innerWidth <= 1024) {
                toggleSidebar();
            }
        });
    }
    
    // Overlay click to close sidebar on mobile/tablet screens
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            if (window.innerWidth <= 1024) {
                closeSidebar();
            }
        });
    }
    
    // Close sidebar when clicking on a link (mobile only)
    if (window.innerWidth <= 1024) {
        document.addEventListener('click', function(e) {
            if (e.target.closest('.sidebar-item') && sidebar.classList.contains('sidebar--open')) {
                setTimeout(() => closeSidebar(), 100);
            }
        });
    }
    
    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.innerWidth > 1024) {
                // Desktop: show sidebar, remove overlay
                sidebar.classList.remove('sidebar--mobile', 'sidebar--open');
                sidebarOverlay.classList.remove('active');
                mainContent.classList.remove('sidebar-open');
            } else {
                // Mobile/Tablet: hide sidebar by default if not open
                if (!sidebar.classList.contains('sidebar--open')) {
                    sidebar.classList.add('sidebar--mobile');
                }
            }
        }, 250);
    });
    
    // Initialize sidebar state
    initSidebarState();
    
    // Make user menu work on touch devices
    const userMenuToggle = document.getElementById('userMenuToggle');
    const userMenu = document.querySelector('.user-menu');
    if (userMenuToggle && userMenu) {
        userMenuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            userMenu.classList.toggle('active');
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!userMenu.contains(e.target)) {
                userMenu.classList.remove('active');
            }
        });
    }
}

function updateArrowDirection() {
    const sidebar = document.getElementById('sidebar');
    const arrowIcon = document.getElementById('toggle-arrow-icon');
    if (sidebar && arrowIcon) {
        if (sidebar.classList.contains('sidebar--expanded')) {
            arrowIcon.className = 'fas fa-chevron-left';
        } else {
            arrowIcon.className = 'fas fa-chevron-right';
        }
    }
}

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const mainContent = document.getElementById('main-content');
    
    if (window.innerWidth <= 1024) {
        // Mobile/Tablet behavior (overlay mode)
        const isOpening = !sidebar.classList.contains('sidebar--open');
        sidebar.classList.toggle('sidebar--open');
        sidebar.classList.toggle('sidebar--mobile');
        sidebarOverlay.classList.toggle('active');
        mainContent.classList.toggle('sidebar-open');
        
        // Prevent body scroll when sidebar is open
        if (isOpening) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    } else {
        // Desktop behavior (push mode)
        sidebar.classList.toggle('sidebar--expanded');
        
        // Save state to localStorage
        const isExpanded = sidebar.classList.contains('sidebar--expanded');
        localStorage.setItem('sidebarExpanded', isExpanded);
        
        // Update arrow direction
        updateArrowDirection();
    }
}

function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const mainContent = document.getElementById('main-content');
    
    sidebar.classList.remove('sidebar--open');
    sidebar.classList.add('sidebar--mobile');
    sidebarOverlay.classList.remove('active');
    mainContent.classList.remove('sidebar-open');
    document.body.style.overflow = '';
}

function initSidebarState() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');
    
    // Check localStorage for expanded state
    const isExpanded = localStorage.getItem('sidebarExpanded') === 'true';
    
    // Default to collapsed state (70px) unless user has explicitly expanded it
    if (isExpanded) {
        sidebar.classList.add('sidebar--expanded');
    } else {
        // Ensure collapsed state is applied
        sidebar.classList.remove('sidebar--expanded');
    }
    
    // Update arrow direction on init
    updateArrowDirection();
}

function highlightActivePage() {
    const currentPath = window.location.pathname;
    const sidebarItems = document.querySelectorAll('.sidebar-item');
    
    sidebarItems.forEach(item => {
        const href = item.getAttribute('href');
        if (!href) return;

        // Remove active class from all items
        item.classList.remove('sidebar-item--active');
        
        // Check for a direct match
        if (currentPath === href || (href.endsWith('index.php') && currentPath === href.replace('index.php', ''))) {
            item.classList.add('sidebar-item--active');
        }
    });
}

function initializeSidebarCategories() {
    const categories = document.querySelectorAll('.sidebar-category');
    
    categories.forEach(category => {
        const header = category.querySelector('.sidebar-category-header');
        const menu = category.querySelector('.sidebar-category-menu');
        const arrow = category.querySelector('.sidebar-category-arrow');
        
        if (header && menu) {
            header.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Close other categories
                categories.forEach(otherCategory => {
                    if (otherCategory !== category) {
                        otherCategory.classList.remove('sidebar-category--open');
                    }
                });
                
                // Toggle current category
                category.classList.toggle('sidebar-category--open');
            });
        }
    });
}

function setupCaseDependentLinks() {
    const items = document.querySelectorAll('[data-case-action="requires-selection"]');
    
    if (!items.length) {
        return;
    }
    
    const isCasesIndexPage = (() => {
        const path = window.location.pathname || '';
        const normalized = path.endsWith('/index.php') ? path.slice(0, -10) : path;
        const checks = [
            '/app/cases',
            '/app/cases'
        ];
        return checks.some(base => normalized === base || normalized === `${base}/`);
    })();
    
    items.forEach(item => {
        if (item.dataset.caseProcessed === 'true') {
            return;
        }
        
        const scope = item.dataset.caseScope || 'always';
        const shouldIntercept = scope === 'always' || (scope === 'cases-index' && isCasesIndexPage);
        
        if (!shouldIntercept) {
            return;
        }
        
        item.dataset.caseProcessed = 'true';
        
        item.addEventListener('click', function(event) {
            event.preventDefault();
            
            const message = this.dataset.caseMessage || 'Please select a case to continue.';
            const redirect = this.dataset.caseRedirect || '/app/cases/';
            const redirectLabel = this.dataset.caseRedirectLabel || 'Go to My Cases';
            
            showSidebarNotification(message, {
                type: 'info',
                redirectUrl: redirect,
                redirectLabel: redirectLabel
            });
        });
    });
}

function ensureSidebarNotificationContainer() {
    let container = document.getElementById('sidebar-notification-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'sidebar-notification-container';
        container.className = 'sidebar-notification-container';
        document.body.appendChild(container);
    }
    return container;
}

function showSidebarNotification(message, options = {}) {
    const { type = 'info', redirectUrl = '', redirectLabel = '' } = options;
    const container = ensureSidebarNotificationContainer();
    
    container.querySelectorAll('.sidebar-notification').forEach(n => n.remove());
    
    const notification = document.createElement('div');
    notification.className = `sidebar-notification sidebar-notification--${type}`;
    notification.innerHTML = `
        <div class="sidebar-notification__icon">
            <i class="fas fa-info-circle"></i>
        </div>
        <div class="sidebar-notification__content">
            <div class="sidebar-notification__message">${message}</div>
            ${redirectUrl ? `<button class="sidebar-notification__action" type="button">${redirectLabel || 'View Cases'}</button>` : ''}
        </div>
        <button class="sidebar-notification__close" type="button" aria-label="Close notification">
            <span>&times;</span>
        </button>
    `;
    
    container.appendChild(notification);
    
    requestAnimationFrame(() => {
        notification.classList.add('sidebar-notification--visible');
    });
    
    const closeNotification = () => {
        notification.classList.remove('sidebar-notification--visible');
        setTimeout(() => {
            if (notification.parentElement) {
                notification.parentElement.removeChild(notification);
            }
        }, 200);
    };
    
    const closeButton = notification.querySelector('.sidebar-notification__close');
    if (closeButton) {
        closeButton.addEventListener('click', closeNotification);
    }
    
    const actionButton = notification.querySelector('.sidebar-notification__action');
    if (actionButton && redirectUrl) {
        actionButton.addEventListener('click', () => {
            window.location.href = redirectUrl;
        });
    }
    
    setTimeout(closeNotification, 6000);
}

async function loadSidebarBadges() {
    try {
        const response = await fetch('/app/api/notifications.php?badges=1', {
            method: 'GET',
            credentials: 'include'
        });
        
        if (response.ok) {
            const data = await response.json();
            
            // Update badge counts
            updateSidebarBadge('badge-notifications', data.notifications || 0);
            updateSidebarBadge('badge-messages', data.messages || 0);
            updateSidebarBadge('badge-cases', data.cases || 0);
            updateSidebarBadge('badge-appointments', data.appointments || 0);
            updateSidebarBadge('badge-cart', data.cart || 0);
            updateSidebarBadge('badge-support-messages', data.support_messages || 0);
        }
    } catch (error) {
        console.log('Failed to load sidebar badges:', error);
    }
}

function updateSidebarBadge(badgeId, count) {
    const badge = document.getElementById(badgeId);
    if (badge) {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }
}

// Auto-refresh badges every 30 seconds
setInterval(loadSidebarBadges, 30000);

// Auto-refresh auth status every 5 minutes
setInterval(async function() {
    try {
        const response = await fetch('/app/api/session.php?scope=client', {
            method: 'GET',
            credentials: 'include'
        });
        
        if (response.ok) {
            const sessionData = await response.json();
            // Update user info if needed
            if (sessionData.logged_in) {
                document.getElementById('userName').textContent = sessionData.name || 'User';
                document.getElementById('userNameSmall').textContent = sessionData.name || 'User';
            }
        }
    } catch (error) {
        console.log('Auth refresh failed:', error);
    }
}, 5 * 60 * 1000);

window.MerLawsSidebar = {
    toggleSidebar: toggleSidebar,
    closeSidebar: closeSidebar,
    loadSidebarBadges: loadSidebarBadges,
    highlightActivePage: highlightActivePage,
    updateArrowDirection: updateArrowDirection
};

// Sidebar CSS Styles
const sidebarStyles = `
<style>
/* Sidebar Navigation Styles */
:root {
    --sidebar-width: 280px;
    --sidebar-collapsed-width: 70px;
    --sidebar-bg: #f7fafc;
    --sidebar-border: #e2e8f0;
    --sidebar-text: #2d3748;
    --sidebar-text-muted: #718096;
    --sidebar-hover: #edf2f7;
    --sidebar-active: #AC132A;
    --sidebar-badge-bg: #e53e3e;
    --sidebar-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --sidebar-transition: all 0.3s ease;
}

/* Sidebar Overlay */
.sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: var(--sidebar-transition);
}

.sidebar-overlay.active {
    opacity: 1;
    visibility: visible;
}

/* Main Sidebar */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: var(--sidebar-collapsed-width);
    height: 100vh;
    background: var(--sidebar-bg);
    border-right: 1px solid var(--sidebar-border);
    z-index: 1001;
    display: flex;
    flex-direction: column;
    transition: var(--sidebar-transition);
    box-shadow: var(--sidebar-shadow);
}

.sidebar--expanded {
    width: var(--sidebar-width);
}

.sidebar--mobile {
    transform: translateX(-100%);
}

.sidebar--open {
    transform: translateX(0);
}

/* Sidebar Header */
.sidebar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--sidebar-border);
    background: white;
}

.sidebar-logo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.logo-img {
    width: 32px;
    height: 32px;
    object-fit: contain;
}

.logo-text {
    font-weight: 600;
    font-size: 1.1rem;
    color: var(--sidebar-active);
    transition: var(--sidebar-transition);
}

.sidebar:not(.sidebar--expanded) .logo-text {
    opacity: 0;
    width: 0;
    overflow: hidden;
}

/* Toggle Arrow Button */
.sidebar-toggle-arrow {
    position: absolute;
    top: 50%;
    right: -20px;
    width: 40px;
    height: 40px;
    background: var(--sidebar-active);
    color: white;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    transition: var(--sidebar-transition);
    z-index: 1002;
    transform: translateY(-50%);
}

.sidebar-toggle-arrow:hover {
    transform: translateY(-50%) scale(1.1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.sidebar-toggle-arrow:active {
    transform: translateY(-50%) scale(0.95);
}

#toggle-arrow-icon {
    transition: var(--sidebar-transition);
}

/* Arrow direction - points right when closed (collapsed), left when open (expanded) */
.sidebar:not(.sidebar--expanded) #toggle-arrow-icon {
    transform: rotate(0deg);
}

.sidebar--expanded #toggle-arrow-icon {
    transform: rotate(180deg);
}

.sidebar-toggle {
    background: none;
    border: none;
    color: var(--sidebar-text-muted);
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 6px;
    transition: var(--sidebar-transition);
}

.sidebar-toggle:hover {
    background: var(--sidebar-hover);
    color: var(--sidebar-text);
}

/* User Info Section */
.sidebar-user {
    display: flex;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--sidebar-border);
    background: white;
    gap: 0.75rem;
}

.user-avatar {
    width: 40px;
    height: 40px;
    background: var(--sidebar-active);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.user-info {
    flex: 1;
    min-width: 0;
}

.user-name {
    font-weight: 600;
    color: var(--sidebar-text);
    font-size: 0.95rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-role {
    font-size: 0.8rem;
    color: var(--sidebar-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.user-actions {
    display: flex;
    align-items: center;
}

.user-settings {
    background: none;
    border: none;
    color: var(--sidebar-text-muted);
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 6px;
    transition: var(--sidebar-transition);
}

.user-settings:hover {
    background: var(--sidebar-hover);
    color: var(--sidebar-text);
}

.sidebar:not(.sidebar--expanded) .user-info,
.sidebar:not(.sidebar--expanded) .user-actions {
    display: none;
}

/* Sidebar Menu */
.sidebar-menu {
    flex: 1;
    overflow-y: auto;
    padding: 0.5rem 0;
}

.sidebar-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1.5rem;
    color: var(--sidebar-text);
    text-decoration: none;
    font-weight: 500;
    font-size: 0.9rem;
    transition: var(--sidebar-transition);
    border-left: 3px solid transparent;
    position: relative;
    gap: 0.75rem;
}

.sidebar-item:hover {
    background: var(--sidebar-hover);
    color: var(--sidebar-active);
    border-left-color: var(--sidebar-active);
}

.sidebar-item--active {
    background: rgba(172, 19, 42, 0.1);
    color: var(--sidebar-active);
    border-left-color: var(--sidebar-active);
    font-weight: 600;
}

.sidebar-item i {
    width: 20px;
    font-size: 0.9rem;
    color: var(--sidebar-text-muted);
    transition: var(--sidebar-transition);
    flex-shrink: 0;
}

.sidebar-item:hover i,
.sidebar-item--active i {
    color: var(--sidebar-active);
}

.sidebar-text {
    transition: var(--sidebar-transition);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sidebar:not(.sidebar--expanded) .sidebar-text {
    opacity: 0;
    width: 0;
    overflow: hidden;
}

/* Sidebar Categories */
.sidebar-category {
    border-bottom: 1px solid var(--sidebar-border);
}

.sidebar-category-header {
    display: flex;
    align-items: center;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    font-size: 0.85rem;
    color: var(--sidebar-text);
    background: white;
    cursor: pointer;
    transition: var(--sidebar-transition);
    border-left: 3px solid var(--sidebar-active);
    gap: 0.75rem;
}

.sidebar-category-header:hover {
    background: var(--sidebar-hover);
}

.sidebar-category-header i:first-child {
    color: var(--sidebar-active);
    flex-shrink: 0;
}

.sidebar-category-arrow {
    font-size: 0.7rem;
    color: var(--sidebar-text-muted);
    transition: var(--sidebar-transition);
    margin-left: auto;
}

.sidebar-category--open .sidebar-category-arrow {
    transform: rotate(90deg);
}

.sidebar-category-menu {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
    background: #fafafa;
}

.sidebar-category--open .sidebar-category-menu {
    max-height: 500px;
}

.sidebar-category-menu .sidebar-item {
    padding-left: 2.5rem;
    font-size: 0.85rem;
    border-left: none;
    border-bottom: 1px solid #f1f5f9;
}

.sidebar-category-menu .sidebar-item:hover {
    background: #f1f5f9;
}

/* Sidebar Badges */
.sidebar-badge {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: var(--sidebar-badge-bg);
    color: white;
    font-size: 0.7rem;
    font-weight: 600;
    padding: 0.25rem 0.5rem;
    border-radius: 10px;
    min-width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: var(--sidebar-transition);
}

.sidebar-badge:empty {
    display: none;
}

.sidebar-badge--notifications {
    background: #e53e3e;
}

.sidebar-badge--messages {
    background: #3182ce;
}

.sidebar-badge--cases {
    background: #38a169;
}

.sidebar-badge--appointments {
    background: #ed8936;
}

.sidebar-badge--cart {
    background: #805ad5;
}

.sidebar-badge--support {
    background: #667eea;
}

.sidebar-item--badge {
    padding-right: 3rem;
}

.sidebar-item--badge:hover .sidebar-badge {
    transform: translateY(-50%) scale(1.1);
}

/* Sidebar Footer */
.sidebar-footer {
    border-top: 1px solid var(--sidebar-border);
    background: white;
    padding: 0.5rem 0;
}

.sidebar-logout {
    color: #e53e3e;
}

.sidebar-logout:hover {
    background: #fee2e2;
    color: #991b1b;
}

/* Top Header */
.top-header {
    position: fixed;
    top: 0;
    left: var(--sidebar-collapsed-width);
    right: 0;
    height: 70px;
    background: white;
    border-bottom: 1px solid var(--sidebar-border);
    z-index: 1002;
    transition: var(--sidebar-transition);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.sidebar--expanded + .top-header {
    left: var(--sidebar-width);
}

.header-content {
    display: flex;
    align-items: center;
    height: 100%;
    padding: 0 1.5rem;
    gap: 1rem;
}

@media (max-width: 768px) {
    .header-content {
        padding: 0 1rem;
        gap: 0.75rem;
    }
}

@media (max-width: 480px) {
    .header-content {
        padding: 0 0.75rem;
        gap: 0.5rem;
    }
}

.mobile-menu-toggle {
    display: none;
    background: none;
    border: none;
    color: var(--sidebar-text);
    cursor: pointer;
    padding: 0.75rem;
    border-radius: 8px;
    transition: var(--sidebar-transition);
    min-width: 44px;
    min-height: 44px;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.mobile-menu-toggle:hover {
    background: var(--sidebar-hover);
    color: var(--sidebar-active);
}

.mobile-menu-toggle:active {
    transform: scale(0.95);
}

.header-logo {
    display: flex;
    align-items: center;
}

.header-logo-img {
    height: 40px;
    object-fit: contain;
    max-width: 100%;
}

@media (max-width: 768px) {
    .header-logo-img {
        height: 32px;
    }
}

@media (max-width: 480px) {
    .header-logo-img {
        height: 28px;
    }
}

.breadcrumbs {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--sidebar-text-muted);
    font-size: 0.9rem;
    overflow: hidden;
}

@media (max-width: 768px) {
    .breadcrumbs {
        display: none;
    }
}

.breadcrumb-item {
    color: var(--sidebar-text);
    font-weight: 500;
}

.header-user {
    display: flex;
    align-items: center;
}

.user-menu {
    position: relative;
}

.user-menu-toggle {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: none;
    border: none;
    color: var(--sidebar-text);
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 6px;
    transition: var(--sidebar-transition);
    min-height: 44px;
}

@media (max-width: 768px) {
    .user-menu-toggle {
        gap: 0.25rem;
        padding: 0.5rem;
    }
    
    .user-name-small {
        display: none;
    }
}

.user-menu-toggle:hover {
    background: var(--sidebar-hover);
}

.user-avatar-small {
    width: 32px;
    height: 32px;
    background: var(--sidebar-active);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}

.user-name-small {
    font-weight: 500;
    font-size: 0.9rem;
}

.user-menu-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border: 1px solid var(--sidebar-border);
    border-radius: 8px;
    box-shadow: var(--sidebar-shadow);
    min-width: 200px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: var(--sidebar-transition);
    z-index: 1003;
    margin-top: 0.5rem;
}

.user-menu:hover .user-menu-dropdown,
.user-menu.active .user-menu-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

@media (max-width: 768px) {
    .user-menu-dropdown {
        right: -10px;
        min-width: 180px;
    }
}

.user-menu-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: var(--sidebar-text);
    text-decoration: none;
    font-size: 0.9rem;
    transition: var(--sidebar-transition);
    min-height: 44px;
}

@media (max-width: 768px) {
    .user-menu-item {
        padding: 1rem;
        font-size: 0.95rem;
    }
}

.user-menu-item:hover {
    background: var(--sidebar-hover);
}

.user-menu-logout {
    color: #e53e3e;
}

.user-menu-logout:hover {
    background: #fee2e2;
    color: #991b1b;
}

.user-menu-divider {
    height: 1px;
    background: var(--sidebar-border);
    margin: 0.25rem 0;
}

/* Main Content */
.main-content {
    margin-left: var(--sidebar-collapsed-width);
    margin-top: 70px;
    transition: var(--sidebar-transition);
    min-height: calc(100vh - 70px);
    padding: 1rem;
}

.sidebar--expanded ~ .main-content {
    margin-left: var(--sidebar-width);
}

.sidebar-open .main-content {
    margin-left: 0;
}

/* Smart sidebar - doesn't disturb content on smaller screens */
@media (max-width: 1024px) {
    .sidebar {
        position: fixed;
        z-index: 1001;
    }
    
    .main-content {
        margin-left: 0;
        width: 100%;
    }
    
    .sidebar--expanded ~ .main-content {
        margin-left: 0;
    }
    
    /* Sidebar overlay when expanded on tablets */
    .sidebar--expanded {
        box-shadow: 4px 0 20px rgba(0,0,0,0.15);
    }
}

/* Mobile Responsive */
@media (max-width: 1024px) {
    .mobile-menu-toggle {
        display: flex !important;
    }
    
    .top-header {
        left: 0;
    }
    
    .main-content {
        margin-left: 0;
        margin-top: 70px;
        padding: 0.75rem;
    }
    
    .sidebar {
        width: 280px;
        transform: translateX(-100%);
    }
    
    .sidebar--open {
        transform: translateX(0);
        box-shadow: 4px 0 20px rgba(0,0,0,0.2);
    }
    
    .sidebar-toggle-arrow {
        display: none;
    }
}

@media (max-width: 768px) {
    .top-header {
        height: 60px;
    }
    
    .main-content {
        margin-top: 60px;
        padding: 0.75rem;
    }
    
    .sidebar {
        width: 260px;
    }
    
    .sidebar-header {
        padding: 0.75rem 1rem;
    }
    
    .sidebar-user {
        padding: 0.75rem 1rem;
    }
    
    .sidebar-item {
        padding: 0.875rem 1rem;
        font-size: 0.9rem;
        min-height: 44px;
    }
    
    .sidebar-category-header {
        padding: 0.875rem 1rem;
        min-height: 44px;
    }
}

@media (max-width: 480px) {
    .top-header {
        height: 56px;
    }
    
    .main-content {
        margin-top: 56px;
        padding: 0.5rem;
    }
    
    .sidebar {
        width: 100%;
        max-width: 320px;
    }
    
    .header-content {
        padding: 0 0.5rem;
    }
}

@media (max-width: 768px) {
    .sidebar-badge {
        right: 1rem;
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
        min-width: 20px;
        height: 20px;
    }
}

@media (max-width: 480px) {
    .sidebar-badge {
        right: 0.75rem;
        font-size: 0.65rem;
        min-width: 18px;
        height: 18px;
    }
}

/* Touch-friendly improvements */
.sidebar-item,
.sidebar-category-header,
.user-menu-toggle,
.mobile-menu-toggle {
    -webkit-tap-highlight-color: rgba(172, 19, 42, 0.1);
    touch-action: manipulation;
}

/* Prevent text selection on mobile for better UX */
@media (max-width: 1024px) {
    .sidebar-item,
    .sidebar-category-header {
        -webkit-user-select: none;
        user-select: none;
    }
}

/* Animations */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.sidebar-category-menu {
    animation: slideIn 0.3s ease;
}

/* Focus states for accessibility */
.sidebar-item:focus,
.sidebar-category-header:focus {
    outline: 2px solid var(--sidebar-active);
    outline-offset: 2px;
}

/* Loading state for badges */
.sidebar-badge.loading {
    background: #a0aec0;
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.sidebar-notification-container {
    position: fixed;
    top: 90px;
    right: 24px;
    z-index: 1100;
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-width: min(360px, calc(100% - 48px));
    pointer-events: none;
}

.sidebar-notification {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    background: white;
    color: var(--sidebar-text);
    border-radius: 12px;
    padding: 16px 18px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
    border-left: 4px solid var(--sidebar-active);
    transform: translateY(-8px);
    opacity: 0;
    transition: transform 0.2s ease, opacity 0.2s ease;
    pointer-events: auto;
}

.sidebar-notification--visible {
    transform: translateY(0);
    opacity: 1;
}

.sidebar-notification__icon {
    color: var(--sidebar-active);
    font-size: 1.1rem;
    flex-shrink: 0;
    margin-top: 2px;
}

.sidebar-notification__content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.sidebar-notification__message {
    font-size: 0.9rem;
    line-height: 1.4;
}

.sidebar-notification__action {
    align-self: flex-start;
    background: var(--sidebar-active);
    color: white;
    border: none;
    border-radius: 999px;
    padding: 6px 16px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s ease, transform 0.2s ease;
}

.sidebar-notification__action:hover {
    background: #8a0f22;
    transform: translateY(-1px);
}

.sidebar-notification__close {
    background: none;
    border: none;
    color: var(--sidebar-text-muted);
    font-size: 1.1rem;
    cursor: pointer;
    padding: 2px;
    line-height: 1;
    transition: color 0.2s ease;
}

.sidebar-notification__close:hover {
    color: var(--sidebar-text);
}

@media (max-width: 480px) {
    .sidebar-notification-container {
        left: 12px;
        right: 12px;
        max-width: none;
        top: auto;
        bottom: 24px;
    }
}
</style>
`;

// Add styles to document head
document.head.insertAdjacentHTML('beforeend', sidebarStyles);
</script>


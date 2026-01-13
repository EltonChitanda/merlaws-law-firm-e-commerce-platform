<?php
require __DIR__ . '/../config.php';
require_once __DIR__ . '/../csrf.php';
$current = basename($_SERVER['SCRIPT_NAME'] ?? '');
function nav_active(string $script, string $current): string { return $current === $script ? ' active' : ''; }
?>

<!-- Professional Admin Header for MerLaws -->
<nav id="adminMenu" class="admin-navbar">
    <div class="admin-navbar__container">
        <!-- Brand/Logo Section -->
        <div class="admin-navbar__brand">
            <a href="/app/admin/dashboard.php" class="brand__link">
                <span class="brand__text">MerLaws</span>
                <span class="brand__subtitle">Admin Portal</span>
            </a>
            <button class="mobile-toggle" id="adminMenuToggle" aria-label="Toggle navigation">
                <span class="toggle-bar"></span>
                <span class="toggle-bar"></span>
                <span class="toggle-bar"></span>
            </button>
        </div>

        <!-- Main Navigation -->
        <div class="admin-navbar__menu" id="adminMenuBox">
            <div class="menu-section menu-section--primary">
                <!-- Core Management -->
                <?php if (has_permission('dashboard:view')) { ?>
                <div class="menu-item<?php echo nav_active('dashboard.php', $current); ?>">
                    <a href="/app/admin/dashboard.php" class="menu-link">
                        <span class="menu-icon">📊</span>
                        <span class="menu-text">Dashboard</span>
                    </a>
                </div>
                <?php } ?>

                <?php if (has_permission('case:view')) { ?>
                <div class="menu-item<?php echo nav_active('cases.php', $current); ?>">
                    <a href="/app/admin/cases.php" class="menu-link">
                        <span class="menu-icon">⚖️</span>
                        <span class="menu-text">Cases</span>
                    </a>
                </div>
                <?php } ?>

                <?php if (has_permission('appointment:view')) { ?>
                <div class="menu-item<?php echo nav_active('calendar.php', $current); ?>">
                    <a href="/app/admin/calendar.php" class="menu-link">
                        <span class="menu-icon">📅</span>
                        <span class="menu-text">Calendar</span>
                    </a>
                </div>
                <?php } ?>

                <?php if (has_permission('user:update')) { ?>
                <div class="menu-item<?php echo nav_active('users.php', $current); ?>">
                    <a href="/app/admin/users.php" class="menu-link">
                        <span class="menu-icon">👥</span>
                        <span class="menu-text">Users</span>
                    </a>
                </div>
                <?php } ?>
            </div>

            <!-- Client Services -->
            <div class="menu-section menu-section--secondary">
                <?php if (has_permission('case:view')) { ?>
                <div class="menu-item<?php echo nav_active('service-requests.php', $current); ?>">
                    <a href="/app/admin/service-requests.php" class="menu-link">
                        <span class="menu-icon">📋</span>
                        <span class="menu-text">Service Requests</span>
                    </a>
                </div>
                <?php } ?>

                <?php if (has_permission('message:view')) { ?>
                <div class="menu-item<?php echo nav_active('messages.php', $current); ?>">
                    <a href="/app/admin/messages.php" class="menu-link">
                        <span class="menu-icon">💬</span>
                        <span class="menu-text">Messages</span>
                    </a>
                </div>
                <?php } ?>

                <?php if (has_permission('invoice:create') || has_permission('payment:process')) { ?>
                <div class="menu-item<?php echo nav_active('finance.php', $current); ?>">
                    <a href="/app/admin/finance.php" class="menu-link">
                        <span class="menu-icon">💰</span>
                        <span class="menu-text">Finance</span>
                    </a>
                </div>
                <?php } ?>
            </div>

            <!-- Reports & Analytics -->
            <?php if (has_permission('report:view')) { ?>
            <div class="menu-section menu-section--tertiary">
            <div class="menu-item<?php echo nav_active('analytics.php', $current); ?>">
                <a href="/app/admin/analytics.php" class="menu-link">
                    <span class="menu-icon">📈</span>
                    <span class="menu-text">Analytics and Reports</span>
                </a>
            </div>
            </div>
            <?php } ?>

            <!-- System Management -->
            <div class="menu-section menu-section--system">
                <?php if (has_permission('settings:manage')) { ?>
                <div class="menu-item<?php echo nav_active('rbac.php', $current); ?>">
                    <a href="/app/admin/rbac.php" class="menu-link">
                        <span class="menu-icon">🔐</span>
                        <span class="menu-text">RBAC</span>
                    </a>
                </div>
                <?php } ?>
                <?php if (has_permission('settings:manage')) { ?>
                <div class="menu-item<?php echo nav_active('content.php', $current); ?>">
                    <a href="/app/admin/content.php" class="menu-link">
                        <span class="menu-icon">📝</span>
                        <span class="menu-text">Content</span>
                    </a>
                </div>
                <?php } ?>

                <?php if (has_permission('audit:view')) { ?>
                <div class="menu-item<?php echo nav_active('audit.php', $current); ?>">
                    <a href="/app/admin/audit.php" class="menu-link">
                        <span class="menu-icon">🔍</span>
                        <span class="menu-text">Audit Logs</span>
                    </a>
                </div>
                <?php } ?>

                <?php if (has_permission('compliance:manage')) { ?>
                <?php } ?>

                <?php if (has_permission('system:view') || has_permission('system:logs')) { ?>
                <?php } ?>

                <?php if (has_permission('system:backup') || has_permission('system:restore')) { ?>
                <?php } ?>

                <?php if (has_permission('notification:create')) { ?>
                <div class="menu-item<?php echo nav_active('notifications.php', $current); ?>">
                    <a href="/app/admin/notifications.php" class="menu-link">
                        <span class="menu-icon">🔔</span>
                        <span class="menu-text">Notifications</span>
                    </a>
                </div>
                <?php } ?>

                <?php if (has_permission('settings:manage') || has_permission('integration:manage')) { ?>
                <?php } ?>
            </div>
        </div>

        <!-- User Profile & Actions -->
        <div class="admin-navbar__user">
            <div class="user-info">
                <div class="user-details">
                    <span class="user-name"><?php echo e(get_user_name()); ?></span>
                    <span class="user-role"><?php echo e(ucfirst(str_replace('_', ' ', get_user_role()))); ?></span>
                </div>
                <div class="user-actions">
                    <a href="/" class="user-action" title="View Site">🌐</a>
                    <a href="/app/logout-admin.php" class="btn btn--logout">Logout</a>
                </div>
            </div>
        </div>
    </div>
</nav>

<style>
/* Professional Admin Header Styles */
.admin-navbar {
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    border-bottom: 3px solid #c9a96e;
    box-shadow: 0 2px 10px rgba(0,0,0,0.15);
    position: sticky;
    top: 0;
    z-index: 1000;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.admin-navbar__container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    max-width: 1400px;
    margin: 0 auto;
    min-height: 70px;
    position: relative;
}

@media (max-width: 768px) {
    .admin-navbar__container {
        padding: 0 15px;
        min-height: 60px;
    }
}

@media (max-width: 480px) {
    .admin-navbar__container {
        padding: 0 12px;
        min-height: 56px;
    }
}

/* Brand Section */
.admin-navbar__brand {
    display: flex;
    align-items: center;
    gap: 20px;
}

.brand__link {
    text-decoration: none;
    color: #ffffff;
    display: flex;
    flex-direction: column;
    transition: all 0.3s ease;
}

.brand__link:hover {
    transform: translateY(-1px);
}

.brand__text {
    font-size: 24px;
    font-weight: 700;
    letter-spacing: 1px;
    color: #c9a96e;
    line-height: 1.2;
}

.brand__subtitle {
    font-size: 12px;
    color: #cccccc;
    font-weight: 500;
    margin-top: -2px;
}

@media (max-width: 768px) {
    .brand__text {
        font-size: 20px;
    }
    
    .brand__subtitle {
        font-size: 11px;
    }
}

@media (max-width: 480px) {
    .brand__text {
        font-size: 18px;
    }
    
    .brand__subtitle {
        font-size: 10px;
    }
}

/* Mobile Toggle */
.mobile-toggle {
    display: none;
    flex-direction: column;
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px;
    gap: 5px;
    min-width: 44px;
    min-height: 44px;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: all 0.3s ease;
    -webkit-tap-highlight-color: rgba(201, 169, 110, 0.2);
    touch-action: manipulation;
}

.mobile-toggle:hover {
    background: rgba(201, 169, 110, 0.1);
}

.mobile-toggle:active {
    transform: scale(0.95);
}

.toggle-bar {
    width: 28px;
    height: 3px;
    background: #c9a96e;
    transition: all 0.3s ease;
    border-radius: 2px;
}

/* Main Menu */
.admin-navbar__menu {
    display: flex;
    align-items: center;
    flex: 1;
    justify-content: center;
    gap: 10px;
}

.menu-section {
    display: flex;
    gap: 8px;
    padding: 0 15px;
    border-right: 1px solid rgba(255,255,255,0.1);
}

.menu-section:last-child {
    border-right: none;
}

.menu-section--primary {
    background: rgba(201, 169, 110, 0.05);
    border-radius: 8px;
    padding: 8px 15px;
}

.menu-item {
    position: relative;
}

.menu-link {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    color: #cccccc;
    padding: 8px 12px;
    border-radius: 8px;
    transition: all 0.3s ease;
    font-size: 13px;
    font-weight: 500;
    min-width: 70px;
    min-height: 44px;
    justify-content: center;
    -webkit-tap-highlight-color: rgba(201, 169, 110, 0.2);
    touch-action: manipulation;
}

.menu-link:hover {
    background: rgba(201, 169, 110, 0.15);
    color: #ffffff;
    transform: translateY(-2px);
}

.menu-item.active .menu-link {
    background: #c9a96e;
    color: #1a1a1a;
    font-weight: 600;
}

.menu-icon {
    font-size: 18px;
    margin-bottom: 4px;
    display: block;
}

.menu-text {
    font-size: 11px;
    text-align: center;
    line-height: 1.2;
}

/* User Section */
.admin-navbar__user {
    display: flex;
    align-items: center;
    gap: 20px;
}


.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 44px;
    -webkit-tap-highlight-color: rgba(220, 53, 69, 0.2);
    touch-action: manipulation;
}

.btn--apply {
    background: #c9a96e;
    color: #1a1a1a;
}

.btn--apply:hover {
    background: #d4b578;
    transform: translateY(-1px);
}

.btn--clear {
    background: transparent;
    color: #cccccc;
    border: 1px solid rgba(255,255,255,0.3);
}

.btn--clear:hover {
    background: rgba(255,255,255,0.1);
}

.btn--logout {
    background: #dc3545;
    color: white;
}

.btn--logout:hover {
    background: #c82333;
    transform: translateY(-1px);
}

.user-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.user-details {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    text-align: right;
}

.user-name {
    color: #ffffff;
    font-size: 14px;
    font-weight: 600;
}

.user-role {
    color: #c9a96e;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.user-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.user-action {
    color: #cccccc;
    text-decoration: none;
    padding: 8px;
    border-radius: 6px;
    font-size: 16px;
    transition: all 0.3s ease;
    min-width: 44px;
    min-height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    -webkit-tap-highlight-color: rgba(201, 169, 110, 0.2);
    touch-action: manipulation;
}

.user-action:hover {
    background: rgba(255,255,255,0.1);
    color: #c9a96e;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .menu-section {
        gap: 6px;
        padding: 0 10px;
    }
    
    .menu-link {
        padding: 6px 8px;
        min-width: 60px;
    }
    
    .menu-text {
        font-size: 10px;
    }
}

@media (max-width: 1024px) {
    .mobile-toggle {
        display: flex !important;
    }
    
    .admin-navbar__menu {
        position: fixed;
        top: 70px;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
        flex-direction: column;
        padding: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        transform: translateX(-100%);
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        overflow-y: auto;
        z-index: 999;
        max-height: calc(100vh - 70px);
    }
    
    .admin-navbar__menu.active {
        transform: translateX(0);
        opacity: 1;
        visibility: visible;
    }
    
    .menu-section {
        flex-direction: column;
        border-right: none;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        padding: 15px 0;
        gap: 10px;
        width: 100%;
    }
    
    .menu-link {
        flex-direction: row;
        justify-content: flex-start;
        padding: 14px 16px;
        min-width: auto;
        width: 100%;
        min-height: 50px;
        font-size: 15px;
    }
    
    .menu-icon {
        margin-bottom: 0;
        margin-right: 12px;
        font-size: 22px;
        flex-shrink: 0;
    }
    
    .menu-text {
        font-size: 15px;
        text-align: left;
        font-weight: 500;
    }
    
    .admin-navbar__user {
        flex-direction: column;
        gap: 10px;
        width: 100%;
    }
    
    .user-info {
        flex-direction: column;
        align-items: flex-start;
        width: 100%;
        gap: 12px;
    }
    
    .user-details {
        align-items: flex-start;
        text-align: left;
    }
    
    .user-actions {
        width: 100%;
        justify-content: flex-start;
    }
    
    .btn--logout {
        flex: 1;
        text-align: center;
    }
}

@media (max-width: 768px) {
    .admin-navbar__menu {
        top: 60px;
        max-height: calc(100vh - 60px);
        padding: 15px;
    }
    
    .menu-link {
        padding: 16px;
        min-height: 52px;
    }
}

@media (max-width: 480px) {
    .admin-navbar__menu {
        top: 56px;
        max-height: calc(100vh - 56px);
        padding: 12px;
    }
    
    .menu-link {
        padding: 14px;
        min-height: 48px;
        font-size: 14px;
    }
    
    .menu-icon {
        font-size: 20px;
        margin-right: 10px;
    }
    
    .menu-text {
        font-size: 14px;
    }
}

/* Mobile menu overlay */
@media (max-width: 1024px) {
    .admin-navbar__menu.active::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: -1;
        animation: fadeIn 0.3s ease;
    }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Prevent body scroll when menu is open */
body.menu-open {
    overflow: hidden;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeAdminNavigation();
});

function initializeAdminNavigation() {
    const menuToggle = document.getElementById('adminMenuToggle');
    const menuBox = document.getElementById('adminMenuBox');
    
    if (!menuToggle || !menuBox) return;
    
    // Mobile menu toggle
    menuToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        const isOpening = !menuBox.classList.contains('active');
        menuBox.classList.toggle('active');
        document.body.classList.toggle('menu-open', isOpening);
        
        // Animate toggle bars
        const bars = menuToggle.querySelectorAll('.toggle-bar');
        if (menuBox.classList.contains('active')) {
            bars[0].style.transform = 'rotate(45deg) translate(7px, 7px)';
            bars[1].style.opacity = '0';
            bars[2].style.transform = 'rotate(-45deg) translate(7px, -7px)';
        } else {
            bars[0].style.transform = 'none';
            bars[1].style.opacity = '1';
            bars[2].style.transform = 'none';
        }
    });
    
    // Close menu when clicking outside or on a menu link
    document.addEventListener('click', function(event) {
        const clickedInside = event.target.closest('.admin-navbar');
        const clickedMenuLink = event.target.closest('.menu-link');
        
        if (!clickedInside && menuBox.classList.contains('active')) {
            closeMenu();
        } else if (clickedMenuLink && menuBox.classList.contains('active')) {
            // Close menu after a short delay when clicking a link
            setTimeout(() => {
                closeMenu();
            }, 150);
        }
    });
    
    function closeMenu() {
        menuBox.classList.remove('active');
        document.body.classList.remove('menu-open');
        resetToggleAnimation();
    }
    
    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.innerWidth > 1024) {
                closeMenu();
            }
        }, 250);
    });
    
    function resetToggleAnimation() {
        const bars = menuToggle.querySelectorAll('.toggle-bar');
        if (bars) {
            bars.forEach(bar => {
                bar.style.transform = 'none';
                bar.style.opacity = '1';
            });
        }
    }
    
    // Close menu on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && menuBox.classList.contains('active')) {
            closeMenu();
        }
    });
    
    // Add notification badges (if needed)
    checkForNotifications();
    
    // Auto-refresh notifications every 5 minutes
    setInterval(checkForNotifications, 5 * 60 * 1000);
}

async function checkForNotifications() {
    try {
        const response = await fetch('/app/api/notifications.php', {
            method: 'GET',
            credentials: 'include'
        });
        
        if (response.ok) {
            const data = await response.json();
            
            // Add badges for various menu items
            if (data.new_messages > 0) {
                addNotificationBadge('a[href*="messages.php"]', data.new_messages);
            }
            
            if (data.pending_requests > 0) {
                addNotificationBadge('a[href*="service-requests.php"]', data.pending_requests);
            }
            
            if (data.system_alerts > 0) {
                addNotificationBadge('a[href*="system-health.php"]', data.system_alerts);
            }
        }
    } catch (error) {
        console.log('Notification check failed:', error);
    }
}

function addNotificationBadge(selector, count) {
    const element = document.querySelector(selector);
    if (element && count > 0) {
        // Remove existing badge
        const existingBadge = element.querySelector('.notification-badge');
        if (existingBadge) {
            existingBadge.remove();
        }
        
        // Add new badge
        const badge = document.createElement('span');
        badge.className = 'notification-badge';
        badge.style.cssText = `
            position: absolute;
            top: 5px;
            right: 5px;
            background: #ff4444;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            z-index: 10;
        `;
        badge.textContent = count > 99 ? '99+' : count;
        
        const menuItem = element.closest('.menu-item');
        if (menuItem) {
            menuItem.style.position = 'relative';
            menuItem.appendChild(badge);
        }
    }
}

// Global admin navigation utilities
window.MerLawsAdminNav = {
    addNotificationBadge: addNotificationBadge,
    checkForNotifications: checkForNotifications
};
</script>
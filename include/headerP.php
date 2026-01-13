<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Med Attorneys Client Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Sidebar Navigation Styles */
        :root {
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 0px;
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

        /* Sidebar Overlay - FIXED: Remove pointer-events when not active */
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
            pointer-events: none; /* ADDED: Allow clicks to pass through when not active */
        }

        .sidebar-overlay.active {
            opacity: 1;
            visibility: visible;
            pointer-events: auto; /* ADDED: Only block clicks when active on mobile */
        }

        /* Main Sidebar - FIXED POSITIONING */
        .sidebar {
            position: fixed;
            top: 70px; /* Position below the header */
            left: 0;
            width: var(--sidebar-width);
            height: calc(100vh - 70px); /* Full height minus header */
            background: var(--sidebar-bg);
            border-right: 1px solid var(--sidebar-border);
            z-index: 999; /* Lower than header but higher than content */
            display: flex;
            flex-direction: column;
            transition: var(--sidebar-transition);
            box-shadow: var(--sidebar-shadow);
            transform: translateX(-100%);
            overflow-y: auto;
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
            height: 70px;
        }

        .sidebar-title {
            display: flex;
            align-items: center;
        }

        .menu-text {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--sidebar-active);
        }

        .sidebar-toggle {
            background: none;
            border: none;
            color: var(--sidebar-text-muted);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 6px;
            transition: var(--sidebar-transition);
            font-size: 1.1rem;
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
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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

        .sidebar-badge--messages {
            background: #3182ce;
        }

        .sidebar-badge--cases {
            background: #38a169;
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

        /* Top Header */
        .top-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 70px;
            background: white;
            border-bottom: 1px solid var(--sidebar-border);
            z-index: 1000; /* Higher than sidebar */
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: var(--sidebar-transition);
        }

        .header-content {
            display: flex;
            align-items: center;
            height: 100%;
            padding: 0 1.5rem;
            gap: 1rem;
        }

        .mobile-menu-toggle {
            display: block;
            background: none;
            border: none;
            color: var(--sidebar-text-muted);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 6px;
            transition: var(--sidebar-transition);
        }

        .mobile-menu-toggle:hover {
            background: var(--sidebar-hover);
            color: var(--sidebar-text);
        }

        .header-logo {
            display: flex;
            align-items: center;
        }

        .header-logo a {
            display: flex;
            align-items: center;
            text-decoration: none;
            gap: 0.75rem;
        }

        .header-logo-img {
            height: 40px;
            object-fit: contain;
        }

        .header-logo-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .logo-name {
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--sidebar-active);
        }

        .logo-subtitle {
            font-size: 0.75rem;
            color: var(--sidebar-text-muted);
            font-weight: 500;
        }

        .header-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-left: auto;
        }

        /* Centered Navigation Items */
        .header-center-nav {
            display: flex;
            align-items: center;
            gap: 1rem;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
        }

        .header-action-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--sidebar-text);
            text-decoration: none;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            transition: var(--sidebar-transition);
            font-weight: 500;
            font-size: 0.9rem;
            position: relative;
        }

        .header-action-item:hover {
            background: var(--sidebar-hover);
            color: var(--sidebar-active);
        }

        .header-action-item i {
            font-size: 1rem;
        }

        .header-action-text {
            white-space: nowrap;
        }

        /* Notifications Dropdown */
        .notifications-dropdown {
            position: relative;
        }

        .notifications-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: none;
            border: none;
            color: var(--sidebar-text);
            cursor: pointer;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            transition: var(--sidebar-transition);
            font-weight: 500;
            font-size: 0.9rem;
            position: relative;
        }

        .notifications-toggle:hover {
            background: var(--sidebar-hover);
            color: var(--sidebar-active);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--sidebar-badge-bg);
            color: white;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.2rem 0.4rem;
            border-radius: 10px;
            min-width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: none;
        }

        .notifications-dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: 1px solid var(--sidebar-border);
            border-radius: 8px;
            box-shadow: var(--sidebar-shadow);
            width: 320px;
            max-height: 400px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: var(--sidebar-transition);
            z-index: 1003;
            display: flex;
            flex-direction: column;
        }

        .notifications-dropdown-menu.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .notifications-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--sidebar-border);
        }

        .notifications-header h3 {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
            color: var(--sidebar-text);
        }

        .notifications-close {
            background: none;
            border: none;
            color: var(--sidebar-text-muted);
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
            transition: var(--sidebar-transition);
        }

        .notifications-close:hover {
            background: var(--sidebar-hover);
            color: var(--sidebar-text);
        }

        .notifications-list {
            flex: 1;
            overflow-y: auto;
            padding: 0.5rem 0;
        }

        .notification-empty {
            padding: 1.5rem;
            text-align: center;
            color: var(--sidebar-text-muted);
            font-size: 0.9rem;
        }

        .notifications-footer {
            padding: 0.75rem 1.25rem;
            border-top: 1px solid var(--sidebar-border);
        }

        .view-all-notifications {
            display: block;
            text-align: center;
            color: var(--sidebar-active);
            font-weight: 500;
            font-size: 0.9rem;
            text-decoration: none;
            transition: var(--sidebar-transition);
        }

        .view-all-notifications:hover {
            color: #8a0f22;
        }

        /* Header Notifications Dropdown Styles */
        .notification-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--sidebar-border);
            transition: background-color 0.2s ease;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-item:hover {
            background-color: var(--sidebar-hover);
        }

        .notification-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
            font-size: 0.8rem;
        }

        .notification-icon.icon-info { background-color: #3b82f6; }
        .notification-icon.icon-message { background-color: #8b5cf6; }
        .notification-icon.icon-request { background-color: #f59e0b; }
        .notification-icon.icon-appointment { background-color: #10b981; }

        .notification-content {
            flex: 1;
            min-width: 0;
        }

        .notification-title {
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--sidebar-text);
            margin-bottom: 0.25rem;
            line-height: 1.3;
        }

        .notification-body {
            font-size: 0.8rem;
            color: var(--sidebar-text-muted);
            margin-bottom: 0.25rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .notification-meta {
            font-size: 0.7rem;
            color: var(--sidebar-text-muted);
        }

        .notification-action .btn-mark-read {
            background: none;
            border: none;
            color: var(--sidebar-text-muted);
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
            transition: all 0.2s ease;
            font-size: 0.8rem;
        }

        .notification-action .btn-mark-read:hover {
            background: var(--sidebar-hover);
            color: var(--sidebar-active);
        }

        /* User Menu */
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
        }

        .user-menu:hover .user-menu-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
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

        /* Main Content - CRITICAL CHANGES HERE */
        .main-content {
            margin-top: 70px;
            min-height: calc(100vh - 70px);
            padding: 1.5rem;
            transition: var(--sidebar-transition);
            margin-left: 0;
        }

        /* Desktop: Sidebar open state - SHIFT ALL CONTENT */
        @media (min-width: 769px) {
            body.sidebar-open .main-content {
                margin-left: var(--sidebar-width);
            }
            
            /* FIXED: On desktop, hide overlay completely to allow clicks */
            .sidebar-overlay {
                display: none;
            }
            
            body.sidebar-open .sidebar-overlay {
                display: none;
            }
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .header-logo-text {
                display: none;
            }

            .header-action-text {
                display: none;
            }

            .notifications-dropdown-menu {
                width: 280px;
                right: -50px;
            }

            .header-center-nav {
                position: static;
                transform: none;
                margin-left: auto;
                margin-right: auto;
            }
            
            /* Mobile: Sidebar overlays content */
            body.sidebar-open .main-content {
                transform: translateX(var(--sidebar-width));
            }
            
            /* FIXED: On mobile, overlay should block clicks when active */
            .sidebar-overlay.active {
                pointer-events: auto;
            }
        }

        @media (max-width: 640px) {
            .header-center-nav {
                display: none;
            }
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
</head>
<body>
    <!-- Professional Sidebar Navigation for Med Attorneys -->
    <div id="sidebar-overlay" class="sidebar-overlay"></div>

    <!-- Top Header Bar -->
    <header id="top-header" class="top-header">
        <div class="header-content">
            <button id="mobile-menu-toggle" class="mobile-menu-toggle" aria-label="Toggle Menu">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="header-logo">
                <a href="/index.php">
                    <img src="/image/logo.jpg" alt="Med Attorneys" class="header-logo-img">
                    <div class="header-logo-text">
                        <div class="logo-name">MER Law</div>
                        <div class="logo-subtitle">Client Portal</div>
                    </div>
                </a>
            </div>
            
            <!-- Centered Navigation Items -->
            <div class="header-center-nav">
                <!-- Dashboard Link -->
                <a href="/app/dashboard.php" class="header-action-item">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="header-action-text">Dashboard</span>
                </a>
                
                <!-- Notifications -->
                <div class="header-action-item notifications-dropdown">
                    <button class="notifications-toggle" id="notificationsToggle">
                        <i class="fas fa-bell"></i>
                        <span class="header-action-text">Notifications</span>
                        <span class="notification-badge" id="header-notification-badge">0</span>
                    </button>
                    <div class="notifications-dropdown-menu" id="notificationsDropdown">
                        <div class="notifications-header">
                            <h3>Notifications</h3>
                            <button class="notifications-close" id="notificationsClose">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="notifications-list" id="notificationsList">
                            <div class="notification-empty">No new notifications</div>
                        </div>
                        <div class="notifications-footer">
                            <a href="/app/notifications/" class="view-all-notifications">View All Notifications</a>
                        </div>
                    </div>
                </div>
                
                <!-- My Profile Link -->
                <a href="/app/profile.php" class="header-action-item">
                    <i class="fas fa-user"></i>
                    <span class="header-action-text">My Profile</span>
                </a>
            </div>
                
            <div class="header-controls">
                <!-- Logout Link -->
                <a href="/app/logout-client.php" class="header-action-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="header-action-text">Logout</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Sidebar Navigation -->
    <nav id="sidebar" class="sidebar">
        <!-- Sidebar Header -->
        <div class="sidebar-header">
            <div class="sidebar-title">
                <span class="menu-text">Menu</span>
            </div>
            <button id="sidebar-toggle" class="sidebar-toggle" aria-label="Toggle Sidebar">
                <i class="fas fa-times"></i>
            </button>
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
        </div>

        <!-- Navigation Menu -->
        <div class="sidebar-menu" id="sidebarMenu">
            <!-- This will be populated by JavaScript based on authentication status -->
        </div>
    </nav>

    <!-- Main Content Area - This will contain ALL page content -->
    <main id="main-content" class="main-content">
        <!-- ALL existing page content will be loaded here automatically -->
        <!-- This is where your dashboard, cases, profile, etc. content will appear -->
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeSidebarNavigation();
            moveExistingContent();
        });

        // Function to move existing page content into the main-content area
        function moveExistingContent() {
            const mainContent = document.getElementById('main-content');
            
            // Find all elements that are not part of our navigation system
            const bodyChildren = Array.from(document.body.children);
            const navigationElements = [
                'sidebar-overlay',
                'sidebar', 
                'top-header',
                'main-content'
            ];
            
            // Move non-navigation elements into main-content
            bodyChildren.forEach(element => {
                if (!navigationElements.includes(element.id) && 
                    element.tagName !== 'SCRIPT' && 
                    element.tagName !== 'STYLE' &&
                    element.id !== 'sidebar-notification-container') {
                    mainContent.appendChild(element);
                }
            });
        }

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
            setupHeaderNotifications();
        }

        function updateSidebarForAuthenticatedUser(userData) {
            const userName = userData.name || 'User';
            const userRole = userData.role || 'client';
            
            // Update user info in sidebar
            document.getElementById('userName').textContent = userName;
            document.getElementById('userRole').textContent = userRole.charAt(0).toUpperCase() + userRole.slice(1);
            
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
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebar-overlay');
            
            // Desktop sidebar toggle (header button)
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    toggleSidebar();
                });
            }
            
            // Mobile menu toggle
            if (mobileMenuToggle) {
                mobileMenuToggle.addEventListener('click', function() {
                    toggleSidebar();
                });
            }
            
            // Overlay click to close sidebar
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    closeSidebar();
                });
            }
            
            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    // Desktop/Tablet: remove overlay
                    sidebarOverlay.classList.remove('active');
                }
            });
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.toggle('sidebar--open');
            
            // Only show overlay on mobile devices
            if (window.innerWidth <= 768) {
                sidebarOverlay.classList.toggle('active');
            }
            
            // Add/remove class to body for better control
            if (sidebar.classList.contains('sidebar--open')) {
                document.body.classList.add('sidebar-open');
            } else {
                document.body.classList.remove('sidebar-open');
            }
        }

        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.remove('sidebar--open');
            sidebarOverlay.classList.remove('active');
            document.body.classList.remove('sidebar-open');
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
                    updateSidebarBadge('badge-messages', data.messages || 0);
                    updateSidebarBadge('badge-cases', data.cases || 0);
                    updateSidebarBadge('badge-cart', data.cart || 0);
                    updateSidebarBadge('badge-support-messages', data.support_messages || 0);
                    
                    // Update header notification badge
                    updateHeaderNotificationBadge(data.notifications || 0);
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

        function updateHeaderNotificationBadge(count) {
            const badge = document.getElementById('header-notification-badge');
            if (badge) {
                if (count > 0) {
                    badge.textContent = count > 99 ? '99+' : count;
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
            }
        }

        function setupHeaderNotifications() {
            const notificationsToggle = document.getElementById('notificationsToggle');
            const notificationsDropdown = document.getElementById('notificationsDropdown');
            const notificationsClose = document.getElementById('notificationsClose');
            const notificationsList = document.getElementById('notificationsList');
            
            if (notificationsToggle && notificationsDropdown) {
                notificationsToggle.addEventListener('click', async function(e) {
                    e.stopPropagation();
                    notificationsDropdown.classList.toggle('active');
                    
                    // Load notifications when dropdown is opened
                    if (notificationsDropdown.classList.contains('active')) {
                        await loadHeaderNotifications();
                    }
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function() {
                    notificationsDropdown.classList.remove('active');
                });
                
                // Prevent dropdown from closing when clicking inside
                notificationsDropdown.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
            
            if (notificationsClose) {
                notificationsClose.addEventListener('click', function() {
                    notificationsDropdown.classList.remove('active');
                });
            }
        }

        async function loadHeaderNotifications() {
            try {
                const notificationsList = document.getElementById('notificationsList');
                if (!notificationsList) return;
                
                // Show loading state
                notificationsList.innerHTML = '<div class="notification-empty">Loading notifications...</div>';
                
                const response = await fetch('/app/api/notifications.php?unread=1&limit=5', {
                    method: 'GET',
                    credentials: 'include'
                });
                
                if (response.ok) {
                    const data = await response.json();
                    
                    if (data.items && data.items.length > 0) {
                        let notificationsHTML = '';
                        
                        data.items.forEach(notification => {
                            const timeAgo = getTimeAgo(notification.created_at);
                            const iconClass = getNotificationIconClass(notification.type);
                            const icon = getNotificationIcon(notification.type);
                            
                            notificationsHTML += `
                                <div class="notification-item" data-notification-id="${notification.id}">
                                    <div class="notification-icon ${iconClass}">
                                        <i class="fas ${icon}"></i>
                                    </div>
                                    <div class="notification-content">
                                        <div class="notification-title">${escapeHtml(notification.title)}</div>
                                        <div class="notification-body">${escapeHtml(notification.message || '')}</div>
                                        <div class="notification-meta">${timeAgo}</div>
                                    </div>
                                    ${notification.action_url ? `
                                        <div class="notification-action">
                                            <a href="${normalizeHeaderActionUrl(notification.action_url)}" class="btn-mark-read" onclick="markNotificationAsRead(${notification.id}, this)">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </div>
                                    ` : ''}
                                </div>
                            `;
                        });
                        
                        notificationsList.innerHTML = notificationsHTML;
                    } else {
                        notificationsList.innerHTML = '<div class="notification-empty">No new notifications</div>';
                    }
                } else {
                    notificationsList.innerHTML = '<div class="notification-empty">Failed to load notifications</div>';
                }
            } catch (error) {
                console.log('Failed to load header notifications:', error);
                const notificationsList = document.getElementById('notificationsList');
                if (notificationsList) {
                    notificationsList.innerHTML = '<div class="notification-empty">Error loading notifications</div>';
                }
            }
        }

        function getTimeAgo(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);
            
            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return `${diffMins}m ago`;
            if (diffHours < 24) return `${diffHours}h ago`;
            if (diffDays < 7) return `${diffDays}d ago`;
            
            return date.toLocaleDateString();
        }

        function getNotificationIconClass(type) {
            const iconMap = {
                'info': 'icon-info',
                'message': 'icon-message',
                'message_reply': 'icon-message',
                'support_message': 'icon-message',
                'service_request': 'icon-request',
                'service_approved': 'icon-request',
                'service_rejected': 'icon-request',
                'appointment': 'icon-appointment',
                'appointment_confirmed': 'icon-appointment',
                'appointment_declined': 'icon-appointment',
                'appointment_proposed': 'icon-appointment',
                'appointment_update': 'icon-appointment',
                'success': 'icon-info',
                'warning': 'icon-request',
                'error': 'icon-request'
            };
            
            return iconMap[type] || 'icon-info';
        }

        function getNotificationIcon(type) {
            const iconMap = {
                'info': 'fa-info-circle',
                'message': 'fa-envelope',
                'message_reply': 'fa-reply',
                'support_message': 'fa-headset',
                'service_request': 'fa-clipboard-list',
                'service_approved': 'fa-check-circle',
                'service_rejected': 'fa-times-circle',
                'appointment': 'fa-calendar-check',
                'appointment_confirmed': 'fa-calendar-check',
                'appointment_declined': 'fa-calendar-times',
                'appointment_proposed': 'fa-calendar-plus',
                'appointment_update': 'fa-calendar-alt',
                'success': 'fa-check-circle',
                'warning': 'fa-exclamation-triangle',
                'error': 'fa-times-circle'
            };
            
            return iconMap[type] || 'fa-bell';
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function normalizeHeaderActionUrl(url) {
            if (!url) return '#';
            
            // Remove domain prefix if present
            let normalizedUrl = url.replace(/^\/www\.merlaws\.com/, '');
            normalizedUrl = normalizedUrl.replace(/^https?:\/\/[^\/]+/, '');
            
            // If it's already a relative path starting with ../, use as is
            if (normalizedUrl.startsWith('../')) {
                return normalizedUrl;
            }
            
            // Convert absolute paths to relative from current location
            if (normalizedUrl.startsWith('/app/')) {
                normalizedUrl = normalizedUrl.replace('/app/', '../');
            } else if (normalizedUrl.startsWith('/')) {
                normalizedUrl = '..' + normalizedUrl;
            }
            
            return normalizedUrl;
        }

        async function markNotificationAsRead(notificationId, element) {
            try {
                const response = await fetch('/app/api/notifications.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `mark_read=1&notification_id=${notificationId}`,
                    credentials: 'include'
                });
                
                if (response.ok) {
                    // Remove the notification from the list
                    const notificationItem = element.closest('.notification-item');
                    if (notificationItem) {
                        notificationItem.remove();
                    }
                    
                    // Update badge count
                    await loadSidebarBadges();
                    
                    // If no notifications left, show empty message
                    const notificationsList = document.getElementById('notificationsList');
                    if (notificationsList && notificationsList.children.length === 0) {
                        notificationsList.innerHTML = '<div class="notification-empty">No new notifications</div>';
                    }
                }
            } catch (error) {
                console.log('Failed to mark notification as read:', error);
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
            highlightActivePage: highlightActivePage
        };
    </script>
</body>
</html>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="/index.php">
            <img src="/image/logo.jpg" alt="MER LAW" height="45">
            <div class="ms-2">
                <div class="fw-bold text-danger fs-4 lh-1">MER Law</div>
                <small class="text-muted d-block lh-1" style="font-size: 0.7rem; margin-top: -2px;">Legal Excellence You Can Trust</small>
            </div>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link position-relative" href="/index.php">
                        Home
                        <?php if (basename($_SERVER['PHP_SELF']) == 'index.php'): ?>
                        <span class="position-absolute bottom-0 start-50 translate-middle-x bg-danger" style="height: 2px; width: 20px;"></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link position-relative" href="/our-services.php">
                        Our Services
                        <?php if (basename($_SERVER['PHP_SELF']) == 'our-services.php'): ?>
                        <span class="position-absolute bottom-0 start-50 translate-middle-x bg-danger" style="height: 2px; width: 20px;"></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link position-relative" href="/about-us.php">
                        About Us
                        <?php if (basename($_SERVER['PHP_SELF']) == 'about-us.php'): ?>
                        <span class="position-absolute bottom-0 start-50 translate-middle-x bg-danger" style="height: 2px; width: 20px;"></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link position-relative" href="/contact-us.php">
                        Contact Us
                        <?php if (basename($_SERVER['PHP_SELF']) == 'contact-us.php'): ?>
                        <span class="position-absolute bottom-0 start-50 translate-middle-x bg-danger" style="height: 2px; width: 20px;"></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                    <a class="nav-link btn btn-outline-danger px-3" href="/app/login.php">
                        <i class="fas fa-sign-in-alt me-1"></i> Client Login
                    </a>
                </li>
                <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                    <a class="nav-link btn btn-danger text-white px-3" href="/app/register.php">
                        <i class="fas fa-user-plus me-1"></i> Register
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
    :root {
        --merlaw-primary: #dc3545;
        --merlaw-primary-dark: #c82333;
        --merlaw-secondary: #a61e2a;
    }

    .navbar {
        padding: 0.75rem 0;
        transition: all 0.3s ease;
    }

    .navbar-brand {
        font-weight: 700;
    }

    .nav-link {
        font-weight: 500;
        color: #2d3748 !important;
        padding: 0.5rem 1rem !important;
        margin: 0 0.25rem;
        border-radius: 8px;
        transition: all 0.3s ease;
        position: relative;
    }

    .nav-link:hover {
        color: var(--merlaw-primary) !important;
        background-color: rgba(220, 53, 69, 0.05);
    }

    .nav-link.btn {
        padding: 0.5rem 1.5rem !important;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .nav-link.btn-outline-danger {
        border: 2px solid var(--merlaw-primary);
        color: var(--merlaw-primary) !important;
    }

    .nav-link.btn-outline-danger:hover {
        background-color: var(--merlaw-primary);
        color: white !important;
        transform: translateY(-1px);
    }

    .nav-link.btn-danger {
        background: linear-gradient(135deg, var(--merlaw-primary), var(--merlaw-primary-dark));
        border: none;
    }

    .nav-link.btn-danger:hover {
        background: linear-gradient(135deg, var(--merlaw-primary-dark), var(--merlaw-secondary));
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    }

    /* Active state for navigation items */
    .nav-link.active {
        color: var(--merlaw-primary) !important;
        font-weight: 600;
    }

    /* Fix for the active indicator positioning */
    .nav-link.position-relative {
        padding-bottom: 0.75rem !important;
    }

    @media (max-width: 991.98px) {
        .navbar-collapse {
            padding: 1rem 0;
        }
        
        .nav-item {
            margin: 0.25rem 0;
        }
        
        .nav-link {
            padding: 0.75rem 1rem !important;
            margin: 0;
        }
        
        .nav-link.btn {
            width: 100%;
            text-align: center;
            margin: 0.25rem 0;
        }

        /* Fix for mobile navbar toggler */
        .navbar-toggler:focus {
            box-shadow: none;
        }
    }
</style>
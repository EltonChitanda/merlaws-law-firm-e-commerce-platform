<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand" href="/index.php">
            <img src="/image/logo.webp" alt="MerLaws" height="40" class="public-logo-img">
            <span class="ms-2 fw-bold text-danger d-none d-sm-inline">MerLaws</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/our-services.php">Our Services</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/about-us.php">About Us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/contact-us.php">Contact Us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link btn btn-outline-danger ms-2 px-3 public-nav-btn" href="/app/login.php">
                        <i class="fas fa-sign-in-alt me-1"></i> <span class="d-none d-md-inline">Client </span>Login
                    </a>
                </li>
                <li class="nav-item" style="display: block !important; visibility: visible !important;">
                    <a class="nav-link btn btn-danger text-white ms-2 px-3 public-nav-btn" href="/app/register.php" style="display: inline-block !important; visibility: visible !important;">
                        <i class="fas fa-user-plus me-1"></i> Register
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
/* Public Header Mobile Enhancements */
.navbar-toggler {
    min-width: 44px;
    min-height: 44px;
    padding: 0.5rem;
    border: 1px solid rgba(0,0,0,0.1);
    border-radius: 6px;
    -webkit-tap-highlight-color: rgba(0,0,0,0.1);
    touch-action: manipulation;
}

.navbar-toggler:focus {
    box-shadow: 0 0 0 0.25rem rgba(172, 19, 42, 0.25);
}

.public-logo-img {
    max-width: 100%;
    height: auto;
    object-fit: contain;
}

@media (max-width: 768px) {
    .public-logo-img {
        height: 32px !important;
    }
    
    .navbar-brand {
        padding: 0.5rem 0;
    }
}

@media (max-width: 480px) {
    .public-logo-img {
        height: 28px !important;
    }
    
    .navbar {
        padding: 0.5rem 0;
    }
    
    .navbar-brand {
        font-size: 0.9rem;
    }
}

.public-nav-btn {
    min-height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
    -webkit-tap-highlight-color: rgba(172, 19, 42, 0.2);
    touch-action: manipulation;
}

@media (max-width: 768px) {
    .navbar-nav {
        padding-top: 1rem;
        padding-bottom: 1rem;
    }
    
    .nav-item {
        margin-bottom: 0.5rem;
    }
    
    .nav-link {
        padding: 0.75rem 1rem;
        font-size: 1rem;
        min-height: 44px;
        display: flex;
        align-items: center;
    }
    
    .public-nav-btn {
        width: 100%;
        margin-left: 0 !important;
        margin-top: 0.5rem;
        justify-content: center;
    }
    
    .navbar-nav .btn {
        margin: 0.25rem 0;
    }
}

@media (max-width: 480px) {
    .nav-link {
        padding: 0.875rem 1rem;
        font-size: 0.95rem;
    }
    
    .public-nav-btn {
        font-size: 0.9rem;
        padding: 0.75rem 1rem;
    }
}

/* Improve mobile menu collapse animation */
.navbar-collapse {
    transition: all 0.3s ease;
}

/* Ensure proper spacing on mobile */
@media (max-width: 991px) {
    .navbar-nav .nav-link {
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    
    .navbar-nav .nav-link:last-child {
        border-bottom: none;
    }
}
</style>

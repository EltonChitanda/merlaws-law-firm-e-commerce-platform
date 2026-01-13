<?php require __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Client Portal Login | Med Attorneys</title>
    
    <!-- Security Headers -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' https://www.google.com https://www.gstatic.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data:; font-src 'self' https://fonts.gstatic.com;">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="Referrer-Policy" content="strict-origin-when-cross-origin">
    
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="../favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon/favicon-16x16.png">
    <link rel="manifest" href="../favicon/site.webmanifest">
    
    <!-- External CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUa/K2nJ+ZdCsqzO5vJoMsEZn6OW0fSkZZBJ1Bq3F5H6kH5H6kH5H6kH5H6k" crossorigin="anonymous">
    
    <style>
        :root {
            --merlaws-primary: #AC132A;
            --merlaws-primary-dark: #8a0f22;
            --merlaws-primary-light: #c41e39;
            --merlaws-secondary: #2c3e50;
            --merlaws-gold: #d4af37;
            --merlaws-white: #ffffff;
            --merlaws-gray-50: #f8fafc;
            --merlaws-gray-100: #f1f5f9;
            --merlaws-gray-200: #e2e8f0;
            --merlaws-gray-300: #cbd5e1;
            --merlaws-gray-400: #94a3b8;
            --merlaws-gray-500: #64748b;
            --merlaws-gray-600: #475569;
            --merlaws-gray-700: #334155;
            --merlaws-gray-800: #1e293b;
            --merlaws-gray-900: #0f172a;
            --merlaws-danger: #dc2626;
            --merlaws-success: #059669;
            --merlaws-warning: #d97706;
            --merlaws-info: #0284c7;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--merlaws-gray-50) 0%, var(--merlaws-gray-100) 100%);
            min-height: 100vh;
            margin: 0;
            color: var(--merlaws-gray-800);
            line-height: 1.6;
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .login-card {
            background: var(--merlaws-white);
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid var(--merlaws-gray-200);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
            position: relative;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--merlaws-primary) 0%, var(--merlaws-gold) 100%);
        }

        .login-header {
            text-align: center;
            padding: 3rem 2rem 2rem;
            background: var(--merlaws-white);
            position: relative;
        }

        .firm-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--merlaws-primary) 0%, var(--merlaws-primary-dark) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 10px 25px -5px rgba(172, 19, 42, 0.3);
        }

        .firm-logo::after {
            content: '⚖';
            font-size: 2rem;
            color: var(--merlaws-white);
        }

        .login-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--merlaws-gray-900);
            margin: 0 0 0.5rem;
            letter-spacing: -0.025em;
        }

        .login-subtitle {
            color: var(--merlaws-gray-600);
            font-size: 1rem;
            margin: 0;
            font-weight: 400;
        }

        .login-body {
            padding: 0 2rem 3rem;
        }

        .alert {
            border-radius: 0.75rem;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            border: none;
            font-weight: 500;
        }

        .alert-danger {
            background: #fef2f2;
            color: var(--merlaws-danger);
            border-left: 4px solid var(--merlaws-danger);
        }

        .alert-info {
            background: #eff6ff;
            color: var(--merlaws-info);
            border-left: 4px solid var(--merlaws-info);
        }

        .alert-success {
            background: #f0fdf4;
            color: var(--merlaws-success);
            border-left: 4px solid var(--merlaws-success);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: var(--merlaws-gray-700);
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--merlaws-gray-300);
            border-radius: 0.75rem;
            font-size: 1rem;
            background: var(--merlaws-gray-50);
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--merlaws-primary);
            background: var(--merlaws-white);
            box-shadow: 0 0 0 3px rgba(172, 19, 42, 0.1);
            transform: translateY(-1px);
        }

        .form-control.is-invalid {
            border-color: var(--merlaws-danger);
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        .password-field {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--merlaws-gray-500);
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 0.25rem;
            transition: color 0.2s ease;
        }

        .password-toggle:hover {
            color: var(--merlaws-primary);
        }

        .btn-primary {
            width: 100%;
            background: linear-gradient(135deg, var(--merlaws-primary) 0%, var(--merlaws-primary-dark) 100%);
            border: none;
            color: var(--merlaws-white);
            padding: 0.875rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(172, 19, 42, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .form-footer a {
            color: var(--merlaws-primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
            transition: color 0.2s ease;
        }

        .form-footer a:hover {
            color: var(--merlaws-primary-dark);
            text-decoration: underline;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: var(--merlaws-gray-500);
            font-size: 0.875rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--merlaws-gray-300);
        }

        .divider span {
            padding: 0 1rem;
            background: var(--merlaws-white);
        }

        .security-notice {
            background: var(--merlaws-gray-50);
            border: 1px solid var(--merlaws-gray-200);
            border-radius: 0.75rem;
            padding: 1rem;
            margin-top: 1.5rem;
            font-size: 0.875rem;
            color: var(--merlaws-gray-600);
            text-align: center;
        }

        .security-notice svg {
            width: 1rem;
            height: 1rem;
            margin-right: 0.5rem;
            vertical-align: middle;
        }

        .loading-spinner {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 2px solid var(--merlaws-white);
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.8s linear infinite;
            margin-right: 0.5rem;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .login-container {
                padding: 1rem;
                align-items: flex-start;
                padding-top: 2rem;
            }

            .login-card {
                border-radius: 1rem;
                max-width: 100%;
            }

            .login-header {
                padding: 2rem 1.5rem 1.5rem;
            }

            .login-body {
                padding: 0 1.5rem 2rem;
            }

            .login-title {
                font-size: 1.5rem;
            }
            
            .login-subtitle {
                font-size: 0.9rem;
            }
            
            .firm-logo {
                width: 70px;
                height: 70px;
            }
            
            .firm-logo::after {
                font-size: 1.75rem;
            }

            .form-footer {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }
            
            .form-control {
                font-size: 16px; /* Prevents zoom on iOS */
                padding: 14px 16px;
                min-height: 48px;
            }
            
            .btn {
                width: 100%;
                padding: 14px 20px;
                font-size: 16px;
                min-height: 48px;
            }
        }
        
        @media (max-width: 480px) {
            .login-container {
                padding: 0.75rem;
                padding-top: 1.5rem;
            }
            
            .login-header {
                padding: 1.5rem 1rem 1rem;
            }
            
            .login-body {
                padding: 0 1rem 1.5rem;
            }
            
            .login-title {
                font-size: 1.35rem;
            }
            
            .firm-logo {
                width: 60px;
                height: 60px;
                margin-bottom: 1rem;
            }
            
            .firm-logo::after {
                font-size: 1.5rem;
            }
            
            .form-group {
                margin-bottom: 1.25rem;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            :root {
                --merlaws-gray-50: #0f172a;
                --merlaws-gray-100: #1e293b;
                --merlaws-white: #334155;
            }
        }

        /* High contrast mode */
        @media (prefers-contrast: high) {
            .form-control {
                border-width: 3px;
            }
        }

        /* Reduced motion */
        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="firm-logo"></div>
                <h1 class="login-title">Client Portal</h1>
                <p class="login-subtitle">Secure access to your legal services</p>
            </div>

            <div class="login-body">
                <div id="errorAlert" class="alert alert-danger" style="display: none;">
                    <ul id="errorList" class="mb-0"></ul>
                </div>

                <div id="infoAlert" class="alert alert-info" style="display: none;">
                    <p id="infoMessage" class="mb-0"></p>
                </div>

                <form id="loginForm" method="post" action="api/auth-login.php" novalidate>
                    <?php require __DIR__ . '/csrf.php'; echo csrf_field(); ?>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address *</label>
                        <input 
                            type="email" 
                            class="form-control" 
                            id="email" 
                            name="email" 
                            required 
                            autocomplete="username"
                            placeholder="your.email@example.com"
                            aria-describedby="emailHelp"
                        >
                        <div class="invalid-feedback" id="emailError"></div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password *</label>
                        <div class="password-field">
                            <input 
                                type="password" 
                                class="form-control" 
                                id="password" 
                                name="password" 
                                required 
                                autocomplete="current-password"
                                placeholder="Enter your password"
                                aria-describedby="passwordHelp"
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path id="eyeIcon" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle id="eyeCircle" cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                        <div class="invalid-feedback" id="passwordError"></div>
                    </div>

                    <button type="submit" class="btn btn-primary" id="loginBtn">
                        <span id="loginText">Sign In</span>
                        <span id="loginSpinner" class="loading-spinner" style="display: none;"></span>
                    </button>

                    <div class="form-footer">
                        <a href="forgot-password.php">Forgot Password?</a>
                        <a href="register.php">Create Account</a>
                    </div>
                </form>

                <div class="divider">
                    <span>Need Help?</span>
                </div>

                <div class="security-notice">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <circle cx="12" cy="16" r="1"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    Your connection is secured with 256-bit SSL encryption
                </div>
            </div>
        </div>
    </div>

    <!-- External Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>

    <script>
        class LoginManager {
            constructor() {
                this.form = document.getElementById('loginForm');
                this.emailField = document.getElementById('email');
                this.passwordField = document.getElementById('password');
                this.loginBtn = document.getElementById('loginBtn');
                this.loginText = document.getElementById('loginText');
                this.loginSpinner = document.getElementById('loginSpinner');
                this.errorAlert = document.getElementById('errorAlert');
                this.errorList = document.getElementById('errorList');
                this.infoAlert = document.getElementById('infoAlert');
                this.infoMessage = document.getElementById('infoMessage');
                
                this.init();
            }

            init() {
                this.generateCSRFToken();
                this.bindEvents();
                this.checkUrlParams();
                this.prefillEmail();
            }

            generateCSRFToken() {
                // Token now comes from server-side via hidden field
                const el = document.getElementById('csrfToken');
                if (!el) return;
            }

            generateSecureToken(length) {
                const array = new Uint8Array(length);
                crypto.getRandomValues(array);
                return Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('');
            }

            bindEvents() {
                this.form.addEventListener('submit', (e) => this.handleSubmit(e));
                this.emailField.addEventListener('blur', () => this.validateEmail());
                this.passwordField.addEventListener('input', () => this.clearFieldError('password'));
                
                // Real-time validation
                this.emailField.addEventListener('input', () => {
                    if (this.emailField.value) this.validateEmail();
                });
            }

            checkUrlParams() {
                const urlParams = new URLSearchParams(window.location.search);
                const error = urlParams.get('error');
                const message = urlParams.get('message');
                const registered = urlParams.get('registered');

                if (error) {
                    this.showError([this.getErrorMessage(error)]);
                }

                if (message) {
                    // Show success message with special styling if coming from registration
                    if (registered === '1') {
                        this.showSuccess(decodeURIComponent(message));
                    } else {
                        this.showInfo(decodeURIComponent(message));
                    }
                }
            }

            getErrorMessage(errorCode) {
                const messages = {
                    'session_expired': 'Your session has expired. Please log in again.',
                    'insufficient_permissions': 'You do not have permission to access that resource.',
                    'admin_required': 'Administrator access required.',
                    'rate_limited': 'Too many login attempts. Please try again later.',
                    'invalid_credentials': 'Invalid email or password.',
                    'account_deactivated': 'Your account has been deactivated. Please contact support.'
                };

                return messages[errorCode] || 'An error occurred. Please try again.';
            }

            prefillEmail() {
                const rememberedEmail = localStorage.getItem('merlaws_login_email');
                if (rememberedEmail) {
                    this.emailField.value = rememberedEmail;
                }
            }

            validateEmail() {
                const email = this.emailField.value.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (!email) {
                    this.setFieldError('email', 'Email address is required');
                    return false;
                }

                if (!emailRegex.test(email)) {
                    this.setFieldError('email', 'Please enter a valid email address');
                    return false;
                }

                this.clearFieldError('email');
                return true;
            }

            validatePassword() {
                const password = this.passwordField.value;

                if (!password) {
                    this.setFieldError('password', 'Password is required');
                    return false;
                }

                if (password.length < 8) {
                    this.setFieldError('password', 'Password must be at least 8 characters');
                    return false;
                }

                this.clearFieldError('password');
                return true;
            }

            setFieldError(fieldName, message) {
                const field = document.getElementById(fieldName);
                const errorElement = document.getElementById(fieldName + 'Error');

                field.classList.add('is-invalid');
                if (errorElement) {
                    errorElement.textContent = message;
                }
            }

            clearFieldError(fieldName) {
                const field = document.getElementById(fieldName);
                const errorElement = document.getElementById(fieldName + 'Error');

                field.classList.remove('is-invalid');
                if (errorElement) {
                    errorElement.textContent = '';
                }
            }

            showError(errors) {
                this.errorList.innerHTML = '';
                errors.forEach(error => {
                    const li = document.createElement('li');
                    li.textContent = error;
                    this.errorList.appendChild(li);
                });
                
                this.errorAlert.style.display = 'block';
                this.infoAlert.style.display = 'none';
                
                // Scroll to top to show error
                this.errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            showInfo(message) {
                this.infoMessage.textContent = message;
                this.infoAlert.style.display = 'block';
                this.infoAlert.className = 'alert alert-info';
                this.errorAlert.style.display = 'none';
            }

            showSuccess(message) {
                this.infoMessage.textContent = message;
                this.infoAlert.style.display = 'block';
                this.infoAlert.className = 'alert alert-success';
                this.errorAlert.style.display = 'none';
            }

            hideAlerts() {
                this.errorAlert.style.display = 'none';
                this.infoAlert.style.display = 'none';
            }

            async handleSubmit(e) {
                e.preventDefault();
                
                this.hideAlerts();
                
                // Validate form
                const emailValid = this.validateEmail();
                const passwordValid = this.validatePassword();
                
                if (!emailValid || !passwordValid) {
                    this.showError(['Please correct the errors above']);
                    return;
                }

                this.setLoading(true);

                try {
                    const formData = new FormData(this.form);

                    const response = await fetch(this.form.action || window.location.pathname, {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });

                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }
                    const result = await response.json();

                    if (result.success) {
                        // Remember email for next time
                        localStorage.setItem('merlaws_login_email', this.emailField.value);
                        
                        // Redirect to dashboard or intended page
                        const host = window.location.host;
                        const basePath = '';
                        const paramRedirect = new URLSearchParams(window.location.search).get('redirect');
                        const safeRedirect = (paramRedirect && paramRedirect.startsWith(basePath)) ? (window.location.origin + paramRedirect) : (window.location.origin + basePath + '/app/dashboard.php');
                        window.location.href = (result.redirect && typeof result.redirect === 'string' && result.redirect.startsWith(window.location.origin + basePath)) ? result.redirect : safeRedirect;
                    } else {
                        this.showError(result.errors || [result.message || 'Login failed']);
                    }

                } catch (error) {
                    console.error('Login error:', error);
                    this.showError(['Network error. Please check your connection and try again.']);
                } finally {
                    this.setLoading(false);
                }
            }

            setLoading(loading) {
                this.loginBtn.disabled = loading;
                
                if (loading) {
                    this.loginText.style.display = 'none';
                    this.loginSpinner.style.display = 'inline-block';
                } else {
                    this.loginText.style.display = 'inline';
                    this.loginSpinner.style.display = 'none';
                }
            }
        }

        // Password toggle functionality
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            const eyeCircle = document.getElementById('eyeCircle');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.setAttribute('d', 'M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19');
                eyeCircle.style.display = 'none';
            } else {
                passwordField.type = 'password';
                eyeIcon.setAttribute('d', 'M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z');
                eyeCircle.style.display = 'block';
            }
        }

        // Initialize login manager when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            new LoginManager();
        });

        // Security: Clear sensitive data on page unload
        window.addEventListener('beforeunload', () => {
            document.getElementById('password').value = '';
        });
    </script>
</body>
</html>
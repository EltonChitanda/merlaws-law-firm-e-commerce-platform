<?php
/**
 * Enhanced Contact Page - MerLaws
 * contact-us.php
 * 
 * Professional contact page with form handling and modern design
 */

// Load database configuration
require_once __DIR__ . '/app/config.php';

// Initialize variables
$form_submitted = false;
$form_errors = [];
$form_success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_valid'])) {
    // Validate form token for security
    if (empty($_POST['form_token']) || empty($_POST['form_token_name'])) {
        $form_errors[] = 'Invalid form submission';
    }
    
    // Sanitize and validate inputs
    $first_name = trim($_POST['first'] ?? '');
    $last_name = trim($_POST['last'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validation
    if (empty($first_name)) $form_errors[] = 'First name is required';
    if (empty($last_name)) $form_errors[] = 'Last name is required';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form_errors[] = 'Valid email is required';
    }
    if (empty($phone)) $form_errors[] = 'Phone number is required';
    if (empty($message)) $form_errors[] = 'Message is required';
    
    // If no errors, process the form
    if (empty($form_errors)) {
        try {
            $pdo = db();
            
            // Save to database
            $stmt = $pdo->prepare("
                INSERT INTO contact_submissions (first_name, last_name, email, phone, subject, message, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'new', NOW())
            ");
            $stmt->execute([$first_name, $last_name, $email, $phone, $subject, $message]);
            $submission_id = $pdo->lastInsertId();
            
            // Create notifications for relevant admin users
            // Roles: receptionist, intake, super_admin, office_admin, partner
            $stmt = $pdo->prepare("
                SELECT id FROM users 
                WHERE role IN ('receptionist', 'intake', 'super_admin', 'office_admin', 'partner') 
                AND is_active = 1
            ");
            $stmt->execute();
            $admin_users = $stmt->fetchAll();
            
            foreach ($admin_users as $admin) {
                $notif_stmt = $pdo->prepare("
                    INSERT INTO user_notifications (user_id, type, title, message, action_url, is_read, created_at) 
                    VALUES (?, 'info', 'New Contact Form Submission', ?, ?, 0, NOW())
                ");
                $notif_message = "New contact form submission from {$first_name} {$last_name}";
                $action_url = "/app/admin/contact-submissions.php?id=" . $submission_id;
                $notif_stmt->execute([$admin['id'], $notif_message, $action_url]);
            }
            
            $form_success = true;
            
            // Clear form data on success
            $first_name = $last_name = $email = $phone = $subject = $message = '';
            
        } catch (Exception $e) {
            // Log error but don't expose to user
            error_log("Contact form submission error: " . $e->getMessage());
            $form_errors[] = 'There was an error submitting your message. Please try again or call us directly.';
        }
    }
}

// Generate CSRF token
$form_token = bin2hex(random_bytes(32));
$form_token_name = 'token-' . time();

// Page metadata
$page_title = "Contact Us | MerLaws";
$page_description = "Get in touch with MerLaws. Request a free consultation for your medical negligence or road accident fund case.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
    <link rel="manifest" href="favicon/site.webmanifest">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Original Styles -->
    <link rel="stylesheet" href="css/default.css">
    
    <!-- Google Tag Manager -->
    <script src="script/google.js"></script>
    
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <style>
        :root {
            --merlaws-primary: #AC132A;
            --merlaws-primary-dark: #8a0f22;
            --merlaws-primary-light: #d41e3a;
            --merlaws-secondary: #1a365d;
            --merlaws-gold: #d69e2e;
            --merlaws-success: #38a169;
            --merlaws-danger: #e53e3e;
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            color: #2d3748;
            line-height: 1.6;
        }

        /* Enhanced Hero Banner */
        .page-header {
            position: relative;
            background: linear-gradient(135deg, var(--merlaws-primary) 0%, var(--merlaws-primary-dark) 50%, var(--merlaws-secondary) 100%);
            color: white;
            padding: 5rem 0 3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><pattern id="grid" width="50" height="50" patternUnits="userSpaceOnUse"><path d="M 50 0 L 0 0 0 50" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }

        .page-header .container {
            position: relative;
            z-index: 2;
            text-align: center;
        }

        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 0 4px 6px rgba(0,0,0,0.2);
        }

        .page-header .lead {
            opacity: 0.9;
        }

        /* Consultation Bar */
        .consultation-bar {
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
            padding: 2rem;
            text-align: center;
            box-shadow: var(--shadow-lg);
        }

        .consult-button-enhanced {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            background: white;
            color: var(--merlaws-primary);
            padding: 1.5rem 3rem;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.25rem;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .consult-button-enhanced:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 35px rgba(0,0,0,0.25);
            color: var(--merlaws-primary-dark);
        }

        /* Main Content */
        .section-enhanced {
            padding: 3rem 0 5rem;
            background: linear-gradient(135deg, var(--merlaws-gray-50) 0%, #edf2f7 100%);
        }

        /* Breadcrumb */
        .breadcrumb-enhanced {
            background: white;
            padding: 1rem 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
        }

        .breadcrumb-enhanced a {
            color: var(--merlaws-primary);
            text-decoration: none;
            font-weight: 600;
        }

        .breadcrumb-enhanced a:hover {
            text-decoration: underline;
        }

        /* Section Title */
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 600;
            color: var(--merlaws-secondary);
            margin-bottom: 3rem;
            text-align: center;
            position: relative;
            padding-bottom: 1rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, var(--merlaws-primary), var(--merlaws-gold));
            border-radius: 2px;
        }

        /* Form Styling */
        .form-container {
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: var(--shadow-xl);
            border: 1px solid #e2e8f0;
            margin-bottom: 3rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--merlaws-secondary);
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control-enhanced {
            width: 100%;
            padding: 1rem 1.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f7fafc;
        }

        .form-control-enhanced:focus {
            outline: none;
            border-color: var(--merlaws-primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(172, 19, 42, 0.1);
        }

        textarea.form-control-enhanced {
            min-height: 150px;
            resize: vertical;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .submit-button {
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
            color: white;
            padding: 1.25rem 3rem;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .submit-button:hover {
            background: linear-gradient(135deg, var(--merlaws-primary-dark), var(--merlaws-secondary));
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(172, 19, 42, 0.4);
        }

        .call-button {
            background: var(--merlaws-gold);
            color: var(--merlaws-secondary);
            padding: 1.25rem 3rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.1rem;
            display: inline-block;
            transition: all 0.3s ease;
            text-align: center;
        }

        .call-button:hover {
            background: #c99029;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(214, 158, 46, 0.4);
        }

        /* Alert Messages */
        .alert-enhanced {
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            border-left: 4px solid;
        }

        .alert-success {
            background: #f0fff4;
            border-color: var(--merlaws-success);
            color: #22543d;
        }

        .alert-danger {
            background: #fff5f5;
            border-color: var(--merlaws-danger);
            color: #742a2a;
        }

        .alert-enhanced ul {
            margin: 0.5rem 0 0 1.5rem;
        }

        /* Office Cards */
        .offices-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }

        .office-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .office-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--merlaws-primary), var(--merlaws-gold));
        }

        .office-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }

        .office-card h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--merlaws-secondary);
            margin-bottom: 1rem;
        }

        .office-card p {
            color: #4a5568;
            line-height: 1.8;
            margin-bottom: 1.5rem;
            flex-grow: 1;
        }

        .map-button {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
            color: white;
            padding: 0.875rem 1.75rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: auto;
        }

        .map-button:hover {
            background: linear-gradient(135deg, var(--merlaws-primary-dark), var(--merlaws-secondary));
            color: white;
            transform: translateX(5px);
        }

        .map-button i {
            margin-right: 0.5rem;
        }

        /* Contact Methods */
        .contact-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }

        .contact-method {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            box-shadow: var(--shadow-lg);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .contact-method:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
        }

        .contact-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-gold));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 2rem;
        }

        .contact-method h4 {
            font-weight: 700;
            color: var(--merlaws-secondary);
            margin-bottom: 1rem;
        }

        .contact-method a {
            color: var(--merlaws-primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .contact-method a:hover {
            text-decoration: underline;
        }

        /* Map Image */
        .map-image {
            text-align: center;
            margin: 3rem 0;
        }

        .map-image img {
            max-width: 100%;
            height: auto;
            border-radius: 24px;
            box-shadow: var(--shadow-xl);
        }

        /* Office Photos Grid */
        .office-photos {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin: 3rem 0;
        }

        .office-photo {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
        }

        .office-photo:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-xl);
        }

        .office-photo img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            display: block;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }

            .page-title {
                font-size: 2.5rem;
            }

            .form-container, .office-card {
                padding: 2rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .offices-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header Include -->
    <div id="header"></div>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container text-center">
            <h1 class="page-title">Contact Us</h1>
            <p class="lead">We're here to help. Get in touch with our legal team today.</p>
        </div>
    </div>

    <!-- Consultation Bar -->
    <section class="consultation-bar">
        <a href="tel:0100407611" class="consult-button-enhanced">
            <div>
                <div style="font-size: 0.9rem; opacity: 0.9;">REQUEST A FREE CONSULTATION</div>
                <div style="font-size: 1.5rem; font-weight: 800;">CALL 010 040 7611</div>
            </div>
        </a>
    </section>

    <!-- Main Content -->
    <section class="section-enhanced">
        <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb-enhanced">
            <strong>You are here:</strong> <a href="index.php">Home</a> / Contact Us
        </div>
        <!-- Contact Methods -->
        <div class="contact-methods">
            <div class="contact-method">
                <div class="contact-icon">
                    <i class="fas fa-phone-alt"></i>
                </div>
                <h4>Call Us</h4>
                <a href="tel:0800633529">0800 MERLAWS (633529)</a>
                <p style="margin-top: 0.5rem; color: #718096; font-size: 0.9rem;">Mon-Fri: 8:00 AM - 5:00 PM</p>
            </div>

            <div class="contact-method">
                <div class="contact-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <h4>Email Us</h4>
                <a href="mailto:info@merlaws.com">info@merlaws.com</a>
                <p style="margin-top: 0.5rem; color: #718096; font-size: 0.9rem;">We respond within 24 hours</p>
            </div>

            <div class="contact-method">
                <div class="contact-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h4>Visit Us</h4>
                <p style="color: #4a5568; margin-bottom: 0.5rem;">8 Hawley Road, Bedfordview</p>
                <a href="https://goo.gl/maps/wQNHpiF4tHJ12Wfj8" target="_blank">Get Directions</a>
            </div>
        </div>

        <!-- Contact Form -->
        <h2 class="section-title" style="margin-top: 4rem;">Send Us a Message</h2>

        <?php if ($form_success): ?>
        <div class="alert-enhanced alert-success">
            <strong><i class="fas fa-check-circle"></i> Thank you for contacting us!</strong>
            <p style="margin: 0.5rem 0 0 0;">Your message has been received. A member of our team will contact you within 24 hours.</p>
        </div>
        <?php endif; ?>

        <?php if (!empty($form_errors)): ?>
        <div class="alert-enhanced alert-danger">
            <strong><i class="fas fa-exclamation-triangle"></i> Please correct the following errors:</strong>
            <ul>
                <?php foreach ($form_errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="form-container">
            <form action="contact-us.php" method="POST" id="contact-form">
                <input type="hidden" name="form_valid" value="1">
                <input type="hidden" name="form_token_name" value="<?php echo htmlspecialchars($form_token_name); ?>">
                <input type="hidden" name="form_token" value="<?php echo htmlspecialchars($form_token); ?>">
                <input type="hidden" id="gtoken" name="gtoken">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first" class="form-label">First Name *</label>
                        <input 
                            type="text" 
                            id="first" 
                            name="first" 
                            class="form-control-enhanced" 
                            value="<?php echo htmlspecialchars($first_name ?? ''); ?>"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="last" class="form-label">Last Name *</label>
                        <input 
                            type="text" 
                            id="last" 
                            name="last" 
                            class="form-control-enhanced" 
                            value="<?php echo htmlspecialchars($last_name ?? ''); ?>"
                            required
                        >
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email" class="form-label">Your Email *</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-control-enhanced" 
                            value="<?php echo htmlspecialchars($email ?? ''); ?>"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="phone" class="form-label">Your Phone Number *</label>
                        <input 
                            type="tel" 
                            id="phone" 
                            name="phone" 
                            class="form-control-enhanced" 
                            value="<?php echo htmlspecialchars($phone ?? ''); ?>"
                            required
                        >
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="subject" class="form-label">Subject</label>
                    <input 
                        type="text" 
                        id="subject" 
                        name="subject" 
                        class="form-control-enhanced" 
                        value="<?php echo htmlspecialchars($subject ?? ''); ?>"
                    >
                </div>
                
                <div class="form-group">
                    <label for="message" class="form-label">Message *</label>
                    <textarea 
                        id="message" 
                        name="message" 
                        class="form-control-enhanced"
                        required
                    ><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                </div>
                
                <div class="form-row" style="margin-top: 2rem;">
                    <a href="tel:0800633529" class="call-button">
                        <i class="fas fa-phone"></i> CALL US ON 0800 MERLAWS (633529)
                    </a>
                    <button type="submit" class="submit-button">
                        <i class="fas fa-paper-plane"></i> SUBMIT MESSAGE
                    </button>
                </div>
            </form>
        </div>

        <!-- Our Offices -->
        <h2 class="section-title" style="margin-top: 4rem;">Our Offices</h2>

        <!-- Map Image -->
        <div class="map-image">
            <img src="image/country-1.webp" alt="South Africa Office Locations">
        </div>

        <!-- Office Locations -->
        <div class="offices-grid">
            <div class="office-card">
                <h3>BEDFORDVIEW</h3>
                <p>
                    8 Hawley Road,<br>
                    Bedfordview,<br>
                    Gauteng
                </p>
                <a href="https://maps.app.goo.gl/4w4KVdNaL5tT5FAL6" target="_blank" class="map-button">
                    <i class="fas fa-map-marker-alt"></i> View on Google Maps
                </a>
            </div>

            <div class="office-card">
                <h3>DURBAN</h3>
                <p>
                    2 Ncondo Place,<br>
                    Umhlanga Rocks,<br>
                    Durban
                </p>
                <a href="https://maps.app.goo.gl/4w4KVdNaL5tT5FAL6" target="_blank" class="map-button">
                    <i class="fas fa-map-marker-alt"></i> View on Google Maps
                </a>
            </div>

            <div class="office-card">
                <h3>PRETORIA</h3>
                <p>
                    2nd floor 2N5, Hilda Law<br>
                    Chambers, 339 Hilda Street,<br>
                    Hatfield, Pretoria
                </p>
                <a href="https://goo.gl/maps/kbzbPPPeiHLen3Sm9" target="_blank" class="map-button">
                    <i class="fas fa-map-marker-alt"></i> View on Google Maps
                </a>
            </div>

            <div class="office-card">
                <h3>BLOEMFONTEIN</h3>
                <p>
                    Office G03, Regus Business<br>
                    Centre, Ground Floor,<br>
                    Unipark Building,<br>
                    Vodacom Lane, Nobel Street,<br>
                    Brandwag, Bloemfontein
                </p>
                <a href="https://goo.gl/maps/jJByBJ9Yfcd4Pn78A" target="_blank" class="map-button">
                    <i class="fas fa-map-marker-alt"></i> View on Google Maps
                </a>
            </div>

            <div class="office-card">
                <h3>MBOMBELA</h3>
                <p>
                    Office G20, Regus Business<br>
                    Centre, Cor. Van Der Merwe<br>
                    and Ferreira Streets,<br>
                    Nelspruit
                </p>
                <a href="https://goo.gl/maps/BCCLtBhcDZ8NCKXS8" target="_blank" class="map-button">
                    <i class="fas fa-map-marker-alt"></i> View on Google Maps
                </a>
            </div>

            <div class="office-card">
                <h3>MIDDLEBURG</h3>
                <p>
                    141 Cowen Ntuli Street,<br>
                    Middelburg
                </p>
                <a href="https://goo.gl/maps/fRSkrkZbmMwQjNum6" target="_blank" class="map-button">
                    <i class="fas fa-map-marker-alt"></i> View on Google Maps
                </a>
            </div>

            <div class="office-card">
                <h3>LADYSMITH</h3>
                <p>
                    Unit 6, Queensgate Building,<br>
                    65 Queen Street<br>
                    (corner of Keate street)<br>
                    Ladysmith, 3370
                </p>
                <a href="https://goo.gl/maps/qfSYMv8jagHKtWT99" target="_blank" class="map-button">
                    <i class="fas fa-map-marker-alt"></i> View on Google Maps
                </a>
            </div>

            <div class="office-card">
                <h3>PHUTHADITJHABA</h3>
                <p>
                    1st Floor,<br>
                    Old Mutual Building,<br>
                    Motloung Street<br>
                    Phuthaditjhaba
                </p>
                <a href="https://goo.gl/maps/BjS5SLFcuLEeZbfy9" target="_blank" class="map-button">
                    <i class="fas fa-map-marker-alt"></i> View on Google Maps
                </a>
            </div>
        </div>

        <!-- Head Office Photos -->
        <h2 class="section-title" style="margin-top: 4rem;">Our Head Office</h2>
        
        <div class="office-photos">
            <div class="office-photo">
                <img src="image/head-office-1-1.webp" alt="Head Office Building">
            </div>
            <div class="office-photo">
                <img src="image/head-office-2-1.webp" alt="Head Office Reception">
            </div>
            <div class="office-photo">
                <img src="image/head-office-3-1.webp" alt="Head Office Conference Room">
            </div>
            <div class="office-photo">
                <img src="image/head-office-4-1.webp" alt="Head Office Interior">
            </div>
        </div>

        <!-- Final CTA -->
        <div style="background: white; border-radius: 24px; padding: 3rem; text-align: center; box-shadow: var(--shadow-xl); margin-top: 4rem;">
            <h3 style="font-family: 'Playfair Display', serif; font-size: 2rem; color: var(--merlaws-secondary); margin-bottom: 1.5rem;">
                Ready to Get Started?
            </h3>
            <p style="color: #4a5568; font-size: 1.1rem; margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto;">
                Contact us today for a free consultation. Our experienced legal team is ready to help you get the compensation you deserve.
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="tel:0800633529" class="call-button">
                    <i class="fas fa-phone"></i> CALL 0800 MERLAWS (633529)
                </a>
                <a href="mailto:info@merlaws.com" class="map-button">
                    <i class="fas fa-envelope"></i> EMAIL INFO@MERLAWS.CO.ZA
                </a>
            </div>
        </div>
        </div>
    </div>

    <!-- Footer Include -->
    <div id="footer"></div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://www.google.com/recaptcha/api.js?render=6LdKX-IpAAAAAFSXsbD8KRl6dGmeBFqC_i7K0njj"></script>
    
    <script>
        // Load header and footer
        (async function(){
            try {
                document.getElementById('header').innerHTML = await (await fetch('include/public-header.php')).text();
                document.getElementById('footer').innerHTML = await (await fetch('include/footer.php')).text();
            } catch(e){ 
                console.error('Include load failed', e); 
            }
        })();

        // Form validation and submission
        document.getElementById('contact-form').addEventListener('submit', function(e) {
            // Add loading state
            const submitBtn = this.querySelector('.submit-button');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            submitBtn.disabled = true;

            // In a local environment, we bypass reCAPTCHA to avoid site key errors.
            // The form will submit directly. On a live server, the reCAPTCHA
            // script should be active.
            // e.preventDefault(); // Keep this commented out for direct submission
        });

        // Phone number formatting
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0) {
                if (value.length <= 3) {
                    e.target.value = value;
                } else if (value.length <= 6) {
                    e.target.value = value.slice(0, 3) + ' ' + value.slice(3);
                } else {
                    e.target.value = value.slice(0, 3) + ' ' + value.slice(3, 6) + ' ' + value.slice(6, 10);
                }
            }
        });

        // Smooth scroll for anchor links
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

        // Form field animations
        document.querySelectorAll('.form-control-enhanced').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-2px)';
                this.parentElement.style.transition = 'transform 0.3s ease';
            });

            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });

        // Animate office cards on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '0';
                    entry.target.style.transform = 'translateY(30px)';
                    
                    setTimeout(() => {
                        entry.target.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, 100);
                    
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.office-card, .contact-method').forEach(card => {
            observer.observe(card);
        });

        // Auto-dismiss success message after 10 seconds
        const successAlert = document.querySelector('.alert-success');
        if (successAlert) {
            setTimeout(() => {
                successAlert.style.transition = 'opacity 0.5s ease';
                successAlert.style.opacity = '0';
                setTimeout(() => {
                    successAlert.remove();
                }, 500);
            }, 10000);
        }
    </script>
</body>
</html>
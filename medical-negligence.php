<?php
/**
 * Medical Negligence Service Page
 * Redesigned with modern professional styling
 */
require_once __DIR__ . '/app/config.php';

$pageTitle = "Medical Negligence | Our Services";
$pageDescription = "Expert medical negligence legal services. When healthcare professionals breach their duty of care, we fight for your rights and full compensation.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <title><?php echo htmlspecialchars($pageTitle); ?> | Med Attorneys</title>
    
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
    <link rel="manifest" href="favicon/site.webmanifest">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="css/default.css">
    
    <style>
        :root {
            --merlaws-primary: #AC132A;
            --merlaws-primary-dark: #8a0f22;
            --merlaws-primary-light: #d41e3a;
            --merlaws-secondary: #1a365d;
            --merlaws-accent: #f7fafc;
            --merlaws-gold: #d69e2e;
            --merlaws-gray-50: #f7fafc;
            --merlaws-gray-100: #edf2f7;
            --merlaws-gray-200: #e2e8f0;
            --merlaws-gray-600: #4a5568;
            --merlaws-gray-800: #1a202c;
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            color: var(--merlaws-gray-800);
            line-height: 1.7;
        }

        /* Hero Section */
        .service-hero {
            background: linear-gradient(135deg, var(--merlaws-primary) 0%, var(--merlaws-primary-dark) 50%, var(--merlaws-secondary) 100%);
            color: white;
            padding: 5rem 0 3rem;
            position: relative;
            overflow: hidden;
        }

        .service-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><pattern id="grid" width="50" height="50" patternUnits="userSpaceOnUse"><path d="M 50 0 L 0 0 0 50" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
            pointer-events: none;
        }

        .breadcrumb-modern {
            background: rgba(255, 255, 255, 0.1);
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
        }

        .breadcrumb-modern a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .breadcrumb-modern a:hover {
            color: var(--merlaws-gold);
        }

        .breadcrumb-modern i {
            font-size: 0.75rem;
            opacity: 0.6;
        }

        .service-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .service-hero .lead {
            font-size: 1.25rem;
            opacity: 0.95;
            max-width: 800px;
            margin: 0 auto;
        }

        /* Content Container */
        .content-container {
            max-width: 1200px;
            margin: -3rem auto 4rem;
            padding: 0 1rem;
            position: relative;
            z-index: 10;
        }

        /* Main Content Card */
        .content-card {
            background: white;
            border-radius: 24px;
            padding: 3rem;
            box-shadow: var(--shadow-xl);
            margin-bottom: 2rem;
        }

        .content-card h2 {
            font-family: 'Playfair Display', serif;
            color: var(--merlaws-primary);
            font-size: 2rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 3px solid var(--merlaws-gold);
        }

        .content-card p {
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 1.5rem;
            color: var(--merlaws-gray-600);
        }

        /* Specialist Card */
        .specialist-card {
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--merlaws-gray-200);
            position: relative;
            overflow: hidden;
        }

        .specialist-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--merlaws-primary), var(--merlaws-gold));
        }

        .specialist-content {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 2rem;
            align-items: center;
        }

        .specialist-image {
            width: 200px;
            height: 200px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            border: 4px solid var(--merlaws-gray-100);
        }

        .specialist-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .specialist-info h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.75rem;
            color: var(--merlaws-gray-800);
            margin-bottom: 1rem;
        }

        .specialist-info h3 span {
            color: var(--merlaws-primary);
            display: block;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .specialist-info p {
            color: var(--merlaws-gray-600);
            margin-bottom: 1.5rem;
            line-height: 1.7;
        }

        .contact-details {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 1rem;
            background: var(--merlaws-gray-50);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .contact-item:hover {
            background: var(--merlaws-primary);
            color: white;
            transform: translateX(5px);
        }

        .contact-item i {
            font-size: 1.25rem;
            color: var(--merlaws-primary);
            width: 30px;
            text-align: center;
        }

        .contact-item:hover i {
            color: white;
        }

        .contact-item a {
            color: inherit;
            text-decoration: none;
            font-weight: 600;
        }

        /* CTA Button */
        .cta-button {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
            color: white;
            padding: 1rem 2.5rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(172, 19, 42, 0.3);
            margin: 2rem 0;
        }

        .cta-button:hover {
            background: linear-gradient(135deg, var(--merlaws-primary-dark), var(--merlaws-secondary));
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(172, 19, 42, 0.4);
        }

        .cta-button i {
            margin-left: 0.75rem;
            transition: transform 0.3s ease;
        }

        .cta-button:hover i {
            transform: translateX(5px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .service-hero h1 {
                font-size: 2.5rem;
            }

            .content-card {
                padding: 2rem;
            }

            .specialist-content {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .specialist-image {
                margin: 0 auto;
            }

            .contact-details {
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <!-- Include Header -->
    <?php 
    $headerPath = __DIR__ . '/include/header.php';
    if (file_exists($headerPath)) {
        echo file_get_contents($headerPath);
    }
    ?>

    <!-- Hero Section -->
    <section class="service-hero">
        <div class="container text-center position-relative">
            <div class="breadcrumb-modern">
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
                <i class="fas fa-chevron-right"></i>
                <a href="our-services.php">Our Services</a>
                <i class="fas fa-chevron-right"></i>
                <span>Medical Negligence</span>
            </div>
            <h1>Medical Negligence</h1>
            <p class="lead">Fighting for justice when healthcare professionals breach their duty of care</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="content-container">
        <div class="content-card">
            <h2>Expert Medical Negligence Representation</h2>
            
            <p>When a medical practitioner who you have entrusted with your or your family's health and safety deviates from the standards of that practitioner's profession, the consequences are often devastating. If you or a loved one has been harmed or injured due to poor medical treatment or mistaken diagnosis, we can guarantee the specialist investigation and aggressive pursuit of full compensation for your injuries.</p>

            <p>You have the right to trust medical practitioners to look after you properly. If your health care professional, nurse or the staff of a hospital or other facility breaches what is known as their duty of care, then you may be able to make a claim for compensation. At MED Attorneys, we have the experienced medical malpractice team, dedicated to helping you get your life back on track. No matter what you are up against, MED will fight to protect you.</p>

            <div class="text-center">
                <a href="contact-us.php" class="cta-button">
                    Get Your Free Consultation
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Specialist Card -->
        <div class="specialist-card">
            <div class="specialist-content">
                <div class="specialist-image">
                    <img src="image/nicole-1.webp" alt="Nicole - Medical Negligence Specialist">
                </div>
                <div class="specialist-info">
                    <h3>
                        Talk to a Medical Negligence Specialist
                        <span>Nicole - Your Dedicated Advisor</span>
                    </h3>
                    <p>Obtain free initial advice and the opportunity to talk through your experience with Nicole, our medical negligence specialist. We understand the complexity of medical malpractice cases and are here to guide you every step of the way.</p>
                    
                    <div class="contact-details">
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <a href="tel:0824982567">082 498 2567</a>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:nicoled@merlaws.com">nicoled@merlaws.com</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Footer -->
    <?php 
    $footerPath = __DIR__ . '/include/footer.html';
    if (file_exists($footerPath)) {
        echo file_get_contents($footerPath);
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
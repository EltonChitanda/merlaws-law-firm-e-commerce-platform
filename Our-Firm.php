<?php
/**
 * Our Firm - About Med Attorneys
 * Professional version matching dashboard styling
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Learn about Med Attorneys - South Africa's premier medical law firm with a proven track record of success.">
    <title>Our Firm | About Us | Med Attorneys</title>
    
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
    
    <link rel="stylesheet" href="css/default.css">
    
    <style>
        :root {
            --merlaws-primary: #AC132A;
            --merlaws-primary-dark: #8a0f22;
            --merlaws-primary-light: #d41e3a;
            --merlaws-secondary: #1a365d;
            --merlaws-accent: #f7fafc;
            --merlaws-gold: #d69e2e;
            --merlaws-success: #38a169;
            --merlaws-warning: #ed8936;
            --merlaws-info: #3182ce;
            --merlaws-gray-50: #f7fafc;
            --merlaws-gray-100: #edf2f7;
            --merlaws-gray-200: #e2e8f0;
            --merlaws-gray-300: #cbd5e0;
            --merlaws-gray-400: #a0aec0;
            --merlaws-gray-500: #718096;
            --merlaws-gray-600: #4a5568;
            --merlaws-gray-700: #2d3748;
            --merlaws-gray-800: #1a202c;
            --merlaws-gray-900: #171923;
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            color: var(--merlaws-gray-800);
            line-height: 1.6;
        }

        /* Hero Banner */
        .hero-banner {
            position: relative;
            height: 500px;
            background: linear-gradient(135deg, var(--merlaws-primary) 0%, var(--merlaws-primary-dark) 50%, var(--merlaws-secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .hero-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><pattern id="grid" width="50" height="50" patternUnits="userSpaceOnUse"><path d="M 50 0 L 0 0 0 50" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
            pointer-events: none;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            text-align: center;
            color: white;
            max-width: 1200px;
            padding: 0 2rem;
        }

        .hero-content h1 {
            font-family: 'Playfair Display', serif;
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }

        .hero-content p {
            font-size: 1.5rem;
            opacity: 0.95;
            font-weight: 300;
        }

        /* Consultation Button */
        .consultation-cta {
            position: fixed;
            right: 2rem;
            top: 50%;
            transform: translateY(-50%);
            z-index: 1000;
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
            color: white;
            padding: 1.5rem 1rem;
            border-radius: 16px;
            text-align: center;
            text-decoration: none;
            box-shadow: 0 10px 40px rgba(172, 19, 42, 0.4);
            transition: all 0.3s ease;
            width: 150px;
        }

        .consultation-cta:hover {
            background: linear-gradient(135deg, var(--merlaws-primary-dark), var(--merlaws-secondary));
            color: white;
            transform: translateY(-50%) scale(1.05);
            box-shadow: 0 15px 50px rgba(172, 19, 42, 0.5);
        }

        .consultation-cta .cta-request {
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .consultation-cta .cta-free {
            font-size: 2rem;
            font-weight: 800;
            color: var(--merlaws-gold);
            line-height: 1;
            margin: 0.5rem 0;
        }

        .consultation-cta .cta-consultation {
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .consultation-cta .cta-call {
            font-size: 0.75rem;
            font-weight: 500;
            border-top: 1px solid rgba(255,255,255,0.3);
            padding-top: 0.75rem;
        }

        .consultation-cta .cta-phone {
            font-size: 1.1rem;
            font-weight: 700;
            margin-top: 0.25rem;
        }

        /* Content Container */
        .content-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 4rem 2rem;
        }

        /* Breadcrumb */
        .breadcrumb-custom {
            background: white;
            border-radius: 16px;
            padding: 1.25rem 2rem;
            margin-bottom: 3rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--merlaws-gray-200);
        }

        .breadcrumb-custom a {
            color: var(--merlaws-primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .breadcrumb-custom a:hover {
            color: var(--merlaws-primary-dark);
        }

        /* Section Header */
        .section-header {
            background: white;
            border-radius: 24px;
            padding: 3rem;
            margin-bottom: 3rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--merlaws-gray-200);
            position: relative;
            overflow: hidden;
        }

        .section-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--merlaws-primary), var(--merlaws-gold));
        }

        .section-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--merlaws-gray-800);
            margin-bottom: 1.5rem;
        }

        .section-header p {
            font-size: 1.15rem;
            color: var(--merlaws-gray-700);
            line-height: 1.8;
        }

        /* Value Cards Grid */
        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .value-card {
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--merlaws-gray-200);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .value-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--merlaws-primary), var(--merlaws-gold));
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .value-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-xl);
        }

        .value-card:hover::before {
            transform: scaleX(1);
        }

        .value-icon {
            width: 90px;
            height: 90px;
            border-radius: 20px;
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 25px rgba(172, 19, 42, 0.3);
        }

        .value-icon i {
            font-size: 2.5rem;
            color: white;
        }

        .value-card h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--merlaws-gray-800);
            margin-bottom: 1rem;
        }

        .value-card p {
            color: var(--merlaws-gray-600);
            line-height: 1.7;
            font-size: 1rem;
        }

        /* Success Quote */
        .success-quote {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            border-radius: 24px;
            padding: 3rem;
            margin: 4rem 0;
            position: relative;
            border: 2px solid var(--merlaws-gold);
            box-shadow: var(--shadow-lg);
        }

        .success-quote::before {
            content: '"';
            position: absolute;
            top: -20px;
            left: 40px;
            font-size: 6rem;
            color: var(--merlaws-gold);
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            line-height: 1;
        }

        .success-quote-content {
            position: relative;
            z-index: 1;
        }

        .success-quote h4 {
            font-family: 'Playfair Display', serif;
            font-size: 1.75rem;
            color: #92400e;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .success-quote p {
            font-size: 1.15rem;
            color: #78350f;
            line-height: 1.8;
            margin-bottom: 2rem;
        }

        .success-quote .btn-view-more {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background: var(--merlaws-primary);
            color: white;
            padding: 1rem 2rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }

        .success-quote .btn-view-more:hover {
            background: var(--merlaws-primary-dark);
            color: white;
            transform: translateX(5px);
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
            border-radius: 24px;
            padding: 4rem;
            text-align: center;
            color: white;
            box-shadow: var(--shadow-xl);
            margin-top: 4rem;
        }

        .cta-section h3 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .cta-section p {
            font-size: 1.25rem;
            opacity: 0.95;
            margin-bottom: 2rem;
        }

        .cta-section .btn-contact {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background: white;
            color: var(--merlaws-primary);
            padding: 1.25rem 2.5rem;
            border-radius: 16px;
            text-decoration: none;
            font-size: 1.25rem;
            font-weight: 700;
            transition: all 0.3s ease;
            border: none;
        }

        .cta-section .btn-contact:hover {
            background: var(--merlaws-gold);
            color: var(--merlaws-gray-900);
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .consultation-cta {
                right: 1rem;
            }
        }

        @media (max-width: 768px) {
            .hero-banner {
                height: 350px;
            }

            .hero-content h1 {
                font-size: 2.5rem;
            }

            .hero-content p {
                font-size: 1.15rem;
            }

            .consultation-cta {
                position: fixed;
                bottom: 1rem;
                right: 1rem;
                top: auto;
                transform: none;
                width: 120px;
                padding: 1rem 0.75rem;
            }

            .consultation-cta:hover {
                transform: scale(1.05);
            }

            .content-container {
                padding: 2rem 1rem;
            }

            .values-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .section-header,
            .success-quote,
            .cta-section {
                padding: 2rem;
            }

            .section-header h2 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <?php 
    $headerPath = __DIR__ . '/include/header.php';
    if (file_exists($headerPath)) {
        echo file_get_contents($headerPath);
    }
    ?>

    <!-- Hero Banner -->
    <div class="hero-banner">
        <div class="hero-content">
            <h1>About Our Firm</h1>
            <p>The Distinct Name in Medical Law</p>
        </div>
    </div>

    <!-- Floating Consultation Button -->
    <a href="tel:0100407611" class="consultation-cta">
        <div class="cta-request">REQUEST A</div>
        <div class="cta-free">FREE</div>
        <div class="cta-consultation">CONSULTATION</div>
        <div class="cta-call">
            <div>CALL</div>
            <div class="cta-phone">010 040 7611</div>
        </div>
    </a>

    <!-- Main Content -->
    <div class="content-container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb-custom">
            <i class="fas fa-home"></i>
            <strong>You are here:</strong> 
            <a href="index.php">Home</a> / 
            <a href="our-firm.php">About</a> / 
            <span>Our Firm</span>
        </nav>

        <!-- About Section -->
        <div class="section-header">
            <h2><i class="fas fa-building"></i> About Our Firm</h2>
            <p>
                MED Attorneys is one of South Africa's most prolific and respected law firms, with offices throughout the country. In addition to our specialist injury lawyers, we operate in a network of emergency responders, medical experts and investigators. These considerable resources, along with our ability to advance litigation costs and funding in our clients' cases and our no-win-no-fee services, enable MED Attorneys to hold individuals, corporations and government accountable. And accountability keeps people safe.
            </p>
        </div>

        <!-- Why MED Attorneys Section -->
        <div class="section-header">
            <h2><i class="fas fa-star"></i> Why MED Attorneys?</h2>
        </div>

        <!-- Values Grid -->
        <div class="values-grid">
            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-universal-access"></i>
                </div>
                <h3>Accessibility</h3>
                <p>
                    MED Attorneys has become one of South Africa's most prolific law firms through its unwavering belief that the law should serve everyone. We are here to take your call and to give you advice, ensuring that you get the answers, respect and access to the assistance you need during difficult times.
                </p>
            </div>

            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Protection</h3>
                <p>
                    We all need someone to protect our interests in times of hardship and suffering. The world isn't always a fair place and our clients deserve to have someone stand up for their rights. We fight for you with uncompromising tenacity and we are here to help and guard you on the path to justice.
                </p>
            </div>

            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-user-friends"></i>
                </div>
                <h3>Personal Attention</h3>
                <p>
                    Our firm's attorneys share a passion for the practice of law and are dedicated to being available to our clients at all times. By working closely with our clients and by doing so with compassion and integrity, we are always at your side to help give you peace of mind.
                </p>
            </div>

            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <h3>Track Record</h3>
                <p>
                    We take great pride in our innovative approach to the practice of law and our firm's role in cases resulting in landmark decisions and precedent-setting rulings. We never give up and have successfully appealed cases to the Constitutional Court.
                </p>
            </div>

            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <h3>No Win - No Fee</h3>
                <p>
                    We believe that every South African should have access to justice, not just those who can afford it. That is why we have a flexible fee policy that recognises the varying circumstances of our clients including a no-win-no-fee arrangement. In all cases, we offer a free first consultation.
                </p>
            </div>
        </div>

        <!-- Success Quote -->
        <div class="success-quote">
            <div class="success-quote-content">
                <h4><i class="fas fa-award"></i> Recent Successes</h4>
                <p>
                    In July 2016 the MED team obtained an amount of R4,393,849.00 in respect of a minor child who sustained a brain injury and various soft tissue injuries and whose educational potential has been affected.
                </p>
                <a href="our-successes.php" class="btn-view-more">
                    See More Successes
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="cta-section">
            <h3>Ready to Fight for Your Rights?</h3>
            <p>Contact us today and get the compensation you deserve</p>
            <a href="contact-us.php" class="btn-contact">
                <i class="fas fa-phone-alt"></i>
                Contact Us Today
            </a>
        </div>
    </div>

    <?php 
    $footerPath = __DIR__ . '/include/footer.html';
    if (file_exists($footerPath)) {
        echo file_get_contents($footerPath);
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animate cards on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            entry.target.style.opacity = '1';
                            entry.target.style.transform = 'translateY(0)';
                        }, index * 100);
                    }
                });
            }, observerOptions);

            const cards = document.querySelectorAll('.value-card');
            cards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = 'all 0.6s ease';
                observer.observe(card);
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
        });
    </script>
</body>
</html>
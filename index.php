<?php
/**
 * Enhanced Home Page - MER Law
 * index.php
 * 
 * Professional landing page with modern design matching dashboard theme
 */

// Optional: Add session handling if needed
// session_start();

// Page metadata
$page_title = "MER Law - The distinct name in medical law";
$page_description = "Medical Negligence & Road Accident Fund Specialists. Obtaining justice for the injured across South Africa.";
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
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            color: #2d3748;
            line-height: 1.6;
        }

        /* Enhanced Hero Section */
        .hero-banner {
            position: relative;
            height: 70vh;
            min-height: 500px;
            background: linear-gradient(135deg, var(--merlaws-primary) 0%, var(--merlaws-primary-dark) 50%, var(--merlaws-secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
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
            opacity: 0.3;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 1200px;
            padding: 2rem;
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-shadow: 0 4px 6px rgba(0,0,0,0.2);
            animation: fadeInUp 0.8s ease-out;
        }

        .hero-subtitle {
            font-size: 1.5rem;
            font-weight: 300;
            margin-bottom: 2rem;
            opacity: 0.95;
            animation: fadeInUp 1s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Enhanced CTA Button */
        .cta-button {
            display: inline-flex;
            align-items: center;
            background: var(--merlaws-gold);
            color: var(--merlaws-secondary);
            padding: 1.5rem 3rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.25rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(214, 158, 46, 0.4);
            animation: fadeInUp 1.2s ease-out;
        }

        .cta-button:hover {
            background: #c99029;
            color: white;
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(214, 158, 46, 0.6);
        }

        .cta-button i {
            margin-right: 0.75rem;
            font-size: 1.5rem;
        }

        /* Enhanced Consultation Bar */
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

        /* Enhanced Section Styling */
        .section-enhanced {
            padding: 5rem 0;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            font-weight: 700;
            color: var(--merlaws-secondary);
            text-align: center;
            margin-bottom: 1rem;
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

        .section-subtitle {
            text-align: center;
            font-size: 1.25rem;
            color: #718096;
            max-width: 800px;
            margin: 0 auto 3rem;
        }

        /* Enhanced Practice Area Cards */
        .practice-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .practice-card {
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .practice-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--merlaws-primary), var(--merlaws-gold));
        }

        .practice-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-xl);
        }

        .practice-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 25px rgba(172, 19, 42, 0.3);
        }

        .practice-icon img {
            width: 50px;
            height: 50px;
            filter: brightness(0) invert(1);
        }

        .practice-card h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--merlaws-secondary);
            margin-bottom: 1rem;
        }

        .practice-card p {
            color: #4a5568;
            margin-bottom: 1.5rem;
            line-height: 1.7;
            flex-grow: 1; /* Allow paragraph to grow and take available space */
        }

        .card-button {
            margin-top: auto; /* Push the button to the bottom */
            display: inline-flex;
            align-items: center;
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
            color: white;
            padding: 0.875rem 1.75rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .card-button:hover {
            background: linear-gradient(135deg, var(--merlaws-primary-dark), var(--merlaws-secondary));
            color: white;
            transform: translateX(5px);
        }

        .card-button i {
            margin-left: 0.5rem;
        }

        /* Enhanced Stats Section */
        .stats-section {
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
            padding: 5rem 0;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .stats-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><pattern id="grid" width="50" height="50" patternUnits="userSpaceOnUse"><path d="M 50 0 L 0 0 0 50" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            position: relative;
            z-index: 1;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, white, var(--merlaws-gold));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .stat-divider {
            width: 60px;
            height: 3px;
            background: var(--merlaws-gold);
            margin: 1rem auto;
        }

        /* Enhanced Why Choose Us Section */
        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .benefit-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-md);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .benefit-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }

        .benefit-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-gold));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .benefit-icon img {
            width: 40px;
            height: 40px;
            filter: brightness(0) invert(1);
        }

        .benefit-card h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--merlaws-secondary);
            text-align: center;
            margin-bottom: 1rem;
        }

        .benefit-card p {
            color: #4a5568;
            text-align: center;
            line-height: 1.7;
        }

        /* Enhanced Testimonial */
        .testimonial-section {
            background: linear-gradient(135deg, #f7fafc, #edf2f7);
            padding: 5rem 0;
        }

        .testimonial-card {
            background: white;
            border-radius: 24px;
            padding: 3rem;
            max-width: 900px;
            margin: 0 auto;
            box-shadow: var(--shadow-xl);
            position: relative;
        }

        .testimonial-card::before {
            content: '"';
            position: absolute;
            top: -20px;
            left: 30px;
            font-size: 8rem;
            font-family: 'Playfair Display', serif;
            color: var(--merlaws-primary);
            opacity: 0.1;
        }

        .testimonial-text {
            font-size: 1.25rem;
            font-style: italic;
            color: #2d3748;
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .testimonial-author {
            font-weight: 700;
            color: var(--merlaws-primary);
            font-size: 1.1rem;
        }

        /* Enhanced Map Section */
        .map-section {
            padding: 5rem 0;
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
            color: white;
            position: relative;
            overflow: hidden;
        }

        .map-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><pattern id="grid" width="50" height="50" patternUnits="userSpaceOnUse"><path d="M 50 0 L 0 0 0 50" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
        }

        .offices-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .office-badge {
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
            color: white;
            padding: 1rem;
            border-radius: 12px;
            text-align: center;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .office-badge:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(172, 19, 42, 0.3);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 1.2rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .practice-cards,
            .benefits-grid,
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header Include -->
    <div id="header"></div>

    <!-- Enhanced Hero Banner -->
    <section class="hero-banner">
        <div class="hero-content">
            <h1 class="hero-title">Medical Negligence & Road Accident Fund Specialists</h1>
            <p class="hero-subtitle">Obtaining justice for the injured across South Africa</p>

        </div>
    </section>

    <!-- Enhanced Consultation Bar -->
    <section class="consultation-bar">
        <a href="tel:0100407611" class="consult-button-enhanced">
            <div>
                <div style="font-size: 0.9rem; opacity: 0.9;">REQUEST A FREE CONSULTATION</div>
                <div style="font-size: 1.5rem; font-weight: 800;">CALL 010 040 7611</div>
            </div>
        </a>
    </section>

    <!-- Main Content Section -->
    <section class="section-enhanced">
        <div class="container">
            <h2 class="section-title">Obtaining Justice for the Injured</h2>
            <p class="section-subtitle">
                MER Law are compassionate but uncompromising specialist injury lawyers 
                that represent only Plaintiffs. We have proudly built our practice on the belief that we can make a 
                genuine difference in the lives of those who need our help.
            </p>

            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div style="background: white; padding: 3rem; border-radius: 24px; box-shadow: var(--shadow-lg); border: 1px solid #e2e8f0;">
                        <p style="color: #4a5568; line-height: 1.8; margin-bottom: 1.5rem;">
                            We work closely with our clients throughout the litigation process to maximise and expedite each recovery. 
                            Each of our clients is assigned an attorney who is responsible for prosecuting the case and regularly 
                            communicating progress. At the same time, our attorneys work as a team, drawing upon their combined 
                            knowledge, training and skills to provide our clients with decades of litigation expertise.
                        </p>
                        <p style="color: #4a5568; line-height: 1.8; margin-bottom: 0;">
                            View our client charter which explains our principles, our commitment to our clients and the way we 
                            will work with you to get the best possible result. This promise applies to anyone who contacts us 
                            for information or assistance including existing and potential clients.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Enhanced Testimonial -->
    <section class="testimonial-section">
        <div class="container">
            <div class="testimonial-card">
                <p class="testimonial-text">
                    "MER Law was the best choice I ever made. An attorney with a good heart, and who truly cares 
                    for his client's best interests. Keep up the fantastic work."
                </p>
                <p class="testimonial-author">- Gladstone</p>
                <div class="text-center mt-4">
                    <a href="testimonials.php" class="card-button">
                        See What Other Clients Say <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Holding Wrongdoers Accountable -->
    <section class="section-enhanced" style="background: #f7fafc;">
        <div class="container">
            <h2 class="section-title">Holding Wrongdoers Accountable</h2>
            <p class="section-subtitle">
                For decades, we have been fighting to obtain justice for those who have been treated unfairly.
            </p>

            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div style="background: white; padding: 3rem; border-radius: 24px; box-shadow: var(--shadow-lg);">
                        <p style="color: #4a5568; line-height: 1.8; margin-bottom: 1.5rem;">
                            If you are facing an unfair situation, you and your family should not have to suffer alone. 
                            We never stand back when injustice is suffered because we believe you are worth fighting for.
                        </p>
                        <p style="color: #4a5568; line-height: 1.8; margin-bottom: 0;">
                            Each year, thousands are injured in South Africa. Many of these accidents are caused by the 
                            carelessness, negligence or even the malicious intent of others. Often people don't realise 
                            they are entitled to compensation or they feel that they do not have the resources to take on 
                            a powerful institution. We aim to change that. We believe that all South Africans should have 
                            access to justice and we make sure that they do.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Areas of Practice -->
    <section class="section-enhanced">
        <div class="container">
            <h2 class="section-title">Areas of Practice</h2>
            <p class="section-subtitle">Specialized legal services tailored to your needs</p>

            <div class="practice-cards">
                <div class="practice-card">
                    <div class="practice-icon">
                        <img src="image/icon-heart.webp" alt="Medical Malpractice">
                    </div>
                    <h3>Medical Malpractice</h3>
                    <p>
                        When a medical practitioner who you have entrusted with your or your family's health and safety 
                        deviates from the standards of that practitioner's profession, the consequences are often devastating. 
                        We guarantee the specialist investigation and aggressive action required to obtain full compensation.
                    </p>
                    <a href="medical-negligence.php" class="card-button">
                        Learn More <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <div class="practice-card">
                    <div class="practice-icon">
                        <img src="image/icon-product.webp" alt="Product Liability">
                    </div>
                    <h3>Product Liability</h3>
                    <p>
                        MER Law has directed its substantial resources towards taking on big business, international 
                        pharmaceutical corporations, public institutions, government and individuals to protect the rights 
                        of commuters, patients and consumers.
                    </p>
                    <a href="product-liability.php" class="card-button">
                        Learn More <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <div class="practice-card">
                    <div class="practice-icon">
                        <img src="image/icon-premise.webp" alt="Premises Liability">
                    </div>
                    <h3>Premises Liability</h3>
                    <p>
                        We represent persons across South Africa who have been seriously injured due to dangerous conditions 
                        created in private and public places. Our lawyers work closely with victims and their families 
                        throughout the entire legal process.
                    </p>
                    <a href="premises-liability.php" class="card-button">
                        Learn More <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <div class="practice-card">
                    <div class="practice-icon">
                        <img src="image/icon-car.webp" alt="Motor Vehicle Accidents">
                    </div>
                    <h3>Motor Vehicle Accidents (Road Accident Fund)</h3>
                    <p>
                        We operate as part of a network of emergency, medical and financial experts and investigators 
                        enabling us to thoroughly examine any accident, to identify all responsible parties and to ensure 
                        that you are fully compensated for your injuries.
                    </p>
                    <a href="motor-vehicle-accidents.php" class="card-button">
                        Learn More <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Success Stats -->
    <section class="stats-section">
        <div class="container">
            <h2 class="section-title" style="color: white;">Our Successes</h2>
            <p class="section-subtitle" style="color: rgba(255,255,255,0.9);">
                Proven track record of excellence in legal representation
            </p>

            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">100%</div>
                    <div class="stat-divider"></div>
                    <div class="stat-label">Success rate on lodged claims</div>
                </div>

                <div class="stat-item">
                    <div class="stat-number">2,178</div>
                    <div class="stat-divider"></div>
                    <div class="stat-label">Current client base</div>
                </div>

                <div class="stat-item">
                    <div class="stat-number">R1.36M</div>
                    <div class="stat-divider"></div>
                    <div class="stat-label">Average settlement in 2023</div>
                </div>
            </div>

            <div class="text-center mt-5" id="see-more-successes-button-container">
                <a href="our-successes.php" class="cta-button" style="padding: 1rem 2.5rem; font-size: 1.1rem;">
                    See More Successes <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Why MER Law -->
    <section class="section-enhanced">
        <div class="container">
            <h2 class="section-title">Why MER Law?</h2>
            <p class="section-subtitle">Dedicated to excellence in legal representation</p>

            <div class="benefits-grid">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <img src="image/icon-accessibility.webp" alt="Accessibility">
                    </div>
                    <h3>ACCESSIBILITY</h3>
                    <p>
                        We are here to take your call and to give you advice, ensuring that you get the answers, 
                        respect and access to the assistance you need during difficult times.
                    </p>
                </div>

                <div class="benefit-card">
                    <div class="benefit-icon">
                        <img src="image/icon-protection.webp" alt="Protection">
                    </div>
                    <h3>PROTECTION</h3>
                    <p>
                        We fight for you with uncompromising tenacity and we are here to help and guard you on 
                        the path to justice.
                    </p>
                </div>

                <div class="benefit-card">
                    <div class="benefit-icon">
                        <img src="image/icon-personal.webp" alt="Personal Attention">
                    </div>
                    <h3>PERSONAL ATTENTION</h3>
                    <p>
                        By working closely with our clients and by doing so with compassion and integrity, we are 
                        always at your side to help give you peace of mind.
                    </p>
                </div>

                <div class="benefit-card">
                    <div class="benefit-icon">
                        <img src="image/icon-track-record.webp" alt="Track Record">
                    </div>
                    <h3>TRACK RECORD</h3>
                    <p>
                        We take great pride in our innovative approach to the practice of law and our firm's role 
                        in cases resulting in landmark decisions.
                    </p>
                </div>

                <div class="benefit-card">
                    <div class="benefit-icon">
                        <img src="image/icon-no-win-no-fee.webp" alt="No Win No Fee">
                    </div>
                    <h3>NO WIN - NO FEE</h3>
                    <p>
                        We have a flexible fee policy that recognises the varying circumstances of our clients 
                        including a no-win-no-fee arrangement.
                    </p>
                </div>
            </div>

        </div>
    </section>

    <!-- Map & Offices -->
    <section class="map-section">
        <div class="container">
            <h2 class="section-title" style="color: white;">Wherever You Are in South Africa We Can Help</h2>
            <p class="section-subtitle" style="color: rgba(255,255,255,0.9);">Convenient offices located throughout South Africa</p>

            <div style="text-align: center; margin: 3rem 0;">
                <img src="image/map.webp" alt="South Africa Map" style="max-width: 100%; height: auto; border-radius: 24px; box-shadow: var(--shadow-lg);">
            </div>

            <div class="offices-grid">
                <div class="office-badge">Johannesburg</div>
                <div class="office-badge">Pretoria</div>
                <div class="office-badge">Bloemfontein</div>
                <div class="office-badge">Durban</div>
                <div class="office-badge">Middleburg</div>
                <div class="office-badge">Nelspruit</div>
                <div class="office-badge">Ladysmith</div>
                <div class="office-badge">Phuthaditjhaba</div>
            </div>

        </div>
    </section>

    <!-- Footer Include -->
    <div id="footer"></div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script/gsap.min.js"></script>
    <script src="script/ScrollTrigger.min.js"></script>
    
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

        // Animate statistics on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const statNumbers = document.querySelectorAll('.stat-number');
            
            const observerOptions = {
                threshold: 0.5,
                rootMargin: '0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animateValue(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            statNumbers.forEach(stat => observer.observe(stat));
        });

        function animateValue(element) {
            const text = element.textContent;
            const hasPercent = text.includes('%');
            const hasM = text.includes('M');
            const hasComma = text.includes(',');
            
            let targetValue;
            if (hasPercent) {
                targetValue = parseInt(text);
            } else if (hasM) {
                targetValue = parseFloat(text.replace('R', '').replace('M', ''));
            } else {
                targetValue = parseInt(text.replace(',', ''));
            }

            let current = 0;
            const increment = targetValue / 50;
            const duration = 2000;
            const stepTime = duration / 50;

            const timer = setInterval(() => {
                current += increment;
                if (current >= targetValue) {
                    current = targetValue;
                    clearInterval(timer);
                }

                if (hasPercent) {
                    element.textContent = Math.floor(current) + '%';
                } else if (hasM) {
                    element.textContent = 'R' + current.toFixed(2) + 'M';
                } else if (hasComma) {
                    element.textContent = Math.floor(current).toLocaleString();
                } else {
                    element.textContent = Math.floor(current);
                }
            }, stepTime);
        }

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
    </script>
</body>
</html>
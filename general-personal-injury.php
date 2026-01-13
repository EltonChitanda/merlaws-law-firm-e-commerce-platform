<?php
/**
 * General Personal Injury Service Page
 */
require_once __DIR__ . '/app/config.php';

$pageTitle = "General Personal Injury | Our Services";
$pageDescription = "Expert personal injury legal representation. Decades of experience securing compensation for injury claims.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <title><?php echo htmlspecialchars($pageTitle); ?> | Med Attorneys</title>
    
    <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/default.css">
    
    <style>
        :root {
            --merlaws-primary: #AC132A;
            --merlaws-primary-dark: #8a0f22;
            --merlaws-secondary: #1a365d;
            --merlaws-gold: #d69e2e;
            --merlaws-gray-50: #f7fafc;
            --merlaws-gray-100: #edf2f7;
            --merlaws-gray-200: #e2e8f0;
            --merlaws-gray-600: #4a5568;
            --merlaws-gray-800: #1a202c;
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            color: var(--merlaws-gray-800);
            line-height: 1.7;
        }

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
        }

        .breadcrumb-modern a:hover {
            color: var(--merlaws-gold);
        }

        .service-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        .content-container {
            max-width: 1200px;
            margin: -3rem auto 4rem;
            padding: 0 1rem;
            position: relative;
            z-index: 10;
        }

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
        }

        .contact-item:hover i {
            color: white;
        }

        .contact-item a {
            color: inherit;
            text-decoration: none;
            font-weight: 600;
        }

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
        }

        @media (max-width: 768px) {
            .service-hero h1 { font-size: 2.5rem; }
            .content-card { padding: 2rem; }
            .specialist-content {
                grid-template-columns: 1fr;
                text-align: center;
            }
            .specialist-image { margin: 0 auto; }
        }
    </style>
</head>
<body>
    <?php 
    $headerPath = __DIR__ . '/include/header.php';
    if (file_exists($headerPath)) echo file_get_contents($headerPath);
    ?>

    <section class="service-hero">
        <div class="container text-center position-relative">
            <div class="breadcrumb-modern">
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
                <i class="fas fa-chevron-right"></i>
                <a href="our-services.html">Our Services</a>
                <i class="fas fa-chevron-right"></i>
                <span>General Personal Injury</span>
            </div>
            <h1>General Personal Injury</h1>
            <p class="lead">Unmatchable track record in winning personal injury claims</p>
        </div>
    </section>

    <div class="content-container">
        <div class="content-card">
            <h2>Decades of Experience in Personal Injury Law</h2>
            
            <p>MED Attorneys has an unmatchable track record in winning personal injury claims. With decades of experience, our dedicated teams are experts in securing compensation - whatever the injury, however complex the claim.</p>

            <p>We exist to defend the rights of the injured or mistreated and we do not act for insurance companies, employers or large corporations. MED is proud to act for those who need us the most, not those who are able to pay us the most.</p>

            <p>Our lawyers are totally committed to securing the best possible outcome for clients in the shortest possible time and to supporting them every step of the way. You can trust us to explain the nature of your claim in clear language and to keep you totally informed from start to finish. No unnecessary jargon, no unpleasant surprises - putting you in control of your own case.</p>

            <div class="text-center">
                <a href="contact-us.php" class="cta-button">
                    Start Your Claim Today
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <div class="specialist-card">
            <div class="specialist-content">
                <div class="specialist-image">
                    <img src="image/kagiso.webp" alt="Kagiso - Personal Injury Specialist">
                </div>
                <div class="specialist-info">
                    <h3>
                        Talk to a Personal Injury Specialist
                        <span>Kagiso - Your Dedicated Advisor</span>
                    </h3>
                    <p>Obtain free initial advice and the opportunity to talk through your experience with Kagiso, our general personal liability specialist.</p>
                    
                    <div class="contact-details">
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <a href="tel:0828855201">082 885 5201</a>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:kagisom@merlaws.com">kagisom@merlaws.com</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php 
    $footerPath = __DIR__ . '/include/footer.html';
    if (file_exists($footerPath)) echo file_get_contents($footerPath);
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
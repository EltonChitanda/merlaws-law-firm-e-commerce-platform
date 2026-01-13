<?php
/**
 * About Us Page
 * about-us.php
 * 
 * Professional page detailing the firm's history, mission, and values.
 */

$page_title = "About Us | MerLaws";
$page_description = "Learn about MerLaws, one of South Africa's most respected law firms. Discover our history, our values, and our commitment to obtaining justice for the injured.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
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
    
    <!-- Custom Styles -->
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
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--merlaws-gray-50) 0%, var(--merlaws-gray-100) 100%);
            color: var(--merlaws-gray-800);
            line-height: 1.6;
        }

        .page-header {
            background: linear-gradient(135deg, var(--merlaws-primary) 0%, var(--merlaws-primary-dark) 50%, var(--merlaws-secondary) 100%);
            color: white;
            padding: 5rem 0 3rem;
            margin-bottom: 3rem;
            position: relative;
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
        }

        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .section-enhanced {
            padding: 3rem 0 5rem;
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

        .about-content {
            background: white;
            padding: 3rem;
            border-radius: 24px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--merlaws-gray-200);
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--merlaws-gray-600);
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .value-card {
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--merlaws-gray-200);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .value-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--merlaws-primary), var(--merlaws-gold));
        }

        .value-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-xl);
        }

        .value-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 25px rgba(172, 19, 42, 0.3);
            color: white;
            font-size: 2.2rem;
        }

        .value-card h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--merlaws-secondary);
            margin-bottom: 1rem;
        }

        .value-card p {
            color: var(--merlaws-gray-600);
            margin-bottom: 1.5rem;
            line-height: 1.7;
            flex-grow: 1;
        }

        .cta-section {
            background: linear-gradient(135deg, var(--merlaws-secondary), var(--merlaws-primary-dark));
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
        }

        .cta-section p {
            font-size: 1.25rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }

        .cta-button {
            display: inline-flex;
            align-items: center;
            background: var(--merlaws-gold);
            color: var(--merlaws-secondary);
            padding: 1.25rem 2.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .cta-button:hover {
            background: #c99029;
            color: white;
            transform: translateY(-3px);
        }

        @media (max-width: 768px) {
            .page-title { font-size: 2.5rem; }
            .section-title { font-size: 2.2rem; }
            .values-grid { grid-template-columns: 1fr; }
            .about-content, .cta-section { padding: 2rem; }
        }
    </style>
</head>
<body>
    <div id="header"></div>

    <div class="page-header">
        <div class="container text-center">
            <h1 class="page-title">About Our Firm</h1>
            <p class="lead" style="opacity: 0.9;">The Distinct Name in Medical Law</p>
        </div>
    </div>

    <section class="section-enhanced">
        <div class="container">
            <div class="about-content mb-5">
                <p><strong>MED Attorneys is one of South Africa's most prolific and respected law firms, with offices throughout the country.</strong></p>
                <p>In addition to our specialist injury lawyers, we operate in a network of emergency responders, medical experts and investigators. These considerable resources, along with our ability to advance litigation costs and our no-win-no-fee services, enable MED Attorneys to hold individuals, corporations and government accountable. And accountability keeps people safe.</p>
            </div>

            <h2 class="section-title">Our Core Values</h2>
            <p class="section-subtitle">These principles guide every action we take and every case we handle.</p>

            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-universal-access"></i></div>
                    <h3>Accessibility</h3>
                    <p>We believe the law should serve everyone. We are here to take your call and give you advice, ensuring you get the answers, respect, and assistance you need during difficult times.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-shield-alt"></i></div>
                    <h3>Protection</h3>
                    <p>Our clients deserve someone to stand up for their rights. We fight for you with uncompromising tenacity and are here to help and guard you on the path to justice.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-user-friends"></i></div>
                    <h3>Personal Attention</h3>
                    <p>By working closely with our clients with compassion and integrity, we are always at your side to help give you peace of mind.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-trophy"></i></div>
                    <h3>Track Record</h3>
                    <p>We take great pride in our innovative approach and our firm's role in cases resulting in landmark decisions. We never give up and have successfully appealed cases to the Constitutional Court.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-handshake"></i></div>
                    <h3>No Win - No Fee</h3>
                    <p>We believe every South African should have access to justice. We offer a flexible fee policy, including a no-win-no-fee arrangement and a free first consultation.</p>
                </div>
            </div>

            <div class="cta-section">
                <h3>Ready to Fight for Your Rights?</h3>
                <p>Contact us today and get the compensation you deserve.</p>
                <a href="contact-us.php" class="cta-button">
                    Contact Us Today <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </section>

    <div id="footer"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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
    </script>
</body>
</html>
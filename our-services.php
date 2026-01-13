<?php
/**
 * Our Services Page
 * Redesigned with modern professional styling
 */

$page_title = "Our Services | Med Attorneys";
$page_description = "Explore our core areas of expertise in medical law, including medical negligence, product liability, motor vehicle accidents, and more.";
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

        .section-subtitle {
            text-align: center;
            font-size: 1.25rem;
            color: #718096;
            max-width: 800px;
            margin: 0 auto 3rem;
        }

        .practice-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .practice-card {
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
            color: var(--merlaws-gray-600);
            margin-bottom: 1.5rem;
            line-height: 1.7;
            flex-grow: 1;
        }

        .card-button {
            margin-top: auto;
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

        @media (max-width: 768px) {
            .page-title {
                font-size: 2.5rem;
            }
            .practice-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div id="header"></div>

    <div class="page-header">
        <div class="container text-center">
            <h1 class="page-title">Our Services</h1>
            <p class="lead" style="opacity: 0.9;">Specialized legal services tailored to your needs</p>
        </div>
    </div>

    <section class="section-enhanced">
        <div class="container">
            <p class="section-subtitle">
                We are compassionate but uncompromising specialist injury lawyers that represent only Plaintiffs. 
                We have proudly built our practice on the belief that we can make a genuine difference in the lives of those who need our help.
            </p>

            <div class="practice-cards">
                <?php
                $services = [
                    [
                        'title' => 'Medical Malpractice',
                        'description' => 'When a medical practitioner deviates from professional standards, the consequences can be devastating. We guarantee specialist investigation and aggressive action to obtain full compensation.',
                        'icon' => 'image/icon-heart.webp',
                        'link' => 'medical-negligence.php'
                    ],
                    [
                        'title' => 'Product Liability',
                        'description' => 'We take on big business and corporations to protect the rights of consumers harmed by defective products, dangerous drugs, or faulty medical devices.',
                        'icon' => 'image/icon-product.webp',
                        'link' => 'product-liability.php'
                    ],
                    [
                        'title' => 'Motor Vehicle Accidents',
                        'description' => 'Our network of experts enables us to thoroughly examine any accident, identify all responsible parties, and ensure you are fully compensated for your injuries.',
                        'icon' => 'image/icon-car.webp',
                        'link' => 'motor-vehicle-accidents.php'
                    ],
                    [
                        'title' => 'Premises Liability',
                        'description' => 'We represent persons across South Africa who have been seriously injured due to dangerous conditions created in private and public places.',
                        'icon' => 'image/icon-premise.webp',
                        'link' => 'premises-liability.php'
                    ],
                    [
                        'title' => 'General Personal Injury',
                        'description' => 'With decades of experience, our dedicated teams are experts in securing compensation for any injury, however complex the claim. We exist to defend the rights of the injured.',
                        'icon' => 'image/icon-track-record.webp',
                        'link' => 'general-personal-injury.php'
                    ]
                ];

                foreach ($services as $service):
                ?>
                <div class="practice-card">
                    <div class="practice-icon">
                        <img src="<?php echo htmlspecialchars($service['icon']); ?>" alt="<?php echo htmlspecialchars($service['title']); ?>">
                    </div>
                    <h3><?php echo htmlspecialchars($service['title']); ?></h3>
                    <p><?php echo htmlspecialchars($service['description']); ?></p>
                    <a href="<?php echo htmlspecialchars($service['link']); ?>" class="card-button">
                        Learn More <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <?php endforeach; ?>
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

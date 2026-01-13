<?php
/**
 * Our Successes Page
 * our-successes.php
 */

require __DIR__ . '/app/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="View our track record of successful cases and settlements - The distinct name in medical law.">
    <title>Our Successes | Med Attorneys</title>
    
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
            --merlaws-secondary: #1a365d;
            --merlaws-gold: #d69e2e;
            --merlaws-success: #38a169;
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

        .page-header-content {
            position: relative;
            z-index: 1;
        }

        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .container-custom {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem 4rem;
        }

        .stats-section {
            background: white;
            border-radius: 24px;
            padding: 3rem;
            box-shadow: var(--shadow-xl);
            margin-bottom: 3rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .stat-item {
            text-align: center;
            padding: 2rem;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--merlaws-gray-50), white);
            border: 2px solid var(--merlaws-gray-200);
            transition: all 0.3s ease;
        }

        .stat-item:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--merlaws-primary);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-gold));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }

        .stat-label {
            color: var(--merlaws-gray-600);
            font-size: 1rem;
            font-weight: 500;
        }

        .year-section {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
            border-left: 5px solid var(--merlaws-primary);
        }

        .year-header {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: var(--merlaws-primary);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .year-header::after {
            content: '';
            flex: 1;
            height: 2px;
            background: linear-gradient(90deg, var(--merlaws-primary), transparent);
        }

        .success-item {
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            background: var(--merlaws-gray-50);
            border-radius: 12px;
            border-left: 4px solid var(--merlaws-success);
            transition: all 0.3s ease;
        }

        .success-item:hover {
            background: white;
            box-shadow: var(--shadow-md);
            transform: translateX(5px);
        }

        .success-date {
            color: var(--merlaws-primary);
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .success-description {
            color: var(--merlaws-gray-600);
            line-height: 1.8;
        }

        .success-amount {
            background: linear-gradient(135deg, var(--merlaws-success), #48bb78);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
        }

        .btn-show-more {
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
            color: white;
            padding: 1rem 2.5rem;
            border-radius: 12px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin: 2rem auto;
        }

        .btn-show-more:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(172, 19, 42, 0.4);
        }

        .hidden-content {
            display: none;
        }

        .cta-quote {
            background: linear-gradient(135deg, var(--merlaws-gray-50), white);
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            box-shadow: var(--shadow-lg);
            margin: 3rem 0;
            border: 2px solid var(--merlaws-gray-200);
        }

        .cta-quote p {
            font-size: 1.25rem;
            font-style: italic;
            color: var(--merlaws-gray-600);
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 2.5rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .container-custom {
                padding: 0 1rem 3rem;
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

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="page-header-content">
                <h1 class="page-title">Our Successes</h1>
                <p class="page-subtitle" style="font-size: 1.25rem; opacity: 0.9; font-weight: 300;">
                    A proven track record of securing justice and maximum compensation for our clients
                </p>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="container-custom">
        <!-- Key Statistics -->
        <div class="stats-section">
            <h2 class="text-center mb-4" style="font-family: 'Playfair Display', serif; font-size: 2.5rem; color: var(--merlaws-gray-800);">
                Our Track Record
            </h2>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">100%</div>
                    <div class="stat-label">Success Rate on Lodged Claims</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">2,178</div>
                    <div class="stat-label">Current Active Clients</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">R1.36M</div>
                    <div class="stat-label">Average Settlement (2023)</div>
                </div>
            </div>
        </div>

        <!-- Recent Successes -->
        <div class="year-section">
            <h3 class="year-header">
                <i class="fas fa-calendar-alt"></i> 2024
            </h3>
            
            <div class="success-item">
                <div class="success-date">
                    <i class="fas fa-check-circle"></i> 16 May 2024
                </div>
                <div class="success-description">
                    A female child aged 11 who sustained severe brain damage and resultant mixed-type cerebral palsy 
                    as a result of the negligence of a hospital's medical and nursing personnel was granted 
                    <span class="success-amount">R20,218,718</span>.
                </div>
            </div>

            <div class="success-item">
                <div class="success-date">
                    <i class="fas fa-check-circle"></i> 30 April 2024
                </div>
                <div class="success-description">
                    A male with severe brain injury from motor vehicle accident was granted 
                    <span class="success-amount">R6,023,169</span>.
                </div>
            </div>

            <div class="success-item">
                <div class="success-date">
                    <i class="fas fa-check-circle"></i> 19 March 2024
                </div>
                <div class="success-description">
                    A minor child with severe brain injury from motor vehicle accident was granted 
                    <span class="success-amount">R5,546,312</span>.
                </div>
            </div>
        </div>

        <div class="year-section">
            <h3 class="year-header">
                <i class="fas fa-calendar-alt"></i> 2023
            </h3>
            
            <div class="success-item">
                <div class="success-date">
                    <i class="fas fa-check-circle"></i> 28 November 2023
                </div>
                <div class="success-description">
                    An adult with severe orthopaedic injuries and mild brain injury from a motor vehicle accident 
                    was granted <span class="success-amount">R4,529,735</span>.
                </div>
            </div>

            <div class="success-item">
                <div class="success-date">
                    <i class="fas fa-check-circle"></i> 3 October 2023
                </div>
                <div class="success-description">
                    A minor female child with a severe brain injury from a motor vehicle accident was granted 
                    <span class="success-amount">R6,016,012</span>.
                </div>
            </div>

            <div class="success-item">
                <div class="success-date">
                    <i class="fas fa-check-circle"></i> 25 August 2023
                </div>
                <div class="success-description">
                    An adult female with a severe brain injury from a motor vehicle accident was granted 
                    <span class="success-amount">R6,753,767</span>.
                </div>
            </div>

            <div class="success-item">
                <div class="success-date">
                    <i class="fas fa-check-circle"></i> 27 February 2023
                </div>
                <div class="success-description">
                    A 24-year-old male, sustained a severe primary diffuse traumatic brain injury - we proceeded 
                    to trial before the Honourable Judge Moorcraft. Judgement was granted in our favour in the 
                    amount of <span class="success-amount">R4,995,541.66</span>.
                </div>
            </div>
        </div>

        <div class="year-section">
            <h3 class="year-header">
                <i class="fas fa-calendar-alt"></i> 2022
            </h3>
            
            <div class="success-item">
                <div class="success-date">
                    <i class="fas fa-check-circle"></i> 25 November 2022
                </div>
                <div class="success-description">
                    A male child aged 9 who sustained severe brain damage and resultant mixed-type cerebral palsy 
                    as a result of medical negligence was granted <span class="success-amount">R24,193,760</span>.
                </div>
            </div>

            <div class="success-item">
                <div class="success-date">
                    <i class="fas fa-check-circle"></i> February 2022
                </div>
                <div class="success-description">
                    The MED team obtained compensation in the amount of <span class="success-amount">R24,911,418</span> 
                    for a 15-year-old Cerebral Palsy Child.
                </div>
            </div>
        </div>

        <!-- Show More Button -->
        <div class="text-center">
            <button id="showMoreBtn" class="btn-show-more" onclick="toggleMoreContent()">
                <span id="btnText">Show More Successes</span>
                <i id="btnIcon" class="fas fa-chevron-down ms-2"></i>
            </a>
        </div>

        <!-- Hidden Content -->
        <div id="moreContent" class="hidden-content">
            <div class="year-section">
                <h3 class="year-header">
                    <i class="fas fa-calendar-alt"></i> 2021
                </h3>
                
                <div class="success-item">
                    <div class="success-date">
                        <i class="fas fa-check-circle"></i> November 2021
                    </div>
                    <div class="success-description">
                        The MED team obtained an amount of <span class="success-amount">R9,420,006.30</span> 
                        for a 25-year-old who sustained traumatic brain injuries.
                    </div>
                </div>

                <div class="success-item">
                    <div class="success-date">
                        <i class="fas fa-check-circle"></i> 11 October 2021
                    </div>
                    <div class="success-description">
                        A 26 year old female sustained a severe primary diffuse traumatic brain injury. 
                        General damages were settled at <span class="success-amount">R1,200,000</span> 
                        and loss of earnings at <span class="success-amount">R7,835,221.50</span>.
                    </div>
                </div>
            </div>

            <div class="year-section">
                <h3 class="year-header">
                    <i class="fas fa-calendar-alt"></i> 2020
                </h3>
                
                <div class="success-item">
                    <div class="success-date">
                        <i class="fas fa-check-circle"></i> November 2020
                    </div>
                    <div class="success-description">
                        The MED team obtained compensation in the amount of <span class="success-amount">R13,082,572</span> 
                        for a 10-year-old Cerebral Palsy Child.
                    </div>
                </div>

                <div class="success-item">
                    <div class="success-date">
                        <i class="fas fa-check-circle"></i> November 2020
                    </div>
                    <div class="success-description">
                        Our specialist attorneys obtained compensation of <span class="success-amount">R5,495,970</span> 
                        for a 45-year-old female who sustained head injuries.
                    </div>
                </div>
            </div>

            <div class="year-section">
                <h3 class="year-header">
                    <i class="fas fa-calendar-alt"></i> 2019
                </h3>
                
                <div class="success-item">
                    <div class="success-date">
                        <i class="fas fa-check-circle"></i> August 2019
                    </div>
                    <div class="success-description">
                        The MED team obtained an amount of <span class="success-amount">R5,262,636.24</span> 
                        for a young gentleman who sustained a severe brain injury in a MVA.
                    </div>
                </div>
            </div>
        </div>

        <!-- Client Quote CTA -->
        <div class="cta-quote">
            <i class="fas fa-quote-left" style="font-size: 2rem; color: var(--merlaws-primary); opacity: 0.3; margin-bottom: 1rem;"></i>
            <p>
                "Every month there is an update by mail and SMS regarding status of case and not ever have we had 
                to call to find where we are with regards to medical appointments and trial date a friendly 
                consultant from your firm has also kept me in the loop in this regard."
            </p>
            <p style="font-weight: 600; color: var(--merlaws-primary); margin-bottom: 1.5rem;">- Edna, Satisfied Client</p>
            <a href="testimonials.php" class="btn-show-more" style="text-decoration: none;">
                Read More Testimonials
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <!-- Final CTA -->
        <div class="stats-section" style="text-align: center;">
            <h3 style="font-family: 'Playfair Display', serif; font-size: 2rem; margin-bottom: 1rem;">
                Ready to Get the Compensation You Deserve?
            </h3>
            <p style="color: var(--merlaws-gray-600); font-size: 1.1rem; margin-bottom: 2rem;">
                Join our successful clients and let us fight for your rights.
            </p>
            <a href="contact-us.php" class="btn-show-more" style="text-decoration: none;">
                <i class="fas fa-phone"></i> Contact Us Today
            </a>
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
    <script>
        function toggleMoreContent() {
            const content = document.getElementById('moreContent');
            const btnText = document.getElementById('btnText');
            const btnIcon = document.getElementById('btnIcon');

            if (content.classList.contains('hidden-content')) {
                content.classList.remove('hidden-content');

                btnText.textContent = 'Show Less';
                btnIcon.classList.remove('fa-chevron-down');
                btnIcon.classList.add('fa-chevron-up');
                content.style.display = 'block';
            } else {
                content.classList.add('hidden-content');
                btnText.textContent = 'Show More Successes';
                btnIcon.classList.remove('fa-chevron-up');
                btnIcon.classList.add('fa-chevron-down');
                content.style.display = 'none';
            }
        }
    </script>

</body>
</html>
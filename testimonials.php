<?php
/**
 * Testimonials Page
 * testimonials.php
 */

require __DIR__ . '/app/config.php';

// Get testimonials from database (if available)
try {
    $pdo = db();
    $stmt = $pdo->query("
        SELECT * FROM testimonials 
        WHERE status = 'approved' 
        ORDER BY created_at DESC
    ");
    $testimonials = $stmt->fetchAll();
} catch (Exception $e) {
    // Fallback to static testimonials if database query fails
    $testimonials = [
        ['name' => 'Alice', 'content' => 'I just want to say many thanks to my attorney and her staff for the way you handled my claim. A thank you is extremely small in comparison to what your firm has done for me. The service at MED is professional and a customer is not just a number in your office. I could speak to any one, at any time when I had questions. Best wishes for you and your business and thank you again.'],
        ['name' => 'Itumeleng', 'content' => 'The professionalism and customer care that you have accorded me has been amazing to say the least. This is my letter to your office to inform you of my gratitude about how well you have handled my matter. All appointments have been honoured on time. Your driver has been very professional when driving me around. I will certainly recommend your offices to my friends, family and colleagues. I thank you so much.'],
        ['name' => 'Mavis', 'content' => 'We would hereby like to thank your firm for the good service that I received from you and the professional way of handling our case. I will surely refer people to you should they need any assistance with a Road accident claim.'],
        ['name' => 'Leonard', 'content' => 'I trust that I find you well. I would like to extend my utmost thanks and appreciation to you and your team. All the pain and suffering did not end up in vain. I do not have many words but to just say keep up the fantastic work. I will 100% recommend anyone to you.'],
        ['name' => 'Caroline', 'content' => 'A word of thanks to you and your team. An excellent ruling and outcome, which is credited to your professionalism and dedication. Whilst I appreciate and understand that there is still a lot of work that needs to take place behind the scenes, I\'m confident all will run smoothly, then we can finally say the matter is closed.'],
        ['name' => 'Alina', 'content' => 'My deepest appreciation to MED Attorneys for their continued support throughout this process. Not only have they treated me with such care but they provided for my baby in every consult with an expert. They have truly made this process easier given my circumstances.'],
        ['name' => 'Nthabeleng', 'content' => 'Please allow me a short but heartfelt thanks for your excellent service and assistance regarding my claim. I can only thank Nicole Davidson and her team for their highly professional work ethic and skills. You have never been anything but helpful and accommodating to the extreme. From the onset of my claim right up to the finest details of my case you have been most helpful and understanding. It has been a real privilege to have been a client of yours.'],
        ['name' => 'Edna', 'content' => 'Every month there is an update by mail and SMS regarding status of case and not ever have we had to call to find where we are with regards to medical appointments and trial date a friendly consultant from your firm has also kept me in the loop in this regard.'],
        ['name' => 'Beauty', 'content' => 'I would once again like to thank you all who have assisted me with my case. Words cannot convey how grateful I am that I chose your Firm to represent me and handle every aspect of my case. I have been treated with compassion and care from my first consultation. Once again THANK YOU']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Read testimonials from our satisfied clients - The distinct name in medical law.">
    <title>Client Testimonials | Med Attorneys</title>
    
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
            --merlaws-gold: #d69e2e;
            --merlaws-gray-50: #f7fafc;
            --merlaws-gray-100: #edf2f7;
            --merlaws-gray-200: #e2e8f0;
            --merlaws-gray-600: #4a5568;
            --merlaws-gray-800: #1a202c;
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
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .page-subtitle {
            font-size: 1.25rem;
            opacity: 0.9;
            font-weight: 300;
        }

        .breadcrumb-nav {
            background: rgba(255, 255, 255, 0.1);
            padding: 1rem;
            border-radius: 12px;
            margin-top: 2rem;
        }

        .breadcrumb-nav a {
            color: white;
            text-decoration: none;
            opacity: 0.8;
            transition: opacity 0.3s ease;
        }

        .breadcrumb-nav a:hover {
            opacity: 1;
        }

        .container-custom {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem 4rem;
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .testimonial-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--merlaws-gray-200);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .testimonial-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--merlaws-primary), var(--merlaws-gold));
        }

        .testimonial-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }

        .testimonial-quote {
            position: relative;
            padding-left: 3rem;
            margin-bottom: 2rem;
        }

        .testimonial-quote::before {
            content: '\f10d';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            left: 0;
            top: -10px;
            font-size: 2.5rem;
            color: var(--merlaws-primary);
            opacity: 0.2;
        }

        .testimonial-content {
            color: var(--merlaws-gray-600);
            font-size: 1rem;
            line-height: 1.8;
            font-style: italic;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 2px solid var(--merlaws-gray-100);
        }

        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-gold));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.25rem;
        }

        .author-details h4 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--merlaws-gray-800);
        }

        .author-details p {
            margin: 0;
            font-size: 0.875rem;
            color: var(--merlaws-gray-600);
        }

        .rating-stars {
            color: var(--merlaws-gold);
            margin-top: 0.5rem;
        }

        .cta-section {
            background: white;
            border-radius: 24px;
            padding: 3rem;
            text-align: center;
            box-shadow: var(--shadow-lg);
            margin-top: 3rem;
        }

        .cta-section h3 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: var(--merlaws-gray-800);
            margin-bottom: 1rem;
        }

        .cta-section p {
            color: var(--merlaws-gray-600);
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
            color: white;
            padding: 1rem 2.5rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-primary-custom:hover {
            background: linear-gradient(135deg, var(--merlaws-primary-dark), var(--merlaws-secondary));
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(172, 19, 42, 0.4);
            color: white;
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 2.5rem;
            }

            .testimonials-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
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
                <h1 class="page-title">Client Testimonials</h1>
                <p class="page-subtitle">Hear from those we've helped achieve justice and compensation</p>
                
                <div class="breadcrumb-nav">
                    <strong>You are here:</strong> 
                    <a href="index.html">Home</a> / 
                    <a href="our-firm.html">About</a> / 
                    <span>Testimonials</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Testimonials Content -->
    <div class="container-custom">
        <div class="text-center mb-5">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 2.5rem; color: var(--merlaws-gray-800); margin-bottom: 1rem;">
                What Our Clients Say
            </h2>
            <p style="color: var(--merlaws-gray-600); font-size: 1.1rem; max-width: 700px; margin: 0 auto;">
                Our clients' success stories and satisfaction are the foundation of our reputation. 
                Read about their experiences working with Med Attorneys.
            </p>
        </div>

        <div class="testimonials-grid">
            <?php foreach ($testimonials as $testimonial): ?>
            <div class="testimonial-card">
                <div class="testimonial-quote">
                    <div class="testimonial-content">
                        <?php echo htmlspecialchars($testimonial['content']); ?>
                    </div>
                </div>
                
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <?php echo strtoupper(substr($testimonial['name'], 0, 1)); ?>
                    </div>
                    <div class="author-details">
                        <h4><?php echo htmlspecialchars($testimonial['name']); ?></h4>
                        <p>Satisfied Client</p>
                        <div class="rating-stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Call to Action -->
        <div class="cta-section">
            <h3>Ready to Get Started?</h3>
            <p>Join hundreds of satisfied clients who have received the compensation they deserve. 
            Contact us today for a free consultation.</p>
            <a href="contact-us.php" class="btn-primary-custom">
                <i class="fas fa-phone"></i> Request Free Consultation
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
</body>
</html>
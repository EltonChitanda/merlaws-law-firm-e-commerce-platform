<?php
// app/cases/create.php
require __DIR__ . '/../config.php';
require __DIR__ . '/../csrf.php';
require_login();

$errors = [];
$pdo = db();

// Require essential profile completion before allowing new case
try {
    $uid = get_user_id();
    $u = $pdo->prepare('SELECT name, email, phone, address, city FROM users WHERE id = ?');
    $u->execute([$uid]);
    $usr = $u->fetch();
    $missing = [];
    if (!$usr || trim((string)$usr['name']) === '') { $missing[] = 'Full Name'; }
    if (!$usr || !filter_var(($usr['email'] ?? ''), FILTER_VALIDATE_EMAIL)) { $missing[] = 'Valid Email'; }
    if (!$usr || trim((string)$usr['phone']) === '') { $missing[] = 'Phone Number'; }
    if (!$usr || trim((string)$usr['address']) === '') { $missing[] = 'Address'; }
    if (!$usr || trim((string)$usr['city']) === '') { $missing[] = 'City'; }
    if ($missing) {
        $errors[] = 'Please complete your profile before opening a case: ' . implode(', ', $missing) . '. You can update your details on the Profile page.';
    }
} catch (Throwable $e) {
    // If profile lookup fails, block with a safe message
    $errors[] = 'We could not verify your profile details. Please update your profile before opening a case.';
}
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$case_type = $_POST['case_type'] ?? 'other';
$priority = $_POST['priority'] ?? 'medium';

if (is_post()) {
    if (!csrf_validate()) {
        $errors[] = 'Invalid security token. Please refresh and try again.';
    }
    
    if ($title === '') {
        $errors[] = 'Case title is required.';
    }
    
    if (!$errors) {
        try {
            $case_id = create_case([
                'user_id' => get_user_id(),
                'title' => $title,
                'description' => $description,
                'case_type' => $case_type,
                'priority' => $priority,
                'status' => 'draft'
            ]);
            
            // Log audit and analytics events
            log_audit_event('create', 'case_created', "Case created: {$title}", [
                'category' => 'case',
                'entity_type' => 'case',
                'entity_id' => $case_id,
                'metadata' => ['case_type' => $case_type, 'priority' => $priority]
            ]);
            
            log_analytics_event('case_created', 'create_case', [
                'category' => 'case',
                'label' => "Case Type: {$case_type}",
                'metadata' => ['case_id' => $case_id, 'priority' => $priority]
            ]);
            
            redirect("view.php?id=$case_id");
        } catch (Exception $e) {
            error_log('Create case error: ' . $e->getMessage());
            $errors[] = 'Failed to create case. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create New Case | Med Attorneys</title>
    
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="../../favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../../favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../../favicon/favicon-16x16.png">
    <link rel="manifest" href="../../favicon/site.webmanifest">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../css/default.css">
    
    <style>
        :root {
            --merlaws-primary: #AC132A;
            --merlaws-primary-dark: #8a0f22;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--merlaws-primary) 0%, var(--merlaws-primary-dark) 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .form-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid #e9ecef;
        }
        
        .form-label {
            font-weight: 500;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--merlaws-primary);
            box-shadow: 0 0 0 0.2rem rgba(172, 19, 42, 0.25);
        }
        
        .btn-merlaws {
            background-color: var(--merlaws-primary);
            border-color: var(--merlaws-primary);
            color: white;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-merlaws:hover {
            background-color: var(--merlaws-primary-dark);
            color: white;
            transform: translateY(-2px);
        }
        
        .btn-outline-secondary {
            border: 2px solid #6c757d;
            color: #6c757d;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
        }
        
        .help-text {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }
        
        .case-type-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .case-type-option {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .case-type-option:hover {
            border-color: var(--merlaws-primary);
            background-color: rgba(172, 19, 42, 0.05);
        }
        
        .case-type-option.selected {
            border-color: var(--merlaws-primary);
            background-color: rgba(172, 19, 42, 0.1);
        }
        
        .case-type-option i {
            font-size: 2rem;
            color: var(--merlaws-primary);
            margin-bottom: 0.5rem;
        }
        
        .case-type-option h6 {
            margin: 0;
            font-weight: 600;
        }
        
        .case-type-option small {
            color: #6c757d;
        }
    </style>
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>
    <!-- Include header -->
    <?php 
    $headerPath = __DIR__ . '/../../include/header.php';
    if (file_exists($headerPath)) {
        echo file_get_contents($headerPath);
    }
    ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0">Create New Case</h1>
                    <p class="mb-0 mt-2">Start a new legal case to manage your legal matters</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="index.php" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left"></i> Back to Cases
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-card">
                    <?php if ($errors): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <form method="post" action="" id="createCaseForm">
                        <?php echo csrf_field(); ?>

                        <!-- Case Title -->
                        <div class="mb-4">
                            <label for="title" class="form-label">Case Title <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="title" 
                                   name="title" 
                                   value="<?php echo e($title); ?>" 
                                   required
                                   placeholder="Enter a descriptive title for your case">
                            <div class="help-text">Choose a clear, descriptive title that summarizes your case</div>
                        </div>

                        <!-- Case Type -->
                        <div class="mb-4">
                            <label class="form-label">Case Type <span class="text-danger">*</span></label>
                            <div class="help-text mb-2">Select the type of legal matter that best describes your case</div>
                            
                            <div class="case-type-grid">
                                <div class="case-type-option <?php echo $case_type === 'medical_negligence' ? 'selected' : ''; ?>" data-value="medical_negligence">
                                    <i class="fas fa-user-md"></i>
                                    <h6>Medical Negligence</h6>
                                    <small>Medical malpractice, misdiagnosis, surgical errors</small>
                                </div>
                                
                                <div class="case-type-option <?php echo $case_type === 'product_liability' ? 'selected' : ''; ?>" data-value="product_liability">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <h6>Product Liability</h6>
                                    <small>Defective products, recalls, injuries from products</small>
                                </div>
                                
                                <div class="case-type-option <?php echo $case_type === 'motor_vehicle' ? 'selected' : ''; ?>" data-value="motor_vehicle">
                                    <i class="fas fa-car-crash"></i>
                                    <h6>Motor Vehicle</h6>
                                    <small>Car accidents, motorcycle accidents, truck accidents</small>
                                </div>
                                
                                <div class="case-type-option <?php echo $case_type === 'premises_liability' ? 'selected' : ''; ?>" data-value="premises_liability">
                                    <i class="fas fa-building"></i>
                                    <h6>Premises Liability</h6>
                                    <small>Slip and fall, property accidents, unsafe conditions</small>
                                </div>
                                
                                <div class="case-type-option <?php echo $case_type === 'general_injury' ? 'selected' : ''; ?>" data-value="general_injury">
                                    <i class="fas fa-band-aid"></i>
                                    <h6>General Injury</h6>
                                    <small>Personal injury, workplace accidents, other injuries</small>
                                </div>
                                
                                <div class="case-type-option <?php echo $case_type === 'other' ? 'selected' : ''; ?>" data-value="other">
                                    <i class="fas fa-question-circle"></i>
                                    <h6>Other</h6>
                                    <small>Other legal matters not listed above</small>
                                </div>
                            </div>
                            
                            <input type="hidden" id="case_type" name="case_type" value="<?php echo e($case_type); ?>">
                        </div>

                        <!-- Priority -->
                        <div class="mb-4">
                            <label for="priority" class="form-label">Priority Level</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="low" <?php echo $priority === 'low' ? 'selected' : ''; ?>>Low Priority</option>
                                <option value="medium" <?php echo $priority === 'medium' ? 'selected' : ''; ?>>Medium Priority</option>
                                <option value="high" <?php echo $priority === 'high' ? 'selected' : ''; ?>>High Priority</option>
                                <option value="urgent" <?php echo $priority === 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                            </select>
                            <div class="help-text">Select the urgency level for this case</div>
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label for="description" class="form-label">Case Description</label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="6"
                                      placeholder="Provide a detailed description of your case, including relevant facts, dates, and circumstances..."><?php echo e($description); ?></textarea>
                            <div class="help-text">Provide as much detail as possible to help us understand your case better</div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-3 justify-content-end">
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-merlaws">
                                <i class="fas fa-save"></i> Create Case
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Include footer -->
    <?php 
    $footerPath = __DIR__ . '/../../include/footer.html';
    if (file_exists($footerPath)) {
        echo file_get_contents($footerPath);
    }
    ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Handle case type selection
        document.querySelectorAll('.case-type-option').forEach(option => {
            option.addEventListener('click', function() {
                // Remove selected class from all options
                document.querySelectorAll('.case-type-option').forEach(opt => opt.classList.remove('selected'));
                
                // Add selected class to clicked option
                this.classList.add('selected');
                
                // Update hidden input
                document.getElementById('case_type').value = this.dataset.value;
            });
        });

        // Form validation
        document.getElementById('createCaseForm').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const caseType = document.getElementById('case_type').value;
            
            if (!title) {
                e.preventDefault();
                alert('Please enter a case title');
                document.getElementById('title').focus();
                return;
            }
            
            if (!caseType) {
                e.preventDefault();
                alert('Please select a case type');
                return;
            }
        });
    </script>
    <script src="../assets/js/mobile-responsive.js"></script>
</body>
</html>
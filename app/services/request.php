<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../csrf.php';
require_login();

$pdo = db();
$user_id = get_user_id();

// Load user's cases
$stmt = $pdo->prepare('SELECT id, title FROM cases WHERE user_id = ? ORDER BY updated_at DESC');
$stmt->execute([$user_id]);
$cases = $stmt->fetchAll();

$services = get_services();

$errors = [];
$success = '';

if (is_post()) {
    if (!csrf_validate()) {
        $errors[] = 'Invalid security token.';
    } else {
        $case_id = (int)($_POST['case_id'] ?? 0);
        $service_id = (int)($_POST['service_id'] ?? 0);
        $notes = trim((string)($_POST['notes'] ?? ''));
        $urgency = (string)($_POST['urgency'] ?? 'standard');
        $consult_date = trim((string)($_POST['consult_date'] ?? ''));
        $consult_time = trim((string)($_POST['consult_time'] ?? ''));
        $action = (string)($_POST['submit_action'] ?? 'cart');
        
        // Validation
        if ($case_id <= 0 || $service_id <= 0) { 
            $errors[] = 'Case and service are required.'; 
        }
        
        if (!$errors) {
            // Ensure case belongs to user and is not draft
            try {
                $c = $pdo->prepare('SELECT user_id, status FROM cases WHERE id = ?');
                $c->execute([$case_id]);
                $caseRow = $c->fetch();
                if (!$caseRow || (int)$caseRow['user_id'] !== $user_id) {
                    $errors[] = 'Invalid case selection.';
                } elseif (in_array($caseRow['status'], ['draft','closed'], true)) {
                    $errors[] = 'Service requests are only allowed for Active cases. Your case is currently ' . str_replace('_',' ', $caseRow['status']) . '. Please wait for admin approval or re-opened status.';
                }
            } catch (Throwable $e) {
                $errors[] = 'Unable to validate case status.';
            }
        }

        if (!$errors) {
            // Check for duplicate truly open requests (only pending/approved)
            try {
                $stmt = $pdo->prepare('
                    SELECT COUNT(*) 
                    FROM service_requests sr
                    JOIN services s ON sr.service_id = s.id
                    WHERE sr.case_id = ? 
                    AND sr.service_id = ? 
                    AND sr.status IN ("pending", "approved")
                ');
                $stmt->execute([$case_id, $service_id]);
                $duplicate_count = $stmt->fetchColumn();
                
                if ($duplicate_count > 0) {
                    // Show specific service name only when we know which it is
                    $svc = $pdo->prepare('SELECT name FROM services WHERE id = ?');
                    $svc->execute([$service_id]);
                    $sv = $svc->fetch();
                    $svcName = $sv ? $sv['name'] : 'this service';
                    $errors[] = "You already have an open request for '$svcName' on this case. Please wait for the existing request to be resolved.";
                }
            } catch (Throwable $e) {
                $errors[] = 'Error checking for existing requests.';
            }
        }
        
        if (!$errors) {
            // Check if service is consultation and validate date/time
            $stmt = $pdo->prepare('SELECT category FROM services WHERE id = ?');
            $stmt->execute([$service_id]);
            $service = $stmt->fetch();
            
            if ($service && $service['category'] === 'consultation') {
                if (empty($consult_date) || empty($consult_time)) {
                    $errors[] = 'Consultation date and time are required for consultation services.';
                } else {
                    // Validate date format (YYYY-MM-DD)
                    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $consult_date)) {
                        $errors[] = 'Invalid consultation date format. Please use YYYY-MM-DD.';
                    }
                    
                    // Validate time format (HH:MM)
                    if (!preg_match('/^\d{2}:\d{2}$/', $consult_time)) {
                        $errors[] = 'Invalid consultation time format. Please use HH:MM.';
                    }
                    
                    // Validate that consultation is not in the past
                    $consult_datetime = $consult_date . ' ' . $consult_time;
                    if (strtotime($consult_datetime) < time()) {
                        $errors[] = 'Consultation date and time cannot be in the past.';
                    }
                }
            }
        }
        
        if (!$errors) {
            try {
                if ($action === 'submit') {
                    $stmt = $pdo->prepare('INSERT INTO service_requests (case_id, service_id, notes, urgency, status, requested_at, created_at) VALUES (?, ?, ?, ?, "pending", NOW(), NOW())');
                    $stmt->execute([$case_id, $service_id, $notes, $urgency]);
                    $request_id = $pdo->lastInsertId();
                    
                    // Get service name for notification
                    $service_stmt = $pdo->prepare('SELECT name FROM services WHERE id = ?');
                    $service_stmt->execute([$service_id]);
                    $service_name = $service_stmt->fetchColumn();
                    
                    // Log audit event
                    log_audit_event('create', 'service_requested', "Service requested: {$service_name}", [
                        'category' => 'service',
                        'entity_type' => 'service_request',
                        'entity_id' => $request_id,
                        'metadata' => [
                            'case_id' => $case_id,
                            'service_id' => $service_id,
                            'urgency' => $urgency
                        ]
                    ]);
                    
                    // Create notification for the user
                    $notif_stmt = $pdo->prepare('INSERT INTO user_notifications (user_id, type, title, message, action_url, is_read, created_at) VALUES (?, "service_request", ?, ?, ?, 0, NOW())');
                    $notif_message = "Your service request for '{$service_name}' has been submitted and is pending review.";
                    $notif_url = "../cases/view.php?id={$case_id}";
                    $notif_stmt->execute([$user_id, "Service Request Submitted", $notif_message, $notif_url]);
                    
                    $success = 'Service request submitted successfully! You will be notified when it is reviewed.';
                } else {
                    $stmt = $pdo->prepare('INSERT INTO service_requests (case_id, service_id, notes, urgency, status, created_at) VALUES (?, ?, ?, ?, "cart", NOW())');
                    $stmt->execute([$case_id, $service_id, $notes, $urgency]);
                    $success = 'Service added to request cart.';
                }
            } catch (Throwable $e) {
                $errors[] = 'Failed to create service request.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Request a Service | Med Attorneys</title>
    <link rel="apple-touch-icon" sizes="180x180" href="../../favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../../favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../../favicon/favicon-16x16.png">
    <link rel="manifest" href="../../favicon/site.webmanifest">
    <link rel="stylesheet" href="../../css/default.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .consultation-fields {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.375rem;
            border-left: 4px solid #0d6efd;
        }
    </style>
</head>
<body>
<?php 
$headerPath = __DIR__ . '/../../include/header.php';
if (file_exists($headerPath)) { echo file_get_contents($headerPath); }
?>
<div class="container my-4" style="max-width: 900px;">
    <h1 class="h3">Request a Service</h1>
    <?php if ($errors): ?><div class="alert alert-danger"><?php echo e(implode(' ', $errors)); ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?php echo e($success); ?></div><?php endif; ?>
    <div class="card">
        <div class="card-body">
            <form method="post" id="serviceRequestForm">
                <?php echo csrf_field(); ?>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Case</label>
                        <select name="case_id" class="form-select" required id="caseSelect">
                            <option value="">Select case</option>
                            <?php foreach ($cases as $c): ?>
                            <option value="<?php echo (int)$c['id']; ?>"><?php echo e($c['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Service</label>
                        <select name="service_id" class="form-select" required id="serviceSelect">
                            <option value="">Select service</option>
                            <?php foreach ($services as $s): ?>
                            <option value="<?php echo (int)$s['id']; ?>" data-category="<?php echo e($s['category']); ?>"><?php echo e($s['name']); ?> (<?php echo e($s['category']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Consultation Date/Time Fields (initially hidden) -->
                    <div class="col-12 consultation-fields" id="consultationFields" style="display: none;">
                        <h6 class="text-primary mb-3"><i class="fas fa-calendar-alt"></i> Consultation Scheduling</h6>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">Consultation Date <span class="text-danger">*</span></label>
                                <input type="date" name="consult_date" class="form-control" id="consultDate" min="<?php echo date('Y-m-d'); ?>">
                                <small class="form-text text-muted">Please select a future date</small>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Consultation Time <span class="text-danger">*</span></label>
                                <input type="time" name="consult_time" class="form-control" id="consultTime">
                                <small class="form-text text-muted">During business hours (9 AM - 5 PM)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="4" placeholder="Describe your request..."></textarea>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Urgency</label>
                        <select name="urgency" class="form-select">
                            <option value="standard">Standard</option>
                            <option value="priority">Priority</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-2 justify-content-end">
                        <button class="btn btn-outline-primary" name="submit_action" value="cart">Add to Cart</button>
                        <button class="btn btn-primary" name="submit_action" value="submit">Submit Request</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
$footerPath = __DIR__ . '/../../include/footer.html';
if (file_exists($footerPath)) { echo file_get_contents($footerPath); }
?>
<script src="../assets/js/mobile-responsive.js"></script>
<script>
    // Show/hide consultation fields based on service selection
    document.getElementById('serviceSelect').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const isConsultation = selectedOption.dataset.category === 'consultation';
        const consultationFields = document.getElementById('consultationFields');
        
        if (isConsultation) {
            consultationFields.style.display = 'block';
            // Add required attribute
            document.getElementById('consultDate').required = true;
            document.getElementById('consultTime').required = true;
        } else {
            consultationFields.style.display = 'none';
            // Remove required attribute
            document.getElementById('consultDate').required = false;
            document.getElementById('consultTime').required = false;
        }
    });

    // Form validation
    document.getElementById('serviceRequestForm').addEventListener('submit', function(e) {
        const serviceSelect = document.getElementById('serviceSelect');
        const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
        const isConsultation = selectedOption.dataset.category === 'consultation';
        
        if (isConsultation) {
            const consultDate = document.getElementById('consultDate').value;
            const consultTime = document.getElementById('consultTime').value;
            
            if (!consultDate || !consultTime) {
                e.preventDefault();
                alert('Consultation date and time are required for consultation services.');
                return false;
            }
            
            // Validate date is not in past
            const selectedDateTime = new Date(consultDate + 'T' + consultTime);
            if (selectedDateTime < new Date()) {
                e.preventDefault();
                alert('Consultation date and time cannot be in the past.');
                return false;
            }
        }
    });
</script>
</body>
</html>
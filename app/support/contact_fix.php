<?php
// app/support/contact_fix.php - Fixed support contact form
require __DIR__ . '/../config.php';
require_login();

$user_id = get_user_id();
$errors = [];
$success = false;

// Handle form submission
if (is_post() && isset($_POST['submit_support'])) {
    $subject = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'medium';
    
    // Validation
    if (empty($subject)) {
        $errors[] = 'Subject is required';
    }
    
    if (empty($description)) {
        $errors[] = 'Description is required';
    }
    
    if (!in_array($priority, ['low', 'medium', 'high', 'urgent'])) {
        $priority = 'medium';
    }
    
    if (!$errors) {
        try {
            $pdo = db();
            $pdo->beginTransaction();
            
            // Insert support ticket
            $stmt = $pdo->prepare("
                INSERT INTO support_tickets (user_id, subject, description, priority, status, created_at) 
                VALUES (?, ?, ?, ?, 'open', NOW())
            ");
            $stmt->execute([$user_id, $subject, $description, $priority]);
            $ticket_id = $pdo->lastInsertId();
            
            // Get IT admin users
            $stmt = $pdo->prepare("
                SELECT id, name, email FROM users 
                WHERE role IN ('it_admin', 'super_admin') AND is_active = 1
            ");
            $stmt->execute();
            $it_admins = $stmt->fetchAll();
            
            // Create notifications for IT admins
            foreach ($it_admins as $admin) {
                $stmt = $pdo->prepare("
                    INSERT INTO user_notifications (user_id, type, title, message, action_url, is_read, created_at) 
                    VALUES (?, 'system', 'New Support Ticket', ?, ?, 0, NOW())
                ");
                $message = "New support ticket #{$ticket_id}: {$subject}";
                $action_url = "/app/admin/support/tickets.php?id={$ticket_id}";
                $stmt->execute([$admin['id'], $message, $action_url]);
            }
            
            // Send email notifications to IT admins
            foreach ($it_admins as $admin) {
                $email_subject = "New Support Ticket #{$ticket_id}";
                $email_body = "
                    <h3>New Support Ticket</h3>
                    <p><strong>Ticket ID:</strong> #{$ticket_id}</p>
                    <p><strong>Subject:</strong> {$subject}</p>
                    <p><strong>Priority:</strong> " . ucfirst($priority) . "</p>
                    <p><strong>User:</strong> " . get_user_name() . "</p>
                    <p><strong>Description:</strong></p>
                    <p>{$description}</p>
                    <p>Please log in to the admin panel to review and respond.</p>
                ";
                
                // Use the email service if available
                if (class_exists('EmailService')) {
                    $email = new EmailService();
                    $email->send($admin['email'], $email_subject, $email_body);
                }
            }
            
            $pdo->commit();
            $success = "Support ticket submitted successfully. Ticket ID: #{$ticket_id}";
            
        } catch (Exception $e) {
            $pdo->rollback();
            $errors[] = "Failed to submit support request: " . $e->getMessage();
        }
    }
}

$page_title = 'Technical Support';
include __DIR__ . '/../include/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-headset me-2"></i>Submit Support Request
                    </h4>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($errors): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <?= csrf_field() ?>
                        
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject *</label>
                            <input type="text" class="form-control" id="subject" name="subject" 
                                   value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="low" <?= ($_POST['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Low</option>
                                <option value="medium" <?= ($_POST['priority'] ?? '') === 'medium' ? 'selected' : '' ?>>Medium</option>
                                <option value="high" <?= ($_POST['priority'] ?? '') === 'high' ? 'selected' : '' ?>>High</option>
                                <option value="urgent" <?= ($_POST['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control" id="description" name="description" rows="6" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" name="submit_support" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Submit Support Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>

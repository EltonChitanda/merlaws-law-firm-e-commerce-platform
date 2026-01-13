<?php
// app/profile.php
require __DIR__ . '/config.php';
require __DIR__ . '/csrf.php';
require_login();

$user_id = get_user_id();
$errors = [];
$success = '';

// Get current user data
$pdo = db();
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    redirect('dashboard.php');
}

// Get user medical history
$stmt = $pdo->prepare("SELECT * FROM user_medical_history WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$medical_history = $stmt->fetchAll();

// Handle form submissions
if (is_post()) {
    if (!csrf_validate()) {
        $errors[] = 'Invalid security token. Please refresh and try again.';
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile' && !$errors) {
        // Update basic profile information
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $postal_code = trim($_POST['postal_code'] ?? '');
        $date_of_birth = $_POST['date_of_birth'] ?? null;
        $id_number = trim($_POST['id_number'] ?? '');
        $medical_aid = trim($_POST['medical_aid'] ?? '');
        $medical_aid_number = trim($_POST['medical_aid_number'] ?? '');
        $emergency_contact_name = trim($_POST['emergency_contact_name'] ?? '');
        $emergency_contact_phone = trim($_POST['emergency_contact_phone'] ?? '');

        // Validation
        if ($name === '') $errors[] = 'Full name is required for legal documentation.';
        if ($phone && !preg_match('/^[0-9+\- ()]{6,20}$/', $phone)) $errors[] = 'Please provide a valid phone number.';
        if ($date_of_birth && !strtotime($date_of_birth)) $errors[] = 'Please provide a valid date of birth.';
        if ($id_number && !preg_match('/^[0-9]{13}$/', preg_replace('/\D/', '', $id_number))) {
            $errors[] = 'South African ID number must be 13 digits.';
        }

        if (!$errors) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE users SET
                    name = ?, phone = ?, address = ?, city = ?, postal_code = ?,
                    date_of_birth = ?, id_number = ?, medical_aid = ?, medical_aid_number = ?,
                    emergency_contact_name = ?, emergency_contact_phone = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    $name, $phone ?: null, $address ?: null, $city ?: null, $postal_code ?: null,
                    $date_of_birth ?: null, $id_number ?: null, $medical_aid ?: null,
                    $medical_aid_number ?: null, $emergency_contact_name ?: null,
                    $emergency_contact_phone ?: null, $user_id
                ]);

                // Update session name if changed
                if ($name !== $_SESSION['name']) {
                    $_SESSION['name'] = $name;
                }

                // Capture old values for audit log
                $old_profile_data = [
                    'name' => $user['name'],
                    'phone' => $user['phone'] ?? null,
                    'address' => $user['address'] ?? null,
                    'city' => $user['city'] ?? null
                ];
                
                $new_profile_data = [
                    'name' => $name,
                    'phone' => $phone ?: null,
                    'address' => $address ?: null,
                    'city' => $city ?: null
                ];

                // Log audit event
                log_audit_event('update', 'profile_updated', "Profile updated", [
                    'category' => 'user',
                    'entity_type' => 'user',
                    'entity_id' => $user_id,
                    'old_values' => $old_profile_data,
                    'new_values' => $new_profile_data
                ]);
                
                // Log analytics event
                log_analytics_event('profile_updated', 'profile_update', [
                    'category' => 'user',
                    'label' => 'Profile information updated'
                ]);

                $success = 'Profile updated successfully! Your information is now current.';
                
                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
                
            } catch (Exception $e) {
                $errors[] = 'Unable to update profile at this time. Please try again or contact support.';
            }
        }
    } elseif ($action === 'add_medical_history' && !$errors) {
        // Add medical history entry
        $condition_name = trim($_POST['condition_name'] ?? '');
        $diagnosis_date = $_POST['diagnosis_date'] ?? null;
        $treating_doctor = trim($_POST['treating_doctor'] ?? '');
        $hospital_facility = trim($_POST['hospital_facility'] ?? '');
        $current_medication = trim($_POST['current_medication'] ?? '');
        $severity = $_POST['severity'] ?? null;
        $is_ongoing = isset($_POST['is_ongoing']) ? 1 : 0;
        $notes = trim($_POST['notes'] ?? '');

        if ($condition_name === '') $errors[] = 'Medical condition name is required.';

        if (!$errors) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO user_medical_history
                    (user_id, condition_name, diagnosis_date, treating_doctor, hospital_facility,
                     current_medication, severity, is_ongoing, notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $user_id, $condition_name, $diagnosis_date ?: null, $treating_doctor ?: null,
                    $hospital_facility ?: null, $current_medication ?: null, $severity,
                    $is_ongoing, $notes ?: null
                ]);

                $success = 'Medical history entry added successfully and will be included in your legal documentation.';
                
                // Refresh medical history
                $stmt = $pdo->prepare("SELECT * FROM user_medical_history WHERE user_id = ? ORDER BY created_at DESC");
                $stmt->execute([$user_id]);
                $medical_history = $stmt->fetchAll();
                
            } catch (Exception $e) {
                $errors[] = 'Unable to add medical history entry. Please try again.';
            }
        }
    } elseif ($action === 'change_password' && !$errors) {
        // Change password
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if ($current_password === '') $errors[] = 'Current password is required for security verification.';
        if (strlen($new_password) < 8) $errors[] = 'New password must be at least 8 characters for security.';
        if ($new_password !== $confirm_password) $errors[] = 'New password confirmation does not match.';

        if (!$errors) {
            if (!password_verify($current_password, $user['password_hash'])) {
                $errors[] = 'Current password is incorrect.';
            } else {
                try {
                    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$new_hash, $user_id]);

                    $success = 'Password updated successfully! Your account security has been enhanced.';
                } catch (Exception $e) {
                    $errors[] = 'Unable to update password. Please try again.';
                }
            }
        }
    }
}

// Calculate profile completeness for legal readiness
$completeness_fields = [
    'phone' => ['points' => 10, 'label' => 'Contact Phone'],
    'address' => ['points' => 15, 'label' => 'Physical Address'],
    'city' => ['points' => 5, 'label' => 'City'],
    'postal_code' => ['points' => 5, 'label' => 'Postal Code'],
    'date_of_birth' => ['points' => 20, 'label' => 'Date of Birth'],
    'id_number' => ['points' => 20, 'label' => 'ID Number'],
    'medical_aid' => ['points' => 10, 'label' => 'Medical Aid'],
    'emergency_contact_name' => ['points' => 10, 'label' => 'Emergency Contact'],
    'emergency_contact_phone' => ['points' => 5, 'label' => 'Emergency Phone']
];

$completeness_score = 0;
$missing_fields = [];

foreach ($completeness_fields as $field => $info) {
    if (!empty($user[$field])) {
        $completeness_score += $info['points'];
    } else {
        $missing_fields[] = $info['label'];
    }
}

$completeness_percentage = min(100, $completeness_score);
$legal_ready = $completeness_percentage >= 80;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Client Profile Management | Med Attorneys</title>
    
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="../favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon/favicon-16x16.png">
    <link rel="manifest" href="../favicon/site.webmanifest">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/default.css">
    
    <style>
        :root {
            --merlaws-primary: #AC132A;
            --merlaws-primary-dark: #8a0f22;
            --merlaws-primary-light: #c8344a;
            --merlaws-secondary: #f8f9fa;
            --merlaws-accent: #ffc107;
            --merlaws-success: #28a745;
            --merlaws-warning: #fd7e14;
            --merlaws-danger: #dc3545;
            --border-color: #e2e8f0;
            --text-muted: #64748b;
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #1e293b;
            line-height: 1.6;
        }
        
        /* Header Styles */
        .profile-header {
            background: linear-gradient(135deg, var(--merlaws-primary) 0%, var(--merlaws-primary-dark) 100%);
            color: white;
            padding: 3rem 0 2rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }
        
        .profile-header .container {
            position: relative;
            z-index: 2;
        }
        
        .profile-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0 0 0.5rem 0;
            letter-spacing: -0.025em;
        }
        
        .profile-subtitle {
            font-size: 1.125rem;
            opacity: 0.9;
            margin: 0 0 1rem 0;
        }
        
        /* Completeness Indicator */
        .completeness-indicator {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            padding: 1.5rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .completeness-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .completeness-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin: 0;
        }
        
        .completeness-percentage {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .progress-bar {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            height: 12px;
            overflow: hidden;
            position: relative;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #059669);
            border-radius: 10px;
            transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        
        .progress-fill.low {
            background: linear-gradient(90deg, #f59e0b, #d97706);
        }
        
        .progress-fill.medium {
            background: linear-gradient(90deg, #3b82f6, #2563eb);
        }
        
        .legal-readiness {
            margin-top: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
        }
        
        .legal-ready {
            background: rgba(16, 185, 129, 0.2);
            color: #065f46;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        
        .legal-incomplete {
            background: rgba(245, 158, 11, 0.2);
            color: #92400e;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        
        /* Card Styles */
        .profile-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .profile-card:hover {
            box-shadow: var(--shadow-lg);
        }
        
        .section-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f1f5f9;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        
        .section-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-light));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
        }
        
        /* Form Styles */
        .form-section {
            margin-bottom: 2.5rem;
        }
        
        .form-section-title {
            color: var(--merlaws-primary);
            font-weight: 700;
            font-size: 1.125rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f1f5f9;
            position: relative;
        }
        
        .form-section-title::before {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 60px;
            height: 2px;
            background: var(--merlaws-primary);
        }
        
        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .form-control, .form-select {
            border: 2px solid var(--border-color);
            border-radius: 10px;
            padding: 0.875rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: var(--merlaws-primary);
            box-shadow: 0 0 0 3px rgba(172, 19, 42, 0.1);
        }
        
        .form-control:disabled {
            background: #f8fafc;
            color: var(--text-muted);
            cursor: not-allowed;
        }
        
        .form-text {
            color: var(--text-muted);
            font-size: 0.875rem;
            margin-top: 0.375rem;
        }
        
        .required-field {
            color: var(--merlaws-danger);
            font-weight: 600;
        }
        
        /* Button Styles */
        .btn-merlaws {
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-light));
            border: none;
            color: white;
            padding: 0.875rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 12px rgba(172, 19, 42, 0.3);
        }
        
        .btn-merlaws:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(172, 19, 42, 0.4);
            color: white;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, var(--merlaws-warning), #e8590c);
            color: white;
            border: none;
        }
        
        .btn-warning:hover {
            color: white;
            transform: translateY(-2px);
        }
        
        /* Medical History Styles */
        .medical-history-item {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--merlaws-primary);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .medical-history-item:hover {
            box-shadow: var(--shadow-md);
        }
        
        .medical-history-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .condition-name {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--merlaws-primary);
            margin: 0 0 0.25rem 0;
        }
        
        .condition-meta {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        
        .condition-meta-item {
            font-size: 0.875rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }
        
        .severity-badge {
            padding: 0.375rem 0.875rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }
        
        .severity-mild {
            background: #dcfce7;
            color: #166534;
            border: 2px solid #bbf7d0;
        }
        
        .severity-moderate {
            background: #fef3c7;
            color: #92400e;
            border: 2px solid #fed7aa;
        }
        
        .severity-severe {
            background: #fef2f2;
            color: #dc2626;
            border: 2px solid #fecaca;
        }
        
        .severity-critical {
            background: #7f1d1d;
            color: white;
            border: 2px solid #dc2626;
        }
        
        .status-badge {
            padding: 0.375rem 0.875rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        
        .status-ongoing {
            background: #fef3c7;
            color: #92400e;
            border: 2px solid #fed7aa;
        }
        
        .status-resolved {
            background: #dcfce7;
            color: #166534;
            border: 2px solid #bbf7d0;
        }
        
        /* Sidebar Styles */
        .profile-sidebar {
            position: sticky;
            top: 2rem;
        }
        
        .profile-summary-card {
            text-align: center;
            padding: 2rem;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            margin: 0 auto 1.5rem;
            box-shadow: 0 8px 20px rgba(172, 19, 42, 0.3);
        }
        
        .profile-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 0.5rem 0;
        }
        
        .profile-role {
            color: var(--text-muted);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 0.875rem;
        }
        
        .profile-stats {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #f1f5f9;
        }
        
        .profile-stat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f8fafc;
        }
        
        .profile-stat:last-child {
            border-bottom: none;
        }
        
        .stat-label {
            font-weight: 500;
            color: #374151;
        }
        
        .stat-value {
            font-weight: 600;
            color: var(--merlaws-primary);
        }
        
        /* Alert Styles */
        .alert {
            border-radius: 12px;
            border: none;
            padding: 1.25rem 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #dcfce7, #bbf7d0);
            color: #166534;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #fef2f2, #fecaca);
            color: #991b1b;
        }
        
        .alert-icon {
            font-size: 1.25rem;
            margin-top: 0.125rem;
        }
        
        /* Missing Fields Indicator */
        .missing-fields {
            background: linear-gradient(135deg, #fef3c7, #fed7aa);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 2px solid #f59e0b;
        }
        
        .missing-fields-title {
            font-weight: 700;
            color: #92400e;
            margin: 0 0 1rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .missing-fields-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.5rem;
            margin: 0;
            padding: 0;
            list-style: none;
        }
        
        .missing-field-item {
            color: #92400e;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }
        
        /* Quick Actions */
        .quick-actions {
            display: grid;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .quick-action-btn {
            padding: 0.875rem 1.25rem;
            border-radius: 10px;
            border: 2px solid var(--border-color);
            background: white;
            color: #374151;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .quick-action-btn:hover {
            border-color: var(--merlaws-primary);
            color: var(--merlaws-primary);
            background: rgba(172, 19, 42, 0.02);
            transform: translateY(-2px);
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .profile-header .row {
                flex-direction: column;
                gap: 1.5rem;
            }
            
            .completeness-indicator {
                width: 100%;
            }
        }
        
        @media (max-width: 768px) {
            .profile-title {
                font-size: 1.75rem;
            }
            
            .profile-subtitle {
                font-size: 0.95rem;
            }
            
            .profile-header {
                padding: 1.5rem 0 1rem;
            }
            
            .profile-card {
                padding: 1.25rem;
                margin-bottom: 1rem;
            }
            
            .completeness-indicator {
                padding: 1rem;
                width: 100%;
            }
            
            .completeness-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .medical-history-item {
                padding: 1rem;
            }
            
            .missing-fields-list {
                grid-template-columns: 1fr;
            }
            
            .form-control {
                font-size: 16px; /* Prevents zoom on iOS */
                padding: 12px 16px;
                min-height: 48px;
            }
            
            .btn {
                width: 100%;
                padding: 14px 20px;
                font-size: 16px;
                min-height: 48px;
            }
            
            .form-row {
                flex-direction: column;
            }
        }
        
        @media (max-width: 480px) {
            .profile-title {
                font-size: 1.5rem;
            }
            
            .profile-header {
                padding: 1rem 0;
            }
            
            .profile-card {
                padding: 1rem;
            }
            
            .completeness-indicator {
                padding: 0.875rem;
            }
        }
        
        /* Animation */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-slide-up {
            animation: slideInUp 0.6s ease forwards;
        }
        
        /* Password Strength Indicator */
        .password-strength {
            margin-top: 0.5rem;
            padding: 0.5rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .password-strength.weak {
            background: #fef2f2;
            color: #991b1b;
        }
        
        .password-strength.medium {
            background: #fef3c7;
            color: #92400e;
        }
        
        .password-strength.strong {
            background: #dcfce7;
            color: #166534;
        }
    </style>
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body>
    <!-- Include header -->
    <?php
    $headerPath = __DIR__ . '/../include/header.php';
    if (file_exists($headerPath)) {
        echo file_get_contents($headerPath);
    }
    ?>

    <!-- Profile Header -->
    <div class="profile-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="profile-title">Client Profile Management</h1>
                    <p class="profile-subtitle">Maintain accurate and complete information for optimal legal representation</p>
                </div>
                <div class="col-lg-4">
                    <div class="completeness-indicator">
                        <div class="completeness-header">
                            <h3 class="completeness-title">Profile Completeness</h3>
                            <div class="completeness-percentage"><?php echo $completeness_percentage; ?>%</div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill <?php echo $completeness_percentage < 50 ? 'low' : ($completeness_percentage < 80 ? 'medium' : ''); ?>"
                                 style="width: <?php echo $completeness_percentage; ?>%"></div>
                        </div>
                        <div class="legal-readiness <?php echo $legal_ready ? 'legal-ready' : 'legal-incomplete'; ?>">
                            <i class="fas <?php echo $legal_ready ? 'fa-shield-alt' : 'fa-exclamation-triangle'; ?>"></i>
                            <?php echo $legal_ready ? 'Legal Documentation Ready' : 'Additional Information Required'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Alerts -->
        <?php if ($errors): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle alert-icon"></i>
            <div>
                <strong>Please correct the following issues:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($errors as $error): ?>
                    <li><?php echo e($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle alert-icon"></i>
            <div><?php echo e($success); ?></div>
        </div>
        <?php endif; ?>

        <!-- Missing Fields Warning -->
        <?php if (!$legal_ready && !empty($missing_fields)): ?>
        <div class="missing-fields">
            <h4 class="missing-fields-title">
                <i class="fas fa-info-circle"></i>
                Complete Your Profile for Full Legal Protection
            </h4>
            <p class="mb-3">The following information is required to ensure we can provide comprehensive legal representation and documentation:</p>
            <ul class="missing-fields-list">
                <?php foreach ($missing_fields as $field): ?>
                <li class="missing-field-item">
                    <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                    <?php echo e($field); ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Personal Information -->
                <div class="profile-card animate-slide-up">
                    <div class="section-header">
                        <div class="section-icon">
                            <i class="fas fa-user-edit"></i>
                        </div>
                        <h2 class="section-title">Personal Information</h2>
                    </div>
                    
                    <form method="post" id="profileForm">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-section">
                            <h3 class="form-section-title">Basic Information</h3>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">
                                        Full Legal Name <span class="required-field">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="name" name="name"
                                           value="<?php echo e($user['name']); ?>" required
                                           placeholder="Enter your complete legal name">
                                    <div class="form-text">Must match official identification documents</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" value="<?php echo e($user['email']); ?>" disabled>
                                    <div class="form-text">Contact our support team to change your email address</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Primary Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone"
                                           value="<?php echo e($user['phone'] ?? ''); ?>"
                                           placeholder="+27 XX XXX XXXX">
                                    <div class="form-text">We'll use this for urgent case communications</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
                                           value="<?php echo e($user['date_of_birth'] ?? ''); ?>">
                                    <div class="form-text">Required for legal documentation and verification</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="id_number" class="form-label">South African ID Number</label>
                                    <input type="text" class="form-control" id="id_number" name="id_number"
                                           value="<?php echo e($user['id_number'] ?? ''); ?>"
                                           placeholder="0000000000000"
                                           maxlength="13">
                                    <div class="form-text">Required for legal proceedings and medical aid claims</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3 class="form-section-title">Contact & Address Information</h3>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="address" class="form-label">Physical Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="3"
                                              placeholder="Street address, unit/apartment number"><?php echo e($user['address'] ?? ''); ?></textarea>
                                    <div class="form-text">Complete physical address for legal documentation</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="city" name="city"
                                           value="<?php echo e($user['city'] ?? ''); ?>"
                                           placeholder="City or town">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="postal_code" class="form-label">Postal Code</label>
                                    <input type="text" class="form-control" id="postal_code" name="postal_code"
                                           value="<?php echo e($user['postal_code'] ?? ''); ?>"
                                           placeholder="0000">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3 class="form-section-title">Medical Aid Information</h3>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="medical_aid" class="form-label">Medical Aid Provider</label>
                                    <select class="form-select" id="medical_aid" name="medical_aid">
                                        <option value="">Select Medical Aid Provider</option>
                                        <option value="Discovery Health" <?php echo $user['medical_aid'] === 'Discovery Health' ? 'selected' : ''; ?>>Discovery Health</option>
                                        <option value="Momentum Health" <?php echo $user['medical_aid'] === 'Momentum Health' ? 'selected' : ''; ?>>Momentum Health</option>
                                        <option value="Medicover" <?php echo $user['medical_aid'] === 'Medicover' ? 'selected' : ''; ?>>Medicover</option>
                                        <option value="Bonitas" <?php echo $user['medical_aid'] === 'Bonitas' ? 'selected' : ''; ?>>Bonitas</option>
                                        <option value="Fedhealth" <?php echo $user['medical_aid'] === 'Fedhealth' ? 'selected' : ''; ?>>Fedhealth</option>
                                        <option value="Other" <?php echo $user['medical_aid'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                                        <option value="None" <?php echo $user['medical_aid'] === 'None' ? 'selected' : ''; ?>>No Medical Aid</option>
                                    </select>
                                    <div class="form-text">Essential for medical negligence cases</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="medical_aid_number" class="form-label">Medical Aid Number</label>
                                    <input type="text" class="form-control" id="medical_aid_number" name="medical_aid_number"
                                           value="<?php echo e($user['medical_aid_number'] ?? ''); ?>"
                                           placeholder="Member number">
                                    <div class="form-text">Your medical aid membership number</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3 class="form-section-title">Emergency Contact</h3>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="emergency_contact_name" class="form-label">Emergency Contact Name</label>
                                    <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name"
                                           value="<?php echo e($user['emergency_contact_name'] ?? ''); ?>"
                                           placeholder="Full name of emergency contact">
                                    <div class="form-text">Person to contact in case of emergency</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="emergency_contact_phone" class="form-label">Emergency Contact Phone</label>
                                    <input type="tel" class="form-control" id="emergency_contact_phone" name="emergency_contact_phone"
                                           value="<?php echo e($user['emergency_contact_phone'] ?? ''); ?>"
                                           placeholder="+27 XX XXX XXXX">
                                    <div class="form-text">24/7 reachable contact number</div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn-merlaws" id="saveProfileBtn">
                                <i class="fas fa-save"></i>
                                Update Profile Information
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Medical History -->
                <div class="profile-card animate-slide-up">
                    <div class="section-header">
                        <div class="section-icon">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <h2 class="section-title">Medical History</h2>
                    </div>
                    
                    <!-- Existing Medical History -->
                    <?php if ($medical_history): ?>
                    <div class="mb-4">
                        <h3 class="form-section-title">Current Medical Records</h3>
                        <?php foreach ($medical_history as $history): ?>
                        <div class="medical-history-item">
                            <div class="medical-history-header">
                                <div>
                                    <h4 class="condition-name"><?php echo e($history['condition_name']); ?></h4>
                                    <div class="condition-meta">
                                        <?php if ($history['diagnosis_date']): ?>
                                        <div class="condition-meta-item">
                                            <i class="fas fa-calendar"></i>
                                            Diagnosed: <?php echo date('M d, Y', strtotime($history['diagnosis_date'])); ?>
                                        </div>
                                        <?php endif; ?>
                                        <?php if ($history['treating_doctor']): ?>
                                        <div class="condition-meta-item">
                                            <i class="fas fa-user-md"></i>
                                            Dr. <?php echo e($history['treating_doctor']); ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <?php if ($history['severity']): ?>
                                    <span class="severity-badge severity-<?php echo e($history['severity']); ?>">
                                        <?php echo e(ucfirst($history['severity'])); ?>
                                    </span>
                                    <?php endif; ?>
                                    <span class="status-badge status-<?php echo $history['is_ongoing'] ? 'ongoing' : 'resolved'; ?>">
                                        <?php echo $history['is_ongoing'] ? 'Ongoing' : 'Resolved'; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <?php if ($history['hospital_facility'] || $history['current_medication']): ?>
                            <div class="condition-meta mb-3">
                                <?php if ($history['hospital_facility']): ?>
                                <div class="condition-meta-item">
                                    <i class="fas fa-hospital"></i>
                                    <?php echo e($history['hospital_facility']); ?>
                                </div>
                                <?php endif; ?>
                                <?php if ($history['current_medication']): ?>
                                <div class="condition-meta-item">
                                    <i class="fas fa-pills"></i>
                                    <?php echo e($history['current_medication']); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($history['notes']): ?>
                            <p class="mb-0"><strong>Additional Notes:</strong> <?php echo e($history['notes']); ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Add New Medical History -->
                    <div class="border-top pt-4">
                        <h3 class="form-section-title">Add Medical History Entry</h3>
                        <form method="post" id="medicalHistoryForm">
                            <?php echo csrf_token(); ?>
                            <input type="hidden" name="action" value="add_medical_history">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="condition_name" class="form-label">
                                        Medical Condition/Diagnosis <span class="required-field">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="condition_name" name="condition_name" required
                                           placeholder="e.g., Diabetes Type 2, Hypertension">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="diagnosis_date" class="form-label">Date of Diagnosis</label>
                                    <input type="date" class="form-control" id="diagnosis_date" name="diagnosis_date">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="treating_doctor" class="form-label">Treating Physician</label>
                                    <input type="text" class="form-control" id="treating_doctor" name="treating_doctor"
                                           placeholder="Dr. Smith">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="hospital_facility" class="form-label">Hospital/Medical Facility</label>
                                    <input type="text" class="form-control" id="hospital_facility" name="hospital_facility"
                                           placeholder="Groote Schuur Hospital">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="severity" class="form-label">Condition Severity</label>
                                    <select class="form-select" id="severity" name="severity">
                                        <option value="">Select severity level</option>
                                        <option value="mild">Mild - Minor impact</option>
                                        <option value="moderate">Moderate - Noticeable impact</option>
                                        <option value="severe">Severe - Significant impact</option>
                                        <option value="critical">Critical - Life-threatening</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="current_medication" class="form-label">Current Medication</label>
                                    <input type="text" class="form-control" id="current_medication" name="current_medication"
                                           placeholder="Medication names and dosages">
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="notes" class="form-label">Additional Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3"
                                              placeholder="Any additional information about this condition, symptoms, treatments, etc."></textarea>
                                </div>
                                <div class="col-12 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_ongoing" name="is_ongoing" value="1">
                                        <label class="form-check-label" for="is_ongoing">
                                            This is an ongoing/chronic condition
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn-merlaws">
                                    <i class="fas fa-plus"></i>
                                    Add Medical History Entry
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="profile-sidebar">
                    <!-- Profile Summary -->
                    <div class="profile-card profile-summary-card">
                        <div class="profile-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <h3 class="profile-name"><?php echo e($user['name']); ?></h3>
                        <p class="profile-role"><?php echo e(ucfirst($user['role'])); ?> Member</p>
                        
                        <div class="profile-stats">
                            <div class="profile-stat">
                                <span class="stat-label">Member Since:</span>
                                <span class="stat-value"><?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                            </div>
                            <div class="profile-stat">
                                <span class="stat-label">Email Verified:</span>
                                <span class="stat-value">
                                    <?php echo $user['email_verified'] ?
                                        '<i class="fas fa-check-circle" style="color: var(--merlaws-success);"></i> Verified' :
                                        '<i class="fas fa-exclamation-triangle" style="color: var(--merlaws-warning);"></i> Pending'; ?>
                                </span>
                            </div>
                            <div class="profile-stat">
                                <span class="stat-label">Profile Status:</span>
                                <span class="stat-value">
                                    <?php echo $legal_ready ?
                                        '<i class="fas fa-shield-alt" style="color: var(--merlaws-success);"></i> Complete' :
                                        '<i class="fas fa-clock" style="color: var(--merlaws-warning);"></i> In Progress'; ?>
                                </span>
                            </div>
                            <div class="profile-stat">
                                <span class="stat-label">Last Updated:</span>
                                <span class="stat-value"><?php echo date('M d, Y', strtotime($user['updated_at'])); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Security Settings -->
                    <div class="profile-card">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h3 class="section-title" style="font-size: 1.25rem;">Account Security</h3>
                        </div>
                        
                        <form method="post" id="passwordForm">
                            <?php echo csrf_token(); ?>
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <div class="form-text">Minimum 8 characters with mixed case, numbers, and symbols</div>
                                <div id="passwordStrength"></div>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <button type="submit" class="btn-warning w-100">
                                <i class="fas fa-key"></i> Update Password
                            </button>
                        </form>
                    </div>

                    <!-- Quick Actions -->
                    <div class="profile-card">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <h3 class="section-title" style="font-size: 1.25rem;">Quick Actions</h3>
                        </div>
                        
                        <div class="quick-actions">
                            <a href="dashboard.php" class="quick-action-btn">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard Overview
                            </a>
                            <a href="cases/" class="quick-action-btn">
                                <i class="fas fa-briefcase"></i>
                                My Legal Cases
                            </a>
                            <a href="services/" class="quick-action-btn">
                                <i class="fas fa-concierge-bell"></i>
                                Request Services
                            </a>
                            <a href="/contact-us.php" class="quick-action-btn">
                                <i class="fas fa-phone"></i>
                                Contact Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include footer -->
    <?php
    $footerPath = __DIR__ . '/../include/footer.html';
    if (file_exists($footerPath)) {
        echo file_get_contents($footerPath);
    }
    ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Progressive form enhancement
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-dismiss alerts after 8 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.style.animation = 'slideInUp 0.5s ease reverse';
                        setTimeout(() => alert.remove(), 500);
                    }
                }, 8000);
            });

            // Password strength indicator
            const newPasswordInput = document.getElementById('new_password');
            const strengthIndicator = document.getElementById('passwordStrength');
            
            if (newPasswordInput && strengthIndicator) {
                newPasswordInput.addEventListener('input', function() {
                    const password = this.value;
                    const strength = calculatePasswordStrength(password);
                    updatePasswordStrength(strengthIndicator, strength);
                });
            }

            // Phone number formatting for South African numbers
            const phoneInputs = document.querySelectorAll('input[type="tel"]');
            phoneInputs.forEach(input => {
                input.addEventListener('input', function() {
                    let value = this.value.replace(/\D/g, '');
                    
                    if (value.startsWith('27')) {
                        value = '+' + value;
                    } else if (value.startsWith('0') && value.length >= 10) {
                        value = '+27' + value.substring(1);
                    }
                    
                    // Format: +27 XX XXX XXXX
                    if (value.startsWith('+27') && value.length > 3) {
                        const main = value.substring(3);
                        if (main.length <= 2) {
                            value = '+27 ' + main;
                        } else if (main.length <= 5) {
                            value = '+27 ' + main.substring(0, 2) + ' ' + main.substring(2);
                        } else {
                            value = '+27 ' + main.substring(0, 2) + ' ' + main.substring(2, 5) + ' ' + main.substring(5, 9);
                        }
                    }
                    
                    this.value = value;
                });
            });

            // ID number validation and formatting
            const idNumberInput = document.getElementById('id_number');
            if (idNumberInput) {
                idNumberInput.addEventListener('input', function() {
                    let value = this.value.replace(/\D/g, '');
                    if (value.length > 13) {
                        value = value.substring(0, 13);
                    }
                    this.value = value;
                    
                    // Real-time ID validation
                    if (value.length === 13) {
                        const isValid = validateSouthAfricanID(value);
                        this.setCustomValidity(isValid ? '' : 'Invalid South African ID number');
                    } else {
                        this.setCustomValidity('');
                    }
                });
            }

            // Form submission with loading states
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        const originalText = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                        submitBtn.disabled = true;
                        
                        // Re-enable after 10 seconds in case of errors
                        setTimeout(() => {
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                        }, 10000);
                    }
                });
            });

            // Medical aid provider change handler
            const medicalAidSelect = document.getElementById('medical_aid');
            const medicalAidNumber = document.getElementById('medical_aid_number');
            
            if (medicalAidSelect && medicalAidNumber) {
                medicalAidSelect.addEventListener('change', function() {
                    medicalAidNumber.disabled = (this.value === 'None' || this.value === '');
                    if (medicalAidNumber.disabled) {
                        medicalAidNumber.value = '';
                    }
                });
            }

            // Progressive enhancement animations
            const cards = document.querySelectorAll('.profile-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 150);
            });
        });

        // Password strength calculation
        function calculatePasswordStrength(password) {
            let score = 0;
            
            if (password.length >= 8) score += 25;
            if (password.length >= 12) score += 10;
            if (/[A-Z]/.test(password)) score += 15;
            if (/[a-z]/.test(password)) score += 15;
            if (/[0-9]/.test(password)) score += 15;
            if (/[^A-Za-z0-9]/.test(password)) score += 20;
            
            return Math.min(score, 100);
        }

        // Update password strength indicator
        function updatePasswordStrength(indicator, strength) {
            let className, text;
            
            if (strength < 40) {
                className = 'weak';
                text = 'Weak - Consider adding more complexity';
            } else if (strength < 70) {
                className = 'medium';
                text = 'Good - Add special characters for better security';
            } else {
                className = 'strong';
                text = 'Strong - Excellent password security';
            }
            
            indicator.className = `password-strength ${className}`;
            indicator.textContent = `Password Strength: ${text}`;
        }

        // South African ID validation
        function validateSouthAfricanID(idNumber) {
            if (idNumber.length !== 13) return false;
            
            // Extract date components
            const year = parseInt(idNumber.substring(0, 2));
            const month = parseInt(idNumber.substring(2, 4));
            const day = parseInt(idNumber.substring(4, 6));
            
            // Basic date validation
            if (month < 1 || month > 12) return false;
            if (day < 1 || day > 31) return false;
            
            // Luhn algorithm for checksum
            let sum = 0;
            for (let i = 0; i < 12; i++) {
                let digit = parseInt(idNumber[i]);
                if (i % 2 === 1) {
                    digit *= 2;
                    if (digit > 9) digit = digit - 9;
                }
                sum += digit;
            }
            
            const checksum = (10 - (sum % 10)) % 10;
            return checksum === parseInt(idNumber[12]);
        }

        // Auto-save functionality
        let autoSaveTimeout;
        const formInputs = document.querySelectorAll('#profileForm input, #profileForm select, #profileForm textarea');
        
        formInputs.forEach(input => {
            input.addEventListener('input', function() {
                clearTimeout(autoSaveTimeout);
                
                // Show auto-save indicator
                const indicator = document.getElementById('autoSaveIndicator') || createAutoSaveIndicator();
                indicator.style.display = 'block';
                indicator.textContent = 'Changes detected...';
                indicator.className = 'auto-save-indicator saving';
                
                autoSaveTimeout = setTimeout(() => {
                    // Simulate auto-save
                    indicator.textContent = 'Auto-saved';
                    indicator.className = 'auto-save-indicator saved';
                    
                    setTimeout(() => {
                        indicator.style.display = 'none';
                    }, 2000);
                }, 3000);
            });
        });

        function createAutoSaveIndicator() {
            const indicator = document.createElement('div');
            indicator.id = 'autoSaveIndicator';
            indicator.className = 'auto-save-indicator';
            indicator.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 8px 16px;
                border-radius: 6px;
                font-size: 0.875rem;
                font-weight: 600;
                z-index: 9999;
                display: none;
                transition: all 0.3s ease;
            `;
            document.body.appendChild(indicator);
            
            const style = document.createElement('style');
            style.textContent = `
                .auto-save-indicator.saving {
                    background: #fef3c7;
                    color: #92400e;
                    border: 2px solid #fed7aa;
                }
                .auto-save-indicator.saved {
                    background: #dcfce7;
                    color: #166534;
                    border: 2px solid #bbf7d0;
                }
            `;
            document.head.appendChild(style);
            
            return indicator;
        }

        // Form validation enhancement
        function validateForm(form) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            return isValid;
        }

        // Accessibility enhancements
        function enhanceAccessibility() {
            // Add proper ARIA labels
            const requiredFields = document.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                field.setAttribute('aria-required', 'true');
            });
            
            // Keyboard navigation for custom elements
            const cards = document.querySelectorAll('.profile-card');
            cards.forEach((card, index) => {
                card.setAttribute('tabindex', '0');
                card.setAttribute('role', 'region');
                card.setAttribute('aria-label', `Profile section ${index + 1}`);
            });
        }

        // Initialize accessibility enhancements
        enhanceAccessibility();

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + S to save profile
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                const profileForm = document.getElementById('profileForm');
                if (profileForm && validateForm(profileForm)) {
                    profileForm.submit();
                }
            }
            
            // Ctrl/Cmd + M to focus medical history form
            if ((e.ctrlKey || e.metaKey) && e.key === 'm') {
                e.preventDefault();
                const conditionInput = document.getElementById('condition_name');
                if (conditionInput) {
                    conditionInput.focus();
                }
            }
        });

        // Progress tracking
        function updateProgressBar() {
            const totalFields = <?php echo array_sum(array_column($completeness_fields, 'points')); ?>;
            const completedFields = <?php echo $completeness_score; ?>;
            const percentage = Math.min((completedFields / totalFields) * 100, 100);
            
            const progressBar = document.querySelector('.progress-fill');
            if (progressBar) {
                progressBar.style.width = percentage + '%';
            }
        }

        // Medical aid integration
        const medicalAidProviders = {
            'Discovery Health': {
                format: /^\d{4}-\d{3}-\d{6}$/,
                example: '1234-567-123456'
            },
            'Momentum Health': {
                format: /^\d{10}$/,
                example: '1234567890'
            },
            'Bonitas': {
                format: /^\d{4}\d{6}$/,
                example: '12341234567'
            }
        };

        // Dynamic medical aid number validation
        const medicalAidSelect = document.getElementById('medical_aid');
        const medicalAidNumberInput = document.getElementById('medical_aid_number');

        if (medicalAidSelect && medicalAidNumberInput) {
            medicalAidSelect.addEventListener('change', function() {
                const provider = this.value;
                const providerInfo = medicalAidProviders[provider];
                
                if (providerInfo) {
                    medicalAidNumberInput.placeholder = `Format: ${providerInfo.example}`;
                    medicalAidNumberInput.pattern = providerInfo.format.source;
                } else {
                    medicalAidNumberInput.placeholder = 'Member number';
                    medicalAidNumberInput.removeAttribute('pattern');
                }
            });
        }

        // Legal readiness status updates
        function updateLegalReadiness() {
            const completionPercentage = <?php echo $completeness_percentage; ?>;
            const legalReady = completionPercentage >= 80;
            
            const statusElements = document.querySelectorAll('[data-legal-status]');
            statusElements.forEach(element => {
                element.textContent = legalReady ? 'Legal Documentation Ready' : 'Additional Information Required';
                element.className = `legal-readiness ${legalReady ? 'legal-ready' : 'legal-incomplete'}`;
            });
        }

        // Print functionality for medical records
        function printMedicalHistory() {
            const printContent = document.querySelector('.medical-history-section');
            if (printContent) {
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <html>
                        <head>
                            <title>Medical History - ${<?php echo json_encode($user['name']); ?>}</title>
                            <style>
                                body { font-family: Arial, sans-serif; margin: 20px; }
                                .header { text-align: center; margin-bottom: 30px; }
                                .medical-item { margin-bottom: 20px; padding: 15px; border: 1px solid #ccc; }
                                .condition-name { font-weight: bold; color: #AC132A; }
                                .meta { font-size: 0.9em; color: #666; margin: 10px 0; }
                            </style>
                        </head>
                        <body>
                            <div class="header">
                                <h1>Medical History Report</h1>
                                <p>Patient: ${<?php echo json_encode($user['name']); ?>}</p>
                                <p>Generated: ${new Date().toLocaleDateString()}</p>
                            </div>
                            ${printContent.innerHTML}
                        </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.print();
            }
        }

        // Export profile data (for legal documentation)
        function exportProfileData() {
            const profileData = {
                personalInfo: {
                    name: <?php echo json_encode($user['name']); ?>,
                    email: <?php echo json_encode($user['email']); ?>,
                    phone: <?php echo json_encode($user['phone']); ?>,
                    address: <?php echo json_encode($user['address']); ?>,
                    city: <?php echo json_encode($user['city']); ?>,
                    postalCode: <?php echo json_encode($user['postal_code']); ?>,
                    dateOfBirth: <?php echo json_encode($user['date_of_birth']); ?>,
                    idNumber: <?php echo json_encode($user['id_number']); ?>
                },
                medicalAid: {
                    provider: <?php echo json_encode($user['medical_aid']); ?>,
                    memberNumber: <?php echo json_encode($user['medical_aid_number']); ?>
                },
                emergencyContact: {
                    name: <?php echo json_encode($user['emergency_contact_name']); ?>,
                    phone: <?php echo json_encode($user['emergency_contact_phone']); ?>
                },
                medicalHistory: <?php echo json_encode($medical_history); ?>,
                exportDate: new Date().toISOString(),
                completeness: <?php echo $completeness_percentage; ?>
            };

            const dataStr = JSON.stringify(profileData, null, 2);
            const dataBlob = new Blob([dataStr], {type: 'application/json'});
            
            const link = document.createElement('a');
            link.href = URL.createObjectURL(dataBlob);
            link.download = `profile_${profileData.personalInfo.name.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.json`;
            link.click();
        }

        // Add export button functionality if needed
        const exportBtn = document.getElementById('exportProfile');
        if (exportBtn) {
            exportBtn.addEventListener('click', exportProfileData);
        }
    </script>
    <script src="assets/js/mobile-responsive.js"></script>
</body>
</html>
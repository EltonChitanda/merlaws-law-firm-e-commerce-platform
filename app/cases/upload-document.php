<?php
// app/cases/upload-document.php
require __DIR__ . '/../config.php';
require __DIR__ . '/../csrf.php';
require_login();

$case_id = (int)($_POST['case_id'] ?? 0);

// Verify case belongs to user (or user is admin)
$case = get_case($case_id, get_user_id());
if (!$case) {
    redirect('../cases/index.php');
}

$errors = [];
$success = false;

if (is_post()) {
    if (!csrf_validate()) {
        $errors[] = 'Invalid security token. Please refresh and try again.';
    }
    
    if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Please select a valid file to upload.';
    }
    
    if (!$errors) {
        $upload_data = [
            'document_type' => $_POST['document_type'] ?? null,
            'description' => trim($_POST['description'] ?? '')
        ];
        
        $result = upload_case_document($case_id, $_FILES['document'], $upload_data);
        
        if ($result['success']) {
            $success = true;
            
            // Log audit event
            log_audit_event('upload', 'document_uploaded', "Document uploaded: {$result['original_filename']}", [
                'category' => 'case',
                'entity_type' => 'case',
                'entity_id' => $case_id,
                'metadata' => [
                    'filename' => $result['original_filename'],
                    'document_id' => $result['document_id'] ?? null,
                    'file_size' => $_FILES['document']['size'] ?? 0
                ]
            ]);
            
            // Redirect back to case view with success message
            $_SESSION['upload_success'] = "Document '{$result['original_filename']}' uploaded successfully!";
            redirect("view.php?id=$case_id");
        } else {
            $errors[] = $result['error'];
        }
    }
}

// If we get here, there was an error - redirect back with error
if ($errors) {
    $_SESSION['upload_errors'] = $errors;
}
redirect("view.php?id=$case_id");
?>
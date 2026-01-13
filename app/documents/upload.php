<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../csrf.php';
require_login();

$case_id = (int)($_POST['case_id'] ?? 0);

$case = get_case($case_id, get_user_id());
if (!$case) {
	redirect('index.php');
}

$errors = [];
$uploaded = 0;
if (!is_post() || !csrf_validate()) {
	$errors[] = 'Invalid request.';
}

if (!$errors) {
	$category = (string)($_POST['category'] ?? '');
	$description = trim((string)($_POST['description'] ?? ''));

	if (!isset($_FILES['document']) || $_FILES['document']['error'] === UPLOAD_ERR_NO_FILE) {
		$errors[] = 'Please select a file to upload.';
	}

	if (!$errors) {
		$file = $_FILES['document'];
		if ($file['error'] === UPLOAD_ERR_OK) {
			$result = upload_case_document($case_id, $file, [
				'document_type' => ($_POST['document_type'] ?? $category) ?: null,
				'description' => $description,
			]);
			if (!empty($result['success'])) {
				$uploaded++;
			} else {
				$errors[] = $result['error'] ?? 'An unknown error occurred during upload.';
			}
		} else {
			$errors[] = get_upload_error_message($file['error']);
		}
		if ($uploaded === 0) {
			$errors[] = 'No files were uploaded.';
		}
	}
}

$_SESSION['upload_errors'] = $errors;
$_SESSION['upload_success'] = $uploaded > 0 ? "File uploaded successfully." : '';
redirect('/app/documents/?case_id=' . $case_id);
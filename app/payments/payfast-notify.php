<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../config/payfast.php';
require __DIR__ . '/../services/PayFastService.php';

// This is the ITN (Instant Transaction Notification) handler
// PayFast will POST data to this URL to notify us of payment status

// Set content type to text/plain for PayFast
header('Content-Type: text/plain');

// Log the raw POST data for debugging
$raw_post_data = file_get_contents('php://input');
error_log("PayFast ITN received: " . $raw_post_data);

// Parse the POST data
parse_str($raw_post_data, $post_data);

// Log the parsed data
error_log("PayFast ITN parsed data: " . json_encode($post_data));

try {
    $payfast_service = new PayFastService();
    
    // Process the ITN
    $result = $payfast_service->processITN($post_data);
    
    if ($result['success']) {
        // Return "OK" to PayFast to acknowledge receipt
        echo "OK";
        error_log("PayFast ITN processed successfully: " . $result['message']);
    } else {
        // Log the error but still return "OK" to prevent PayFast from retrying
        error_log("PayFast ITN processing failed: " . $result['error']);
        echo "OK";
    }
    
} catch (Exception $e) {
    // Log the exception but return "OK" to prevent PayFast from retrying
    error_log("PayFast ITN exception: " . $e->getMessage());
    echo "OK";
}

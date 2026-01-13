<?php
// PayFast Configuration

define('PAYFAST_SANDBOX', true); // Set to false for production

if (PAYFAST_SANDBOX) {
    // --- SANDBOX CREDENTIALS ---
    define('PAYFAST_MERCHANT_ID', '10043506'); // Replace with your Sandbox Merchant ID
    define('PAYFAST_MERCHANT_KEY', 'aroicyo7dfw3g'); // Replace with your Sandbox Merchant Key
    define('PAYFAST_PASSPHRASE', 'giftlekalakala86'); // Optional: Replace with your Sandbox Passphrase
    define('PAYFAST_URL', 'https://sandbox.payfast.co.za/eng/process');
} else {
    // --- PRODUCTION CREDENTIALS ---
    define('PAYFAST_MERCHANT_ID', 'YOUR_LIVE_MERCHANT_ID');
    define('PAYFAST_MERCHANT_KEY', 'YOUR_LIVE_MERCHANT_KEY');
    define('PAYFAST_PASSPHRASE', 'YOUR_LIVE_PASSPHRASE');
    define('PAYFAST_URL', 'https://www.payfast.co.za/eng/process');
}

// Full HTTPS URLs
// Define URLs for PayFast to communicate with your application
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$base_path = '/app/';

define('PAYFAST_RETURN_URL', $protocol . $host . $base_path . 'payment-success.php');
define('PAYFAST_CANCEL_URL', $protocol . $host . $base_path . 'payment-cancel.php');
define('PAYFAST_NOTIFY_URL', $protocol . $host . $base_path . 'payment-notify.php');
?>


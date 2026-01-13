<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../services/InvoiceService.php';
require __DIR__ . '/../services/PayFastService.php';

// Set JSON content type
header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];
$invoice_service = new InvoiceService();
$payfast_service = new PayFastService();

try {
    switch ($method) {
        case 'GET':
            handleGetRequest($invoice_service, $payfast_service);
            break;
        case 'POST':
            handlePostRequest($invoice_service, $payfast_service);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function handleGetRequest($invoice_service, $payfast_service) {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'history':
            $invoice_id = (int)($_GET['invoice_id'] ?? 0);
            if (!$invoice_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invoice ID required']);
                return;
            }
            
            $payments = $invoice_service->getInvoicePayments($invoice_id);
            echo json_encode(['success' => true, 'data' => $payments]);
            break;
            
        case 'payfast-status':
            $pf_payment_id = $_GET['pf_payment_id'] ?? '';
            if (!$pf_payment_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'PayFast payment ID required']);
                return;
            }
            
            $result = $payfast_service->getPaymentStatus($pf_payment_id);
            echo json_encode($result);
            break;
            
        case 'payfast-transactions':
            $invoice_id = (int)($_GET['invoice_id'] ?? 0);
            if (!$invoice_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invoice ID required']);
                return;
            }
            
            $transactions = $payfast_service->getInvoiceTransactions($invoice_id);
            echo json_encode(['success' => true, 'data' => $transactions]);
            break;
            
        case 'payfast-environment':
            $status = $payfast_service->getEnvironmentStatus();
            echo json_encode(['success' => true, 'data' => $status]);
            break;
            
        case 'payfast-test':
            $result = $payfast_service->testConnection();
            echo json_encode($result);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
}

function handlePostRequest($invoice_service, $payfast_service) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'record':
            if (!has_permission('invoice:payment')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Permission denied']);
                return;
            }
            
            $invoice_id = (int)$_POST['invoice_id'];
            $payment_data = [
                'payment_method' => $_POST['payment_method'],
                'amount' => (float)$_POST['amount'],
                'payment_date' => $_POST['payment_date'],
                'transaction_id' => $_POST['transaction_id'] ?? null,
                'reference_number' => $_POST['reference_number'] ?? null,
                'notes' => $_POST['notes'] ?? null
            ];
            
            $result = $invoice_service->recordPayment($invoice_id, $payment_data);
            echo json_encode($result);
            break;
            
        case 'payfast-checkout':
            $invoice_id = (int)$_POST['invoice_id'];
            $result = $payfast_service->generatePaymentData($invoice_id);
            echo json_encode($result);
            break;
            
        case 'payfast-form':
            $invoice_id = (int)$_POST['invoice_id'];
            $form_html = $payfast_service->createPaymentForm($invoice_id);
            echo json_encode(['success' => true, 'data' => $form_html]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
}

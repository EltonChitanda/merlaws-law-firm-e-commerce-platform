<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../services/InvoiceService.php';

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

try {
    switch ($method) {
        case 'GET':
            handleGetRequest($invoice_service);
            break;
        case 'POST':
            handlePostRequest($invoice_service);
            break;
        case 'PUT':
            handlePutRequest($invoice_service);
            break;
        case 'DELETE':
            handleDeleteRequest($invoice_service);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function handleGetRequest($invoice_service) {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'list':
            // Get invoices with filters
            $filters = [
                'status' => $_GET['status'] ?? '',
                'client_id' => $_GET['client_id'] ?? '',
                'date_from' => $_GET['date_from'] ?? '',
                'date_to' => $_GET['date_to'] ?? '',
                'search' => $_GET['search'] ?? '',
                'limit' => (int)($_GET['limit'] ?? 50)
            ];
            
            $invoices = $invoice_service->getInvoices($filters);
            echo json_encode(['success' => true, 'data' => $invoices]);
            break;
            
        case 'view':
            $invoice_id = (int)($_GET['id'] ?? 0);
            if (!$invoice_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invoice ID required']);
                return;
            }
            
            $invoice = $invoice_service->getInvoice($invoice_id);
            if (!$invoice) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Invoice not found']);
                return;
            }
            
            $items = $invoice_service->getInvoiceItems($invoice_id);
            $payments = $invoice_service->getInvoicePayments($invoice_id);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'invoice' => $invoice,
                    'items' => $items,
                    'payments' => $payments
                ]
            ]);
            break;
            
        case 'stats':
            $stats = $invoice_service->getInvoiceStats();
            echo json_encode(['success' => true, 'data' => $stats]);
            break;
            
        case 'clients':
            $clients = $invoice_service->getClients();
            echo json_encode(['success' => true, 'data' => $clients]);
            break;
            
        case 'cases':
            $client_id = (int)($_GET['client_id'] ?? 0);
            $cases = $invoice_service->getCases($client_id);
            echo json_encode(['success' => true, 'data' => $cases]);
            break;
            
        case 'calculate':
            $items = json_decode($_GET['items'] ?? '[]', true);
            $discount_amount = (float)($_GET['discount_amount'] ?? 0);
            $tax_rate = (float)($_GET['tax_rate'] ?? 15);
            
            $totals = $invoice_service->calculateTotals($items, $discount_amount);
            $totals['tax_rate'] = $tax_rate;
            
            echo json_encode(['success' => true, 'data' => $totals]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
}

function handlePostRequest($invoice_service) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            if (!has_permission('invoice:create')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Permission denied']);
                return;
            }
            
            $data = [
                'client_id' => (int)$_POST['client_id'],
                'case_id' => !empty($_POST['case_id']) ? (int)$_POST['case_id'] : null,
                'invoice_date' => $_POST['invoice_date'],
                'due_date' => $_POST['due_date'],
                'tax_rate' => (float)$_POST['tax_rate'],
                'discount_amount' => (float)$_POST['discount_amount'],
                'notes' => $_POST['notes'] ?? null,
                'terms_conditions' => $_POST['terms_conditions'] ?? null,
                'payment_instructions' => $_POST['payment_instructions'] ?? null,
                'items' => json_decode($_POST['items'] ?? '[]', true)
            ];
            
            $result = $invoice_service->createInvoice($data);
            echo json_encode($result);
            break;
            
        case 'send':
            if (!has_permission('invoice:send')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Permission denied']);
                return;
            }
            
            $invoice_id = (int)$_POST['invoice_id'];
            $result = $invoice_service->sendInvoice($invoice_id);
            echo json_encode($result);
            break;
            
        case 'void':
            if (!has_permission('invoice:delete')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Permission denied']);
                return;
            }
            
            $invoice_id = (int)$_POST['invoice_id'];
            $reason = $_POST['reason'] ?? '';
            $result = $invoice_service->voidInvoice($invoice_id, $reason);
            echo json_encode($result);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
}

function handlePutRequest($invoice_service) {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'update':
            if (!has_permission('invoice:edit')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Permission denied']);
                return;
            }
            
            $invoice_id = (int)$input['invoice_id'];
            $data = [
                'client_id' => (int)$input['client_id'],
                'case_id' => !empty($input['case_id']) ? (int)$input['case_id'] : null,
                'invoice_date' => $input['invoice_date'],
                'due_date' => $input['due_date'],
                'tax_rate' => (float)$input['tax_rate'],
                'discount_amount' => (float)$input['discount_amount'],
                'notes' => $input['notes'] ?? null,
                'terms_conditions' => $input['terms_conditions'] ?? null,
                'payment_instructions' => $input['payment_instructions'] ?? null,
                'items' => $input['items'] ?? []
            ];
            
            $result = $invoice_service->updateInvoice($invoice_id, $data);
            echo json_encode($result);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
}

function handleDeleteRequest($invoice_service) {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'delete':
            if (!has_permission('invoice:delete')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Permission denied']);
                return;
            }
            
            $invoice_id = (int)$input['invoice_id'];
            $reason = $input['reason'] ?? '';
            $result = $invoice_service->voidInvoice($invoice_id, $reason);
            echo json_encode($result);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
}

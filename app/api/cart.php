<?php
// app/api/cart.php
require __DIR__ . '/../config.php';
require_login();

header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';
$case_id = (int)($_REQUEST['case_id'] ?? 0);

// Verify case belongs to user
$case = get_case($case_id, get_user_id());
if (!$case) {
    echo json_encode(['success' => false, 'error' => 'Case not found']);
    exit;
}

try {
    switch ($action) {
        case 'add':
            $service_id = (int)($_POST['service_id'] ?? 0);
            $notes = trim($_POST['notes'] ?? '');
            $urgency = $_POST['urgency'] ?? 'standard';
            $consult_date = trim($_POST['consult_date'] ?? '');
            $consult_time = trim($_POST['consult_time'] ?? '');
            
            if (!$service_id) {
                throw new Exception('Service ID is required');
            }
            
            // Check if service requests can be added to this case
            $service_restriction = can_add_service_requests($case);
            if (!$service_restriction['can_add']) {
                throw new Exception($service_restriction['message']);
            }
            
            $success = add_to_cart($case_id, $service_id, [
                'notes' => $notes,
                'urgency' => $urgency,
                'consult_date' => $consult_date,
                'consult_time' => $consult_time
            ]);
            
            if (!$success) {
                throw new Exception('Service is already in your cart');
            }
            
            $cart_items = get_cart_items($case_id);
            
            echo json_encode([
                'success' => true,
                'cart_count' => count($cart_items),
                'message' => 'Service added to cart'
            ]);
            break;
            
        case 'remove':
            $item_id = (int)($_POST['item_id'] ?? 0);
            
            if (!$item_id) {
                throw new Exception('Item ID is required');
            }
            
            $success = remove_from_cart($case_id, $item_id);
            
            if (!$success) {
                throw new Exception('Failed to remove item from cart');
            }
            
            $cart_items = get_cart_items($case_id);
            
            echo json_encode([
                'success' => true,
                'cart_count' => count($cart_items),
                'message' => 'Service removed from cart'
            ]);
            break;
            
        case 'get':
            $cart_items = get_cart_items($case_id);
            
            echo json_encode([
                'success' => true,
                'items' => $cart_items,
                'cart_count' => count($cart_items)
            ]);
            break;
            
        case 'clear':
            $pdo = db();
            $stmt = $pdo->prepare("DELETE FROM service_requests WHERE case_id = ? AND status = 'cart'");
            $success = $stmt->execute([$case_id]);
            
            echo json_encode([
                'success' => $success,
                'cart_count' => 0,
                'message' => 'Cart cleared'
            ]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
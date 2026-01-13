<?php
// InvoiceService - Business logic for invoice management

require_once __DIR__ . '/../config.php';

class InvoiceService {
    private $pdo;
    
    public function __construct() {
        $this->pdo = db();
    }
    
    /**
     * Generate unique invoice number
     * Format: INV-YYYY-MM-####
     */
    public function generateInvoiceNumber(): string {
        $current_month = date('Y-m');
        $prefix = "INV-{$current_month}-";
        
        // Get the last invoice number for this month
        $stmt = $this->pdo->prepare("
            SELECT invoice_number 
            FROM invoices 
            WHERE invoice_number LIKE ? 
            ORDER BY invoice_number DESC 
            LIMIT 1
        ");
        $stmt->execute([$prefix . '%']);
        $last_invoice = $stmt->fetch();
        
        if ($last_invoice) {
            // Extract the number part and increment
            $last_number = (int)substr($last_invoice['invoice_number'], strlen($prefix));
            $next_number = $last_number + 1;
        } else {
            $next_number = 1;
        }
        
        return $prefix . str_pad($next_number, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Calculate invoice totals
     */
    public function calculateTotals(array $items, float $discount_amount = 0): array {
        $subtotal = 0;
        $total_tax = 0;
        
        foreach ($items as $item) {
            $line_total = $item['quantity'] * $item['unit_price'];
            $tax_amount = $line_total * ($item['tax_rate'] / 100);
            
            $subtotal += $line_total;
            $total_tax += $tax_amount;
        }
        
        $total_amount = $subtotal + $total_tax - $discount_amount;
        
        return [
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round($total_tax, 2),
            'discount_amount' => round($discount_amount, 2),
            'total_amount' => round($total_amount, 2)
        ];
    }
    
    /**
     * Create new invoice
     */
    public function createInvoice(array $data): array {
        try {
            $this->pdo->beginTransaction();
            
            // Generate invoice number
            $invoice_number = $this->generateInvoiceNumber();
            
            // Calculate totals
            $totals = $this->calculateTotals($data['items'], $data['discount_amount'] ?? 0);
            
            // Insert invoice
            $stmt = $this->pdo->prepare("
                INSERT INTO invoices (
                    invoice_number, case_id, client_id, invoice_date, due_date,
                    subtotal, tax_rate, tax_amount, discount_amount, total_amount,
                    notes, terms_conditions, payment_instructions, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $invoice_number,
                $data['case_id'] ?? null,
                $data['client_id'],
                $data['invoice_date'],
                $data['due_date'],
                $totals['subtotal'],
                $data['tax_rate'] ?? 15.00,
                $totals['tax_amount'],
                $totals['discount_amount'],
                $totals['total_amount'],
                $data['notes'] ?? null,
                $data['terms_conditions'] ?? null,
                $data['payment_instructions'] ?? null,
                get_user_id()
            ]);
            
            $invoice_id = (int)$this->pdo->lastInsertId();
            
            // Insert invoice items
            foreach ($data['items'] as $index => $item) {
                $this->addInvoiceItem($invoice_id, $item, $index);
            }
            
            $this->pdo->commit();
            
            return [
                'success' => true,
                'invoice_id' => $invoice_id,
                'invoice_number' => $invoice_number
            ];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Add item to invoice
     */
    private function addInvoiceItem(int $invoice_id, array $item, int $sort_order): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO invoice_items (
                invoice_id, description, quantity, unit_price, tax_rate, amount, service_id, sort_order
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $amount = $item['quantity'] * $item['unit_price'];
        
        return $stmt->execute([
            $invoice_id,
            $item['description'],
            $item['quantity'],
            $item['unit_price'],
            $item['tax_rate'] ?? 15.00,
            $amount,
            $item['service_id'] ?? null,
            $sort_order
        ]);
    }
    
    /**
     * Get invoice by ID
     */
    public function getInvoice(int $invoice_id): ?array {
        $stmt = $this->pdo->prepare("
            SELECT i.*, 
                   u.name as client_name, u.email as client_email,
                   c.title as case_title,
                   creator.name as created_by_name
            FROM invoices i
            JOIN users u ON i.client_id = u.id
            LEFT JOIN cases c ON i.case_id = c.id
            JOIN users creator ON i.created_by = creator.id
            WHERE i.id = ?
        ");
        $stmt->execute([$invoice_id]);
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Get invoice items
     */
    public function getInvoiceItems(int $invoice_id): array {
        $stmt = $this->pdo->prepare("
            SELECT ii.*, s.name as service_name
            FROM invoice_items ii
            LEFT JOIN services s ON ii.service_id = s.id
            WHERE ii.invoice_id = ?
            ORDER BY ii.sort_order, ii.id
        ");
        $stmt->execute([$invoice_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get invoice payments
     */
    public function getInvoicePayments(int $invoice_id): array {
        $stmt = $this->pdo->prepare("
            SELECT ip.*, u.name as created_by_name
            FROM invoice_payments ip
            JOIN users u ON ip.created_by = u.id
            WHERE ip.invoice_id = ?
            ORDER BY ip.payment_date DESC, ip.created_at DESC
        ");
        $stmt->execute([$invoice_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Check if invoice can be edited
     */
    public function canEdit(int $invoice_id): bool {
        $invoice = $this->getInvoice($invoice_id);
        if (!$invoice) return false;
        
        return in_array($invoice['status'], ['draft', 'sent']);
    }
    
    /**
     * Update invoice
     */
    public function updateInvoice(int $invoice_id, array $data): array {
        if (!$this->canEdit($invoice_id)) {
            return [
                'success' => false,
                'error' => 'Invoice cannot be edited in its current status'
            ];
        }
        
        try {
            $this->pdo->beginTransaction();
            
            // Calculate totals
            $totals = $this->calculateTotals($data['items'], $data['discount_amount'] ?? 0);
            
            // Update invoice
            $stmt = $this->pdo->prepare("
                UPDATE invoices 
                SET case_id = ?, client_id = ?, invoice_date = ?, due_date = ?,
                    subtotal = ?, tax_rate = ?, tax_amount = ?, discount_amount = ?, total_amount = ?,
                    notes = ?, terms_conditions = ?, payment_instructions = ?, updated_by = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([
                $data['case_id'] ?? null,
                $data['client_id'],
                $data['invoice_date'],
                $data['due_date'],
                $totals['subtotal'],
                $data['tax_rate'] ?? 15.00,
                $totals['tax_amount'],
                $totals['discount_amount'],
                $totals['total_amount'],
                $data['notes'] ?? null,
                $data['terms_conditions'] ?? null,
                $data['payment_instructions'] ?? null,
                get_user_id(),
                $invoice_id
            ]);
            
            // Delete existing items
            $stmt = $this->pdo->prepare("DELETE FROM invoice_items WHERE invoice_id = ?");
            $stmt->execute([$invoice_id]);
            
            // Insert new items
            foreach ($data['items'] as $index => $item) {
                $this->addInvoiceItem($invoice_id, $item, $index);
            }
            
            $this->pdo->commit();
            
            return ['success' => true];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Send invoice to client
     */
    public function sendInvoice(int $invoice_id): array {
        $invoice = $this->getInvoice($invoice_id);
        if (!$invoice) {
            return ['success' => false, 'error' => 'Invoice not found'];
        }
        
        if ($invoice['status'] !== 'draft') {
            return ['success' => false, 'error' => 'Only draft invoices can be sent'];
        }
        
        try {
            // Update invoice status
            $stmt = $this->pdo->prepare("
                UPDATE invoices 
                SET status = 'sent', sent_at = NOW(), updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$invoice_id]);
            
            // TODO: Send email with PDF attachment
            // This would integrate with the email system
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Void invoice
     */
    public function voidInvoice(int $invoice_id, string $reason = null): array {
        $invoice = $this->getInvoice($invoice_id);
        if (!$invoice) {
            return ['success' => false, 'error' => 'Invoice not found'];
        }
        
        if ($invoice['status'] === 'paid') {
            return ['success' => false, 'error' => 'Paid invoices cannot be voided'];
        }
        
        try {
            $stmt = $this->pdo->prepare("
                UPDATE invoices 
                SET status = 'void', notes = CONCAT(IFNULL(notes, ''), '\n\nVoided: ', ?), updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$reason ?: 'No reason provided', $invoice_id]);
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Record payment
     */
    public function recordPayment(int $invoice_id, array $payment_data): array {
        try {
            $this->pdo->beginTransaction();
            
            // Insert payment record
            $stmt = $this->pdo->prepare("
                INSERT INTO invoice_payments (
                    invoice_id, payment_method, amount, payment_date, transaction_id,
                    reference_number, payfast_payment_id, payfast_status, payfast_raw_response,
                    notes, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $invoice_id,
                $payment_data['payment_method'],
                $payment_data['amount'],
                $payment_data['payment_date'],
                $payment_data['transaction_id'] ?? null,
                $payment_data['reference_number'] ?? null,
                $payment_data['payfast_payment_id'] ?? null,
                $payment_data['payfast_status'] ?? null,
                $payment_data['payfast_raw_response'] ?? null,
                $payment_data['notes'] ?? null,
                get_user_id()
            ]);
            
            // Check if invoice is now fully paid
            $this->checkInvoicePaymentStatus($invoice_id);
            
            $this->pdo->commit();
            
            return ['success' => true];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Check and update invoice payment status
     */
    private function checkInvoicePaymentStatus(int $invoice_id): void {
        // Get total payments
        $stmt = $this->pdo->prepare("
            SELECT SUM(amount) as total_paid 
            FROM invoice_payments 
            WHERE invoice_id = ?
        ");
        $stmt->execute([$invoice_id]);
        $total_paid = $stmt->fetchColumn() ?: 0;
        
        // Get invoice total
        $stmt = $this->pdo->prepare("SELECT total_amount FROM invoices WHERE id = ?");
        $stmt->execute([$invoice_id]);
        $invoice_total = $stmt->fetchColumn();
        
        // Update status
        if ($total_paid >= $invoice_total) {
            $stmt = $this->pdo->prepare("
                UPDATE invoices 
                SET status = 'paid', paid_at = NOW(), updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$invoice_id]);
        }
    }
    
    /**
     * Get invoices with filters
     */
    public function getInvoices(array $filters = []): array {
        $sql = "
            SELECT i.*, 
                   u.name as client_name, u.email as client_email,
                   c.title as case_title,
                   creator.name as created_by_name,
                   (SELECT SUM(amount) FROM invoice_payments WHERE invoice_id = i.id) as total_paid
            FROM invoices i
            JOIN users u ON i.client_id = u.id
            LEFT JOIN cases c ON i.case_id = c.id
            JOIN users creator ON i.created_by = creator.id
            WHERE 1=1
        ";
        
        $params = [];
        
        // Apply filters
        if (!empty($filters['status'])) {
            $sql .= " AND i.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['client_id'])) {
            $sql .= " AND i.client_id = ?";
            $params[] = $filters['client_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND i.invoice_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND i.invoice_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (i.invoice_number LIKE ? OR u.name LIKE ? OR c.title LIKE ?)";
            $search_term = '%' . $filters['search'] . '%';
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        $sql .= " ORDER BY i.created_at DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get invoice statistics
     */
    public function getInvoiceStats(): array {
        $stats = [];
        
        // Total outstanding
        $stmt = $this->pdo->query("
            SELECT SUM(total_amount) as total_outstanding
            FROM invoices 
            WHERE status IN ('sent', 'overdue')
        ");
        $stats['total_outstanding'] = $stmt->fetchColumn() ?: 0;
        
        // Overdue amount
        $stmt = $this->pdo->query("
            SELECT SUM(total_amount) as overdue_amount
            FROM invoices 
            WHERE status = 'overdue' OR (status = 'sent' AND due_date < CURDATE())
        ");
        $stats['overdue_amount'] = $stmt->fetchColumn() ?: 0;
        
        // Paid this month
        $stmt = $this->pdo->query("
            SELECT SUM(ip.amount) as paid_this_month
            FROM invoice_payments ip
            JOIN invoices i ON ip.invoice_id = i.id
            WHERE i.status = 'paid' 
            AND MONTH(i.paid_at) = MONTH(CURDATE()) 
            AND YEAR(i.paid_at) = YEAR(CURDATE())
        ");
        $stats['paid_this_month'] = $stmt->fetchColumn() ?: 0;
        
        // Total invoices
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM invoices");
        $stats['total_invoices'] = $stmt->fetchColumn() ?: 0;
        
        return $stats;
    }
    
    /**
     * Get clients for invoice creation
     */
    public function getClients(): array {
        $stmt = $this->pdo->query("
            SELECT id, name, email 
            FROM users 
            WHERE role = 'client' AND is_active = 1 
            ORDER BY name
        ");
        return $stmt->fetchAll();
    }
    
    /**
     * Get cases for invoice creation
     */
    public function getCases(int $client_id = null): array {
        $sql = "
            SELECT c.id, c.title, u.name as client_name
            FROM cases c
            JOIN users u ON c.user_id = u.id
            WHERE c.status IN ('active', 'under_review')
        ";
        
        $params = [];
        if ($client_id) {
            $sql .= " AND c.user_id = ?";
            $params[] = $client_id;
        }
        
        $sql .= " ORDER BY c.title";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

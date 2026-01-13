<?php
// InvoicePDFGenerator - Generate professional PDF invoices

require_once __DIR__ . '/../config.php';

class InvoicePDFGenerator {
    private $pdo;
    
    public function __construct() {
        $this->pdo = db();
    }
    
    /**
     * Generate PDF invoice
     */
    public function generate(int $invoice_id, string $output_path = null): array {
        try {
            $invoice_service = new InvoiceService();
            $invoice = $invoice_service->getInvoice($invoice_id);
            
            if (!$invoice) {
                return ['success' => false, 'error' => 'Invoice not found'];
            }
            
            $items = $invoice_service->getInvoiceItems($invoice_id);
            $payments = $invoice_service->getInvoicePayments($invoice_id);
            
            // Generate PDF content
            $pdf_content = $this->generatePDFContent($invoice, $items, $payments);
            
            // Save to file if path provided
            if ($output_path) {
                file_put_contents($output_path, $pdf_content);
            }
            
            return [
                'success' => true,
                'content' => $pdf_content,
                'filename' => "invoice_{$invoice['invoice_number']}.pdf"
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'PDF generation failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate PDF content using HTML to PDF conversion
     */
    private function generatePDFContent(array $invoice, array $items, array $payments): string {
        $html = $this->generateHTML($invoice, $items, $payments);
        
        // For now, we'll use a simple HTML to PDF approach
        // In production, you might want to use a library like TCPDF or mPDF
        return $this->convertHTMLToPDF($html);
    }
    
    /**
     * Generate HTML content for invoice
     */
    private function generateHTML(array $invoice, array $items, array $payments): string {
        $company_name = "MerLaws Attorneys";
        $company_address = "123 Legal Street, Johannesburg, 2000, South Africa";
        $company_phone = "+27 11 123 4567";
        $company_email = "info@merlaws.com";
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice ' . htmlspecialchars($invoice['invoice_number']) . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #1a1a1a; padding-bottom: 20px; }
        .company-name { font-size: 24px; font-weight: bold; color: #1a1a1a; margin-bottom: 10px; }
        .company-details { font-size: 12px; color: #666; }
        .invoice-details { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .invoice-info { flex: 1; }
        .client-info { flex: 1; text-align: right; }
        .section-title { font-size: 16px; font-weight: bold; color: #1a1a1a; margin-bottom: 10px; }
        .invoice-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .invoice-table th, .invoice-table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .invoice-table th { background-color: #f8f9fa; font-weight: bold; }
        .invoice-table .text-right { text-align: right; }
        .invoice-table .text-center { text-align: center; }
        .totals-section { margin-top: 20px; }
        .totals-table { width: 300px; margin-left: auto; }
        .totals-table td { padding: 8px; border-bottom: 1px solid #ddd; }
        .totals-table .total-row { font-weight: bold; font-size: 16px; background-color: #f8f9fa; }
        .payment-info { margin-top: 30px; padding: 20px; background-color: #f8f9fa; border-radius: 5px; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #ddd; padding-top: 20px; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .status-paid { background-color: #d4edda; color: #155724; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-overdue { background-color: #f8d7da; color: #721c24; }
        .status-draft { background-color: #e2e3e5; color: #383d41; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">' . htmlspecialchars($company_name) . '</div>
        <div class="company-details">
            ' . htmlspecialchars($company_address) . '<br>
            Phone: ' . htmlspecialchars($company_phone) . ' | Email: ' . htmlspecialchars($company_email) . '
        </div>
    </div>
    
    <div class="invoice-details">
        <div class="invoice-info">
            <div class="section-title">Invoice Details</div>
            <strong>Invoice #:</strong> ' . htmlspecialchars($invoice['invoice_number']) . '<br>
            <strong>Date:</strong> ' . date('F j, Y', strtotime($invoice['invoice_date'])) . '<br>
            <strong>Due Date:</strong> ' . date('F j, Y', strtotime($invoice['due_date'])) . '<br>
            <strong>Status:</strong> <span class="status-badge status-' . $invoice['status'] . '">' . ucfirst($invoice['status']) . '</span>
        </div>
        <div class="client-info">
            <div class="section-title">Bill To</div>
            <strong>' . htmlspecialchars($invoice['client_name']) . '</strong><br>
            ' . htmlspecialchars($invoice['client_email']) . '<br>
            ' . ($invoice['case_title'] ? 'Case: ' . htmlspecialchars($invoice['case_title']) : '') . '
        </div>
    </div>
    
    <table class="invoice-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Tax Rate</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>';
        
        foreach ($items as $item) {
            $html .= '
            <tr>
                <td>' . htmlspecialchars($item['description']) . '</td>
                <td class="text-center">' . number_format($item['quantity'], 2) . '</td>
                <td class="text-right">R ' . number_format($item['unit_price'], 2) . '</td>
                <td class="text-right">' . number_format($item['tax_rate'], 1) . '%</td>
                <td class="text-right">R ' . number_format($item['amount'], 2) . '</td>
            </tr>';
        }
        
        $html .= '
        </tbody>
    </table>
    
    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">R ' . number_format($invoice['subtotal'], 2) . '</td>
            </tr>
            <tr>
                <td>Tax (' . number_format($invoice['tax_rate'], 1) . '%):</td>
                <td class="text-right">R ' . number_format($invoice['tax_amount'], 2) . '</td>
            </tr>';
        
        if ($invoice['discount_amount'] > 0) {
            $html .= '
            <tr>
                <td>Discount:</td>
                <td class="text-right">-R ' . number_format($invoice['discount_amount'], 2) . '</td>
            </tr>';
        }
        
        $html .= '
            <tr class="total-row">
                <td>Total:</td>
                <td class="text-right">R ' . number_format($invoice['total_amount'], 2) . '</td>
            </tr>
        </table>
    </div>';
        
        // Payment information
        if (!empty($payments)) {
            $html .= '
    <div class="payment-info">
        <div class="section-title">Payment History</div>
        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Method</th>
                    <th>Amount</th>
                    <th>Reference</th>
                </tr>
            </thead>
            <tbody>';
            
            foreach ($payments as $payment) {
                $html .= '
                <tr>
                    <td>' . date('F j, Y', strtotime($payment['payment_date'])) . '</td>
                    <td>' . ucfirst(str_replace('_', ' ', $payment['payment_method'])) . '</td>
                    <td class="text-right">R ' . number_format($payment['amount'], 2) . '</td>
                    <td>' . htmlspecialchars($payment['reference_number'] ?: $payment['transaction_id'] ?: 'N/A') . '</td>
                </tr>';
            }
            
            $html .= '
            </tbody>
        </table>
    </div>';
        }
        
        // Terms and conditions
        if ($invoice['terms_conditions']) {
            $html .= '
    <div style="margin-top: 30px;">
        <div class="section-title">Terms & Conditions</div>
        <p>' . nl2br(htmlspecialchars($invoice['terms_conditions'])) . '</p>
    </div>';
        }
        
        // Payment instructions
        if ($invoice['payment_instructions']) {
            $html .= '
    <div style="margin-top: 20px;">
        <div class="section-title">Payment Instructions</div>
        <p>' . nl2br(htmlspecialchars($invoice['payment_instructions'])) . '</p>
    </div>';
        }
        
        $html .= '
    <div class="footer">
        <p>Thank you for your business!</p>
        <p>Generated on ' . date('F j, Y \a\t g:i A') . '</p>
    </div>
</body>
</html>';
        
        return $html;
    }
    
    /**
     * Convert HTML to PDF (simple implementation)
     * In production, use a proper PDF library like TCPDF or mPDF
     */
    private function convertHTMLToPDF(string $html): string {
        // This is a placeholder implementation
        // In a real application, you would use a proper PDF library
        
        // For now, we'll return the HTML as-is
        // The browser can print this to PDF
        return $html;
    }
    
    /**
     * Generate PDF using TCPDF (if available)
     */
    private function generateWithTCPDF(array $invoice, array $items, array $payments): string {
        // This would be implemented if TCPDF is available
        // For now, return empty string
        return '';
    }
    
    /**
     * Generate PDF using mPDF (if available)
     */
    private function generateWithMPDF(array $invoice, array $items, array $payments): string {
        // This would be implemented if mPDF is available
        // For now, return empty string
        return '';
    }
    
    /**
     * Get invoice template
     */
    private function getInvoiceTemplate(): ?array {
        $stmt = $this->pdo->query("
            SELECT * FROM invoice_templates 
            WHERE is_default = 1 
            LIMIT 1
        ");
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Download PDF file
     */
    public function downloadPDF(int $invoice_id): void {
        $result = $this->generate($invoice_id);
        
        if (!$result['success']) {
            http_response_code(404);
            echo $result['error'];
            return;
        }
        
        $invoice_service = new InvoiceService();
        $invoice = $invoice_service->getInvoice($invoice_id);
        
        $filename = "invoice_{$invoice['invoice_number']}.pdf";
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($result['content']));
        
        echo $result['content'];
    }
    
    /**
     * Display PDF in browser
     */
    public function displayPDF(int $invoice_id): void {
        $result = $this->generate($invoice_id);
        
        if (!$result['success']) {
            http_response_code(404);
            echo $result['error'];
            return;
        }
        
        $invoice_service = new InvoiceService();
        $invoice = $invoice_service->getInvoice($invoice_id);
        
        $filename = "invoice_{$invoice['invoice_number']}.pdf";
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($result['content']));
        
        echo $result['content'];
    }
    
    /**
     * Save PDF to file
     */
    public function savePDF(int $invoice_id, string $file_path): bool {
        $result = $this->generate($invoice_id, $file_path);
        return $result['success'];
    }
    
    /**
     * Get PDF file path for invoice
     */
    public function getPDFPath(int $invoice_id): string {
        $invoice_service = new InvoiceService();
        $invoice = $invoice_service->getInvoice($invoice_id);
        
        if (!$invoice) {
            return '';
        }
        
        $filename = "invoice_{$invoice['invoice_number']}.pdf";
        $upload_dir = __DIR__ . '/../uploads/invoices/';
        
        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        return $upload_dir . $filename;
    }
}

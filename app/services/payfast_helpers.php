<?php
/**
 * PayFast helper functions – keep them in one place.
 * Include this file wherever you need the functions.
 */

if (!defined('PAYFAST_SANDBOX')) {
    // Fallback – should be defined in config/payfast.php
    define('PAYFAST_SANDBOX', true);
}

/**
 * Build the signature string (PayFast spec)
 */
function generate_payfast_signature(array $data, string $passphrase = ''): string
{
    unset($data['signature']);                     // never include old signature
    $pfParamString = '';
    foreach ($data as $k => $v) {
        if ($v !== '' && $v !== null) {
            $pfParamString .= $k . '=' . urlencode(trim($v)) . '&';
        }
    }
    $pfParamString = substr($pfParamString, 0, -1);

    if ($passphrase !== '') {
        $pfParamString .= '&passphrase=' . urlencode(trim($passphrase));
    }
    return md5($pfParamString);
}

/**
 * Verify a received signature
 */
function validate_payfast_signature(array $data, string $receivedSig, string $passphrase = ''): bool
{
    $calc = generate_payfast_signature($data, $passphrase);
    return hash_equals($calc, $receivedSig);
}

/**
 * Return PayFast endpoint URL
 */
function get_payfast_url(): string
{
    return PAYFAST_SANDBOX
        ? 'https://sandbox.payfast.co.za/eng/process'
        : 'https://www.payfast.co.za/eng/process';
}

/**
 * Get credentials (merchant_id, key, passphrase)
 */
function get_payfast_credentials(): array
{
    return [
        'merchant_id' => PAYFAST_MERCHANT_ID,
        'merchant_key' => PAYFAST_MERCHANT_KEY,
        'passphrase'  => defined('PAYFAST_PASSPHRASE') ? PAYFAST_PASSPHRASE : ''
    ];
}

/**
 * Validate PayFast source IP
 */
function is_valid_payfast_ip(string $ip): bool
{
    $hosts = ['www.payfast.co.za', 'sandbox.payfast.co.za', 'wct.payfast.co.za'];
    $valid = [];
    foreach ($hosts as $h) {
        $ips = gethostbynamel($h);
        if ($ips) $valid = array_merge($valid, $ips);
    }
    return in_array($ip, $valid, true);
}

/**
 * Confirm a transaction via PayFast API (optional but recommended)
 */
function confirm_payfast_payment(string $pf_payment_id): array
{
    $cred = get_payfast_credentials();
    $url  = 'https://api.payfast.co.za/transactions/' . urlencode($pf_payment_id);

    $timestamp = gmdate('Y-m-d\TH:i:s\Z');
    $sigData   = [
        'merchant-id' => $cred['merchant_id'],
        'version'     => 'v1',
        'timestamp'   => $timestamp
    ];
    $signature = generate_payfast_signature($sigData, $cred['passphrase']);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => false,
        CURLOPT_HTTPHEADER     => [
            'merchant-id: ' . $cred['merchant_id'],
            'version: v1',
            'timestamp: ' . $timestamp,
            'signature: ' . $signature
        ],
        CURLOPT_TIMEOUT        => 15
    ]);

    $response = curl_exec($ch);
    $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code !== 200) {
        return ['success' => false, 'error' => "HTTP $code"];
    }
    $json = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['success' => false, 'error' => 'Invalid JSON'];
    }
    return ['success' => true, 'data' => $json];
}

/**
 * Log raw ITN for audit
 */

function log_payfast_transaction(array $data, string $ip): int
{
    $pdo = db();

    // Extract invoice_id from m_payment_id (this is YOUR invoice ID)
    $invoice_id = !empty($data['m_payment_id']) ? (int)$data['m_payment_id'] : null;

    $sql = "INSERT INTO payfast_transactions 
            (i.id, pf_payment_id, raw_data, ip_address, created_at)
            VALUES (?, ?, ?, ?, NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $invoice_id,
        $data['pf_payment_id'] ?? null,
        json_encode($data),
        $ip
    ]);

    return (int)$pdo->lastInsertId();
}

/**
 * Update transaction row
 */
function update_payfast_transaction(int $id, array $updates): void
{
    $pdo = db();
    $sets = [];
    $params = ['id' => $id];
    foreach ($updates as $col => $val) {
        $sets[] = "$col = :$col";
        $params[":$col"] = $val;
    }
    $sql = "UPDATE payfast_transactions SET " . implode(', ', $sets) . " WHERE id = :id";
    $pdo->prepare($sql)->execute($params);
}

/**
 * Get a transaction by PayFast payment ID
 */
function get_payfast_transaction(string $pf_payment_id): ?array
{
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM payfast_transactions WHERE pf_payment_id = ? LIMIT 1");
    $stmt->execute([$pf_payment_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Environment health check (optional)
 */
function get_payfast_environment_status(): array
{
    $cred = get_payfast_credentials();
    $missing = [];
    if (empty($cred['merchant_id'])) $missing[] = 'merchant_id';
    if (empty($cred['merchant_key'])) $missing[] = 'merchant_key';
    return [
        'sandbox' => PAYFAST_SANDBOX,
        'missing' => $missing,
        'urls'    => [
            'process' => get_payfast_url(),
            'return'  => PAYFAST_RETURN_URL,
            'cancel'  => PAYFAST_CANCEL_URL,
            'notify'  => PAYFAST_NOTIFY_URL
        ]
    ];
}
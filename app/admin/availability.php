<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../csrf.php';
require_permission('appointment:view');

$simpleXlsxPath = __DIR__ . '/../lib/SimpleXLSX.php';
if (file_exists($simpleXlsxPath)) {
    require_once $simpleXlsxPath;
}

$errors = [];
$success = '';
$infoMessages = [];
$skippedRows = [];

$storageDir = __DIR__ . '/../../storage/availability/';
if (!is_dir($storageDir)) {
    @mkdir($storageDir, 0755, true);
}

$userId = get_user_id();
$filePath = $storageDir . 'availability_' . $userId . '.json';
$supportsExcel = class_exists('SimpleXLSX');

function loadAvailability(string $filePath, int $userId): array
{
    if (!file_exists($filePath)) {
        return ['user_id' => $userId, 'entries' => []];
    }

    $raw = @file_get_contents($filePath);
    if ($raw === false) {
        return ['user_id' => $userId, 'entries' => []];
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded) || !isset($decoded['entries']) || !is_array($decoded['entries'])) {
        return ['user_id' => $userId, 'entries' => []];
    }

    if (!isset($decoded['user_id'])) {
        $decoded['user_id'] = $userId;
    }

    return $decoded;
}

function saveAvailability(string $filePath, int $userId, array $entries): void
{
    usort($entries, function ($a, $b) {
        return strtotime($a['start_at'] ?? '') <=> strtotime($b['start_at'] ?? '');
    });

    $payload = [
        'user_id'    => $userId,
        'updated_at' => date('c'),
        'entries'    => array_values($entries),
    ];

    file_put_contents($filePath, json_encode($payload, JSON_PRETTY_PRINT));
}

function normalizeDateValue(?string $value): string
{
    $value = trim((string)$value);
    if ($value === '') {
        return '';
    }

    $patterns = [
        'Y-m-d\TH:i',
        'Y-m-d\TH:i:s',
        'Y-m-d H:i:s',
        'Y-m-d H:i',
        'd/m/Y H:i',
        'd/m/Y',
    ];

    foreach ($patterns as $pattern) {
        $dt = DateTime::createFromFormat($pattern, $value);
        if ($dt instanceof DateTime) {
            return $dt->format('Y-m-d H:i:s');
        }
    }

    $timestamp = strtotime($value);
    if ($timestamp !== false) {
        return date('Y-m-d H:i:s', $timestamp);
    }

    return '';
}

function normalizeEntry(array $row): array
{
    $entry = [
        'start_at'        => normalizeDateValue($row['start_at'] ?? ''),
        'end_at'          => normalizeDateValue($row['end_at'] ?? ''),
        'timezone'        => trim((string)($row['timezone'] ?? '')),
        'location'        => trim((string)($row['location'] ?? '')),
        'recurrence_rule' => trim((string)($row['recurrence_rule'] ?? '')),
        'block_reason'    => trim((string)($row['block_reason'] ?? '')),
    ];

    if (isset($row['source'])) {
        $entry['source'] = trim((string)$row['source']);
    }

    return $entry;
}

function validateEntry(array $entry): ?string
{
    if ($entry['start_at'] === '') {
        return 'Start time is required.';
    }
    if ($entry['end_at'] === '') {
        return 'End time is required.';
    }
    if ($entry['timezone'] === '') {
        return 'Timezone is required.';
    }

    $startTs = strtotime($entry['start_at']);
    $endTs   = strtotime($entry['end_at']);

    if ($startTs === false || $endTs === false) {
        return 'Unable to parse the provided dates.';
    }
    if ($endTs <= $startTs) {
        return 'End time must be after the start time.';
    }

    return null;
}

function parseCsvSchedule(string $tmpPath): array
{
    $entries = [];
    $skipped = [];

    $handle = @fopen($tmpPath, 'r');
    if ($handle === false) {
        throw new RuntimeException('Unable to open the CSV file.');
    }

    $headerRow = fgetcsv($handle);
    if (!$headerRow) {
        fclose($handle);
        throw new RuntimeException('The CSV file does not contain a header row.');
    }

    $headerMap = [];
    foreach ($headerRow as $index => $column) {
        $headerMap[strtolower(trim((string)$column))] = $index;
    }

    $required = ['start_at', 'end_at', 'timezone'];
    foreach ($required as $column) {
        if (!array_key_exists($column, $headerMap)) {
            fclose($handle);
            throw new RuntimeException('Missing required column: ' . $column);
        }
    }

    $line = 1;
    while (($row = fgetcsv($handle)) !== false) {
        $line++;
        $raw = [];
        foreach ($headerMap as $column => $index) {
            $raw[$column] = $row[$index] ?? '';
        }

        $entry = normalizeEntry($raw);
        $error = validateEntry($entry);
        if ($error) {
            $skipped[] = "Row {$line}: {$error}";
            continue;
        }

        $entry['source'] = $raw['source'] ?? 'CSV upload';
        $entries[] = $entry;
    }

    fclose($handle);

    return ['entries' => $entries, 'skipped' => $skipped];
}

function parseXlsxSchedule(string $tmpPath): array
{
    if (!class_exists('SimpleXLSX')) {
        throw new RuntimeException('Excel support is not available on this server.');
    }

    $xlsx = SimpleXLSX::parse($tmpPath);
    if (!$xlsx) {
        throw new RuntimeException(SimpleXLSX::parseError() ?: 'Unable to read the Excel file.');
    }

    $rows = $xlsx->rows();
    if (count($rows) < 2) {
        throw new RuntimeException('The Excel file does not contain any data rows.');
    }

    $headerRow = array_shift($rows);
    $headerMap = [];
    foreach ($headerRow as $index => $column) {
        $headerMap[strtolower(trim((string)$column))] = $index;
    }

    $required = ['start_at', 'end_at', 'timezone'];
    foreach ($required as $column) {
        if (!array_key_exists($column, $headerMap)) {
            throw new RuntimeException('Missing required column: ' . $column);
        }
    }

    $entries = [];
    $skipped = [];
    $line = 1;
    foreach ($rows as $row) {
        $line++;

        $hasContent = false;
        foreach ($headerMap as $index) {
            if (!empty($row[$index])) {
                $hasContent = true;
                break;
            }
        }
        if (!$hasContent) {
            continue;
        }

        $raw = [];
        foreach ($headerMap as $column => $index) {
            $raw[$column] = $row[$index] ?? '';
        }

        $entry = normalizeEntry($raw);
        $error = validateEntry($entry);
        if ($error) {
            $skipped[] = "Row {$line}: {$error}";
            continue;
        }

        $entry['source'] = $raw['source'] ?? 'Excel upload';
        $entries[] = $entry;
    }

    return ['entries' => $entries, 'skipped' => $skipped];
}

function parseScheduleUpload(array $file): array
{
    $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));

    if ($extension === 'csv') {
        return parseCsvSchedule($file['tmp_name']);
    }

    if ($extension === 'xlsx') {
        return parseXlsxSchedule($file['tmp_name']);
    }

    throw new RuntimeException('Unsupported file type. Please upload a CSV or Excel (.xlsx) file.');
}

function formatDisplayDate(?string $value): string
{
    if (!$value) {
        return '—';
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return $value;
    }

    return date('D, d M Y · H:i', $timestamp);
}

function formatInputValue(?string $value): string
{
    if (!$value) {
        return '';
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return '';
    }

    return date('Y-m-d\TH:i', $timestamp);
}

$availabilityData = loadAvailability($filePath, $userId);

if (is_post()) {
    if (!csrf_validate()) {
        $errors[] = 'Invalid security token.';
    } else {
        $action = $_POST['action'] ?? 'upload_schedule';
        $currentEntries = $availabilityData['entries'] ?? [];

        try {
            switch ($action) {
                case 'upload_schedule':
                    if (empty($_FILES['schedule_file']['tmp_name']) || !is_uploaded_file($_FILES['schedule_file']['tmp_name'])) {
                        throw new RuntimeException('Please choose a file to upload.');
                    }

                    $result = parseScheduleUpload($_FILES['schedule_file']);
                    if (empty($result['entries'])) {
                        throw new RuntimeException('No valid availability entries were found in the uploaded file.');
                    }

                    saveAvailability($filePath, $userId, $result['entries']);
                    $availabilityData = loadAvailability($filePath, $userId);
                    $success = 'Availability updated successfully. Imported ' . count($result['entries']) . ' entry' . (count($result['entries']) === 1 ? '' : 'ies') . '.';

                    $skippedRows = $result['skipped'];
                    if (!empty($skippedRows)) {
                        $infoMessages[] = count($skippedRows) . ' row' . (count($skippedRows) === 1 ? '' : 's') . ' were skipped due to validation issues.';
                    }
                    break;

                case 'update_entry':
                    $index = isset($_POST['entry_index']) ? (int)$_POST['entry_index'] : -1;
                    if (!isset($currentEntries[$index])) {
                        throw new RuntimeException('The selected availability entry could not be found.');
                    }

                    $payload = [
                        'start_at'        => $_POST['start_at'] ?? '',
                        'end_at'          => $_POST['end_at'] ?? '',
                        'timezone'        => $_POST['timezone'] ?? '',
                        'location'        => $_POST['location'] ?? '',
                        'recurrence_rule' => $_POST['recurrence_rule'] ?? '',
                        'block_reason'    => $_POST['block_reason'] ?? '',
                    ];

                    $entry = normalizeEntry($payload);
                    $error = validateEntry($entry);
                    if ($error) {
                        throw new RuntimeException($error);
                    }

                    $entry['source'] = 'Manual edit';
                    $currentEntries[$index] = $entry;

                    saveAvailability($filePath, $userId, $currentEntries);
                    $availabilityData = loadAvailability($filePath, $userId);
                    $success = 'Availability entry updated successfully.';
                    break;

                case 'delete_entry':
                    $index = isset($_POST['entry_index']) ? (int)$_POST['entry_index'] : -1;
                    if (!isset($currentEntries[$index])) {
                        throw new RuntimeException('The selected availability entry no longer exists.');
                    }

                    array_splice($currentEntries, $index, 1);
                    saveAvailability($filePath, $userId, $currentEntries);
                    $availabilityData = loadAvailability($filePath, $userId);
                    $success = 'Availability entry removed.';
                    break;

                case 'add_entry':
                    $payload = [
                        'start_at'        => $_POST['start_at'] ?? '',
                        'end_at'          => $_POST['end_at'] ?? '',
                        'timezone'        => $_POST['timezone'] ?? '',
                        'location'        => $_POST['location'] ?? '',
                        'recurrence_rule' => $_POST['recurrence_rule'] ?? '',
                        'block_reason'    => $_POST['block_reason'] ?? '',
                    ];

                    $entry = normalizeEntry($payload);
                    $error = validateEntry($entry);
                    if ($error) {
                        throw new RuntimeException($error);
                    }

                    $entry['source'] = 'Manual entry';
                    $currentEntries[] = $entry;

                    saveAvailability($filePath, $userId, $currentEntries);
                    $availabilityData = loadAvailability($filePath, $userId);
                    $success = 'Availability entry added successfully.';
                    break;

                case 'clear_schedule':
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    $availabilityData = ['user_id' => $userId, 'entries' => []];
                    $success = 'All availability data has been cleared.';
                    break;

                default:
                    throw new RuntimeException('Unsupported action requested.');
            }
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }
    }
}

$entries = $availabilityData['entries'] ?? [];
$entryCount = count($entries);
$lastUpdated = $availabilityData['updated_at'] ?? null;

$now = time();
$nextWeek = strtotime('+7 days', $now);
$upcomingWindows = 0;
$distinctTimezones = [];
$earliestStart = null;
$latestEnd = null;

foreach ($entries as $entry) {
    $startTs = strtotime($entry['start_at'] ?? '');
    $endTs   = strtotime($entry['end_at'] ?? '');

    if ($startTs !== false && $endTs !== false) {
        if ($earliestStart === null || $startTs < $earliestStart) {
            $earliestStart = $startTs;
        }
        if ($latestEnd === null || $endTs > $latestEnd) {
            $latestEnd = $endTs;
        }
        if ($startTs >= $now && $startTs <= $nextWeek) {
            $upcomingWindows++;
        }
    }

    if (!empty($entry['timezone'])) {
        $distinctTimezones[$entry['timezone']] = true;
    }
}

$distinctTimezoneCount = count($distinctTimezones);

$commonTimezones = [
    'Africa/Johannesburg',
    'UTC',
    'Europe/London',
    'America/New_York',
    'America/Los_Angeles',
    'Europe/Berlin',
    'Asia/Dubai',
    'Asia/Singapore',
    'Australia/Sydney',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Attorney Availability | Med Attorneys Admin</title>
    <link rel="apple-touch-icon" sizes="180x180" href="../../favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../../favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../../favicon/favicon-16x16.png">
    <link rel="manifest" href="../../favicon/site.webmanifest">
    <link rel="stylesheet" href="../../css/default.css">
    <style>
        :root {
            --primary: #AC132A;
            --primary-dark: #8a0f22;
            --primary-light: #c91c37;
            --accent: #c9a96e;
            --text-primary: #1a1a1a;
            --text-secondary: #666666;
            --text-muted: #999999;
            --bg-primary: #ffffff;
            --bg-secondary: #f8f9fa;
            --bg-tertiary: #f3f4f6;
            --border: #e5e7eb;
            --border-light: #f0f0f0;
            --success: #059669;
            --success-bg: #d1fae5;
            --error: #dc2626;
            --error-bg: #fee2e2;
            --info: #2563eb;
            --info-bg: #dbeafe;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', sans-serif;
            background: var(--bg-secondary);
            color: var(--text-primary);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 100%;
            width: 100%;
            margin: 0 auto;
            padding: 32px 24px 80px;
        }

        /* Header Section */
        .page-header {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            border-radius: 16px;
            padding: 48px;
            color: white;
            margin-bottom: 40px;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(172, 19, 42, 0.15) 0%, transparent 70%);
            pointer-events: none;
        }

        .header-content {
            position: relative;
            z-index: 1;
        }

        .page-title {
            font-size: 2.25rem;
            font-weight: 700;
            margin-bottom: 12px;
            letter-spacing: -0.02em;
        }

        .page-subtitle {
            font-size: 1.125rem;
            color: rgba(255, 255, 255, 0.8);
            max-width: 720px;
            line-height: 1.7;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
            margin-top: 32px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 24px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .stat-label {
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 8px;
            font-weight: 600;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .stat-hint {
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.6);
        }

        /* Alert Messages */
        .alerts {
            margin-bottom: 32px;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 16px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            box-shadow: var(--shadow-sm);
        }

        .alert-success {
            background: var(--success-bg);
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        .alert-error {
            background: var(--error-bg);
            color: var(--error);
            border-left: 4px solid var(--error);
        }

        .alert-info {
            background: var(--info-bg);
            color: var(--info);
            border-left: 4px solid var(--info);
        }

        .alert-icon {
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .alert-content {
            flex: 1;
        }

        .alert-title {
            font-weight: 700;
            margin-bottom: 4px;
        }

        .alert-list {
            margin-top: 8px;
            padding-left: 20px;
        }

        .alert-list li {
            margin-bottom: 4px;
        }

        /* Main Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        .content-grid.wide {
            grid-template-columns: 1fr;
        }

        .content-grid.wide .card {
            max-width: 100%;
        }

        /* Card Styles */
        .card {
            background: var(--bg-primary);
            border-radius: 16px;
            padding: 40px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-light);
            width: 100%;
            max-width: 100%;
        }

        .card-header {
            margin-bottom: 32px;
        }

        .card-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--text-primary);
        }

        .card-description {
            font-size: 1rem;
            color: var(--text-secondary);
            line-height: 1.7;
        }

        /* Upload Section */
        .upload-zone {
            border: 2px dashed var(--border);
            border-radius: 12px;
            padding: 56px 40px;
            text-align: center;
            background: var(--bg-tertiary);
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            box-sizing: border-box;
        }

        .upload-zone:hover,
        .upload-zone.dragover {
            border-color: var(--primary);
            background: var(--bg-primary);
        }

        .upload-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(172, 19, 42, 0.1), rgba(172, 19, 42, 0.05));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }

        .upload-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text-primary);
        }

        .upload-subtitle {
            font-size: 1rem;
            color: var(--text-secondary);
            margin-bottom: 18px;
        }

        .upload-filename {
            font-size: 0.9375rem;
            color: var(--primary);
            font-weight: 600;
            margin-top: 12px;
        }

        input[type="file"] {
            display: none;
        }

        /* Guidelines Section */
        .guidelines {
            background: var(--bg-tertiary);
            border-radius: 12px;
            padding: 40px;
            width: 100%;
        }

        .guidelines-list {
            list-style: none;
            padding: 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            width: 100%;
        }

        @media (min-width: 1200px) {
            .guidelines-list {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .guidelines-list li {
            padding: 24px;
            border: 1px solid var(--border-light);
            border-radius: 8px;
            background: var(--bg-primary);
            font-size: 1rem;
            line-height: 1.7;
            transition: all 0.2s ease;
            width: 100%;
        }

        .guidelines-list li:hover {
            border-color: var(--primary);
            box-shadow: 0 2px 8px rgba(172, 19, 42, 0.1);
        }

        .guidelines-list strong {
            color: var(--primary);
            font-size: 1.05rem;
        }

        /* Form Elements */
        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 24px;
            gap: 16px;
            flex-wrap: wrap;
        }

        .form-hint {
            font-size: 0.875rem;
            color: var(--text-muted);
            flex: 1;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 4px 12px rgba(172, 19, 42, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(172, 19, 42, 0.4);
        }

        .btn-secondary {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background: var(--bg-secondary);
        }

        .btn-danger {
            background: rgba(220, 38, 38, 0.1);
            color: var(--error);
            border: 1px solid rgba(220, 38, 38, 0.3);
        }

        .btn-danger:hover {
            background: rgba(220, 38, 38, 0.15);
        }

        /* Table Section */
        .table-section {
            margin-top: 40px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            gap: 16px;
            flex-wrap: wrap;
        }

        .section-title {
            font-size: 1.75rem;
            font-weight: 700;
        }

        .table-wrapper {
            background: var(--bg-primary);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-light);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: var(--bg-tertiary);
        }

        th {
            padding: 16px;
            text-align: left;
            font-size: 0.8125rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            border-bottom: 2px solid var(--border);
        }

        td {
            padding: 20px 16px;
            border-bottom: 1px solid var(--border-light);
            font-size: 0.9375rem;
        }

        tbody tr {
            transition: background 0.2s ease;
        }

        tbody tr:hover {
            background: var(--bg-secondary);
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .table-datetime {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .datetime-primary {
            font-weight: 600;
            color: var(--text-primary);
        }

        .datetime-secondary {
            font-size: 0.8125rem;
            color: var(--text-muted);
        }

        .table-actions {
            display: flex;
            gap: 8px;
        }

        .btn-sm {
            padding: 8px 14px;
            font-size: 0.875rem;
            border-radius: 8px;
        }

        .btn-edit {
            background: rgba(37, 99, 235, 0.1);
            color: #2563eb;
            border: 1px solid rgba(37, 99, 235, 0.2);
        }

        .btn-edit:hover {
            background: rgba(37, 99, 235, 0.2);
        }

        .btn-delete {
            background: rgba(220, 38, 38, 0.1);
            color: var(--error);
            border: 1px solid rgba(220, 38, 38, 0.2);
        }

        .btn-delete:hover {
            background: rgba(220, 38, 38, 0.2);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 32px;
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 24px;
            opacity: 0.3;
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--text-primary);
        }

        .empty-description {
            font-size: 1rem;
            color: var(--text-secondary);
            max-width: 480px;
            margin: 0 auto 32px;
            line-height: 1.6;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal.active {
            display: flex;
        }

        .modal-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        .modal-content {
            position: relative;
            background: var(--bg-primary);
            border-radius: 16px;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: var(--shadow-xl);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 28px 32px 20px;
            border-bottom: 1px solid var(--border-light);
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.75rem;
            color: var(--text-muted);
            cursor: pointer;
            line-height: 1;
            padding: 4px;
        }

        .modal-close:hover {
            color: var(--text-primary);
        }

        .modal-body {
            padding: 32px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-field.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .form-input,
        .form-textarea {
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 0.9375rem;
            font-family: inherit;
            transition: all 0.2s ease;
        }

        .form-input:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(172, 19, 42, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding: 20px 32px 28px;
            border-top: 1px solid var(--border-light);
        }

        /* Utilities */
        .visually-hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            border: 0;
        }

        .text-muted {
            color: var(--text-muted);
        }

        .link-button {
            background: none;
            border: none;
            color: var(--primary);
            font-weight: 600;
            cursor: pointer;
            text-decoration: underline;
            padding: 0;
        }

        .link-button:hover {
            color: var(--primary-dark);
        }

        /* Responsive Design */
        @media (max-width: 1400px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 24px 16px 60px;
            }

            .page-header {
                padding: 32px 24px;
            }

            .page-title {
                font-size: 1.75rem;
            }

            .page-subtitle {
                font-size: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .card {
                padding: 24px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .table-wrapper {
                overflow-x: auto;
            }

            table {
                min-width: 800px;
            }

            .form-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .btn {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .page-title {
                font-size: 1.5rem;
            }

            .stat-value {
                font-size: 1.5rem;
            }

            .upload-zone {
                padding: 32px 20px;
            }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/_header.php'; ?>

<div class="container">
    <!-- Page Header -->
    <header class="page-header">
        <div class="header-content">
            <h1 class="page-title">Attorney Availability Management</h1>
            <p class="page-subtitle">
                Manage your consultation schedule efficiently. Upload structured availability data, 
                edit individual time slots, and maintain an up-to-date calendar for client bookings.
            </p>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Windows</div>
                    <div class="stat-value"><?php echo e($entryCount); ?></div>
                    <div class="stat-hint">Available slots</div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Upcoming (7 days)</div>
                    <div class="stat-value"><?php echo e($upcomingWindows); ?></div>
                    <div class="stat-hint">Next week availability</div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Timezones</div>
                    <div class="stat-value"><?php echo e($distinctTimezoneCount); ?></div>
                    <div class="stat-hint">Distinct zones</div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Last Updated</div>
                    <div class="stat-value"><?php echo e($lastUpdated ? date('M d', strtotime($lastUpdated)) : '—'); ?></div>
                    <div class="stat-hint"><?php echo e($lastUpdated ? date('H:i', strtotime($lastUpdated)) : 'Not set'); ?></div>
                </div>
            </div>
        </div>
    </header>

    <!-- Alerts Section -->
    <?php if ($errors || $success || $infoMessages || !empty($skippedRows)): ?>
    <div class="alerts">
        <?php if ($errors): ?>
        <div class="alert alert-error">
            <div class="alert-icon">⚠</div>
            <div class="alert-content">
                <div class="alert-title">Error</div>
                <ul class="alert-list">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success">
            <div class="alert-icon">✓</div>
            <div class="alert-content">
                <div class="alert-title"><?php echo e($success); ?></div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($infoMessages): ?>
        <div class="alert alert-info">
            <div class="alert-icon">ℹ</div>
            <div class="alert-content">
                <?php foreach ($infoMessages as $message): ?>
                    <div><?php echo e($message); ?></div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($skippedRows)): ?>
        <div class="alert alert-info">
            <div class="alert-icon">ℹ</div>
            <div class="alert-content">
                <div class="alert-title">Some rows were skipped</div>
                <button type="button" class="link-button" id="toggleSkipped">View details</button>
                <ul class="alert-list" id="skippedDetails" style="display: none; margin-top: 12px;">
                    <?php foreach ($skippedRows as $skipped): ?>
                        <li><?php echo e($skipped); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Formatting Guidelines Section (Full Width, Top) -->
    <div class="card" style="margin-bottom: 40px;">
        <div class="card-header">
            <h2 class="card-title">Formatting Guidelines</h2>
            <p class="card-description">
                Please read these requirements before uploading your schedule to ensure successful data import.
            </p>
        </div>

        <div class="guidelines">
            <ul class="guidelines-list">
                <li>
                    <strong>start_at</strong> and <strong>end_at</strong>: Use ISO date format 
                    (e.g., 2025-03-18 09:00). Dates will be normalized automatically.
                </li>
                <li>
                    <strong>timezone</strong>: Must be a valid PHP timezone identifier 
                    (e.g., Africa/Johannesburg, UTC, America/New_York).
                </li>
                <li>
                    <strong>Optional columns</strong>: location, recurrence_rule, block_reason. 
                    Any additional columns will be ignored.
                </li>
                <li>
                    End time must be after start time. Invalid rows will be skipped with 
                    detailed error messages.
                </li>
            </ul>
        </div>

        <?php if (!$supportsExcel): ?>
        <div class="alert alert-error" style="margin-top: 20px;">
            <div class="alert-icon">⚠</div>
            <div class="alert-content">
                <div class="alert-title">Excel Support Unavailable</div>
                <div>Please upload CSV files only. Excel support requires the SimpleXLSX library.</div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Upload Schedule Section (Full Width, Below Guidelines) -->
    <div class="card" style="margin-bottom: 40px;">
        <div class="card-header">
            <h2 class="card-title">Upload Schedule</h2>
            <p class="card-description">
                Upload a CSV or Excel file containing your availability. The file should include 
                columns: start_at, end_at, timezone, and optional metadata.
            </p>
        </div>

        <form method="post" enctype="multipart/form-data" id="uploadForm">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="upload_schedule">
            
            <label class="upload-zone" id="uploadZone">
                <div class="upload-icon">📤</div>
                <div class="upload-title">Drop your file here or click to browse</div>
                <div class="upload-subtitle">Accepts CSV and Excel (.xlsx) files</div>
                <div class="upload-filename" id="uploadFilename">No file selected</div>
                <input type="file" name="schedule_file" id="fileInput" accept=".csv,.xlsx" required>
            </label>

            <div class="form-actions">
                <span class="form-hint">Uploading will replace your current availability</span>
                <button type="submit" class="btn btn-primary">
                    <span>Upload Schedule</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Manual Entry Section -->
    <div class="card" style="margin-bottom: 40px;">
        <div class="card-header">
            <h2 class="card-title">Create Schedule Manually</h2>
            <p class="card-description">
                Don't have a CSV file? Create your availability schedule manually by adding individual time slots.
            </p>
        </div>
        <div style="text-align: center; padding: 20px 0;">
            <button type="button" class="btn btn-primary" id="addManualEntryBtn" style="font-size: 1rem; padding: 14px 32px;">
                <span>➕ Add New Availability Window</span>
            </button>
        </div>
    </div>

    <!-- Availability Table Section -->
    <div class="table-section">
        <div class="section-header">
            <h2 class="section-title">Availability Windows</h2>
            <div style="display: flex; gap: 12px; align-items: center;">
                <button type="button" class="btn btn-primary" id="addManualEntryBtnHeader" style="display: <?php echo $entryCount > 0 ? 'inline-flex' : 'none'; ?>;">
                    ➕ Add New
                </button>
                <?php if ($entryCount > 0): ?>
                <form method="post" id="clearForm" onsubmit="return confirm('This will remove all availability data. Continue?');" style="margin: 0;">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="clear_schedule">
                    <button type="submit" class="btn btn-danger">Clear All</button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($entryCount === 0): ?>
        <div class="card">
            <div class="empty-state">
                <div class="empty-icon">📅</div>
                <h3 class="empty-title">No Availability Windows Yet</h3>
                <p class="empty-description">
                    Upload your first schedule to begin managing your availability, or create entries manually. 
                    You can edit individual time slots after importing or creating them.
                </p>
                <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('fileInput').click()">
                        Upload Schedule
                    </button>
                    <button type="button" class="btn btn-primary" id="addManualEntryBtnEmpty">
                        Create Manually
                    </button>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Timezone</th>
                        <th>Location</th>
                        <th>Recurrence</th>
                        <th>Notes</th>
                        <th style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $index => $entry): ?>
                    <tr>
                        <td><?php echo e($index + 1); ?></td>
                        <td>
                            <div class="table-datetime">
                                <div class="datetime-primary"><?php echo e(formatDisplayDate($entry['start_at'] ?? null)); ?></div>
                                <div class="datetime-secondary"><?php echo e($entry['start_at'] ?? ''); ?></div>
                            </div>
                        </td>
                        <td>
                            <div class="table-datetime">
                                <div class="datetime-primary"><?php echo e(formatDisplayDate($entry['end_at'] ?? null)); ?></div>
                                <div class="datetime-secondary"><?php echo e($entry['end_at'] ?? ''); ?></div>
                            </div>
                        </td>
                        <td><?php echo e($entry['timezone'] ?? ''); ?></td>
                        <td><?php echo e($entry['location'] ?? '—'); ?></td>
                        <td><?php echo e($entry['recurrence_rule'] ?? '—'); ?></td>
                        <td><?php echo e($entry['block_reason'] ?? '—'); ?></td>
                        <td>
                            <div class="table-actions">
                                <button type="button" class="btn btn-sm btn-edit" data-edit="<?php echo e($index); ?>"
                                    data-start="<?php echo e($entry['start_at'] ?? ''); ?>"
                                    data-end="<?php echo e($entry['end_at'] ?? ''); ?>"
                                    data-timezone="<?php echo e($entry['timezone'] ?? ''); ?>"
                                    data-location="<?php echo e($entry['location'] ?? ''); ?>"
                                    data-recurrence="<?php echo e($entry['recurrence_rule'] ?? ''); ?>"
                                    data-reason="<?php echo e($entry['block_reason'] ?? ''); ?>">
                                    Edit
                                </button>
                                <button type="button" class="btn btn-sm btn-delete" data-delete="<?php echo e($index); ?>">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal" id="editModal">
    <div class="modal-overlay" onclick="closeEditModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit Availability Window</h3>
            <button type="button" class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <form method="post" id="editForm">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="update_entry">
            <input type="hidden" name="entry_index" id="editIndex">
            
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-field">
                        <label class="form-label" for="editStart">Start Time</label>
                        <input type="datetime-local" class="form-input" name="start_at" id="editStart" required>
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="editEnd">End Time</label>
                        <input type="datetime-local" class="form-input" name="end_at" id="editEnd" required>
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="editTimezone">Timezone</label>
                        <input type="text" class="form-input" name="timezone" id="editTimezone" 
                            list="timezones" placeholder="Africa/Johannesburg" required>
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="editLocation">Location</label>
                        <input type="text" class="form-input" name="location" id="editLocation" 
                            placeholder="Optional">
                    </div>
                    <div class="form-field full-width">
                        <label class="form-label" for="editRecurrence">Recurrence Rule</label>
                        <input type="text" class="form-input" name="recurrence_rule" id="editRecurrence" 
                            placeholder="e.g., Every Tuesday 09:00-12:00">
                    </div>
                    <div class="form-field full-width">
                        <label class="form-label" for="editReason">Block Reason</label>
                        <textarea class="form-textarea" name="block_reason" id="editReason" 
                            placeholder="Optional notes or blocking reason"></textarea>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Manual Entry Modal -->
<div class="modal" id="addModal">
    <div class="modal-overlay" onclick="closeAddModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add New Availability Window</h3>
            <button type="button" class="modal-close" onclick="closeAddModal()">&times;</button>
        </div>
        <form method="post" id="addForm">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="add_entry">
            
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-field">
                        <label class="form-label" for="addStart">Start Time</label>
                        <input type="datetime-local" class="form-input" name="start_at" id="addStart" required>
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="addEnd">End Time</label>
                        <input type="datetime-local" class="form-input" name="end_at" id="addEnd" required>
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="addTimezone">Timezone</label>
                        <input type="text" class="form-input" name="timezone" id="addTimezone" 
                            list="timezones" placeholder="Africa/Johannesburg" required>
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="addLocation">Location</label>
                        <input type="text" class="form-input" name="location" id="addLocation" 
                            placeholder="Optional">
                    </div>
                    <div class="form-field full-width">
                        <label class="form-label" for="addRecurrence">Recurrence Rule</label>
                        <input type="text" class="form-input" name="recurrence_rule" id="addRecurrence" 
                            placeholder="e.g., Every Tuesday 09:00-12:00">
                    </div>
                    <div class="form-field full-width">
                        <label class="form-label" for="addReason">Block Reason</label>
                        <textarea class="form-textarea" name="block_reason" id="addReason" 
                            placeholder="Optional notes or blocking reason"></textarea>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAddModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Availability Window</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Form -->
<form method="post" id="deleteForm" class="visually-hidden">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="action" value="delete_entry">
    <input type="hidden" name="entry_index" id="deleteIndex">
</form>

<!-- Timezone Datalist -->
<datalist id="timezones">
    <?php foreach ($commonTimezones as $tz): ?>
    <option value="<?php echo e($tz); ?>"></option>
    <?php endforeach; ?>
</datalist>

<?php include __DIR__ . '/_footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // File upload handling
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('fileInput');
    const uploadFilename = document.getElementById('uploadFilename');
    
    if (uploadZone && fileInput) {
        uploadZone.addEventListener('click', () => fileInput.click());
        
        ['dragenter', 'dragover'].forEach(event => {
            uploadZone.addEventListener(event, (e) => {
                e.preventDefault();
                uploadZone.classList.add('dragover');
            });
        });
        
        ['dragleave', 'drop'].forEach(event => {
            uploadZone.addEventListener(event, (e) => {
                e.preventDefault();
                uploadZone.classList.remove('dragover');
            });
        });
        
        uploadZone.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length) {
                fileInput.files = files;
                uploadFilename.textContent = files[0].name;
            }
        });
        
        fileInput.addEventListener('change', () => {
            if (fileInput.files.length) {
                uploadFilename.textContent = fileInput.files[0].name;
            } else {
                uploadFilename.textContent = 'No file selected';
            }
        });
    }
    
    // Skipped rows toggle
    const toggleSkipped = document.getElementById('toggleSkipped');
    const skippedDetails = document.getElementById('skippedDetails');
    
    if (toggleSkipped && skippedDetails) {
        toggleSkipped.addEventListener('click', () => {
            const isHidden = skippedDetails.style.display === 'none';
            skippedDetails.style.display = isHidden ? 'block' : 'none';
            toggleSkipped.textContent = isHidden ? 'Hide details' : 'View details';
        });
    }
    
    // Edit modal handling
    const editButtons = document.querySelectorAll('[data-edit]');
    editButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('editIndex').value = btn.dataset.edit;
            document.getElementById('editStart').value = formatForInput(btn.dataset.start);
            document.getElementById('editEnd').value = formatForInput(btn.dataset.end);
            document.getElementById('editTimezone').value = btn.dataset.timezone;
            document.getElementById('editLocation').value = btn.dataset.location;
            document.getElementById('editRecurrence').value = btn.dataset.recurrence;
            document.getElementById('editReason').value = btn.dataset.reason;
            document.getElementById('editModal').classList.add('active');
        });
    });
    
    // Delete handling
    const deleteButtons = document.querySelectorAll('[data-delete]');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            if (confirm('Are you sure you want to delete this availability window? This cannot be undone.')) {
                document.getElementById('deleteIndex').value = btn.dataset.delete;
                document.getElementById('deleteForm').submit();
            }
        });
    });
    
    // Add manual entry button
    const addManualEntryBtn = document.getElementById('addManualEntryBtn');
    const addManualEntryBtnEmpty = document.getElementById('addManualEntryBtnEmpty');
    const addManualEntryBtnHeader = document.getElementById('addManualEntryBtnHeader');
    
    function openAddModal() {
        // Clear the form
        document.getElementById('addForm').reset();
        // Set default timezone if available
        const defaultTz = document.getElementById('addTimezone');
        if (defaultTz && !defaultTz.value) {
            defaultTz.value = 'Africa/Johannesburg';
        }
        // Open modal
        document.getElementById('addModal').classList.add('active');
    }
    
    if (addManualEntryBtn) {
        addManualEntryBtn.addEventListener('click', openAddModal);
    }
    
    if (addManualEntryBtnEmpty) {
        addManualEntryBtnEmpty.addEventListener('click', openAddModal);
    }
    
    if (addManualEntryBtnHeader) {
        addManualEntryBtnHeader.addEventListener('click', openAddModal);
    }
    
    // Format date for datetime-local input
    function formatForInput(dateStr) {
        if (!dateStr) return '';
        const date = new Date(dateStr.replace(' ', 'T'));
        if (isNaN(date.getTime())) return '';
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }
});

function closeEditModal() {
    document.getElementById('editModal').classList.remove('active');
}

function closeAddModal() {
    document.getElementById('addModal').classList.remove('active');
}

// Close modal on Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeEditModal();
        closeAddModal();
    }
});
</script>
</body>
</html>
<?php
require __DIR__ . '/../config.php';
require_login();

$pdo = db();
$userId = get_user_id();
$now = new DateTimeImmutable('now');
$nowTs = $now->getTimestamp();

$casesStmt = $pdo->prepare('SELECT id, title FROM cases WHERE user_id = ? ORDER BY title');
$casesStmt->execute([$userId]);
$cases = $casesStmt->fetchAll(PDO::FETCH_ASSOC);

$caseMap = [];
foreach ($cases as $case) {
    $caseMap[(int)$case['id']] = $case['title'] ?? 'Untitled Case';
}

$availableCaseIds = array_keys($caseMap);
$selectedCaseId = null;
$selectedCaseParam = isset($_GET['case_id']) ? trim($_GET['case_id']) : '';

if ($selectedCaseParam !== '' && strtolower($selectedCaseParam) !== 'all') {
    $candidate = (int)$selectedCaseParam;
    if (in_array($candidate, $availableCaseIds, true)) {
        $selectedCaseId = $candidate;
    }
}

$viewParam = strtolower($_GET['view'] ?? 'upcoming');
$defaultTab = $viewParam === 'history' ? 'history' : 'upcoming';

$appointments = [];
$upcomingAppointments = [];
$pastAppointments = [];

if (!empty($availableCaseIds)) {
    if ($selectedCaseId) {
        $query = '
            SELECT a.*, u.name AS attorney_name 
            FROM appointments a 
            LEFT JOIN users u ON a.assigned_to = u.id 
            WHERE a.case_id = ? AND a.status = \'approved\'
            ORDER BY a.start_time DESC
        ';
        $params = [$selectedCaseId];
    } else {
        $placeholders = implode(',', array_fill(0, count($availableCaseIds), '?'));
        $query = "
            SELECT a.*, u.name AS attorney_name 
            FROM appointments a 
            LEFT JOIN users u ON a.assigned_to = u.id 
            WHERE a.case_id IN ($placeholders) AND a.status = 'approved'
            ORDER BY a.start_time DESC
        ";
        $params = $availableCaseIds;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($appointments as $appointment) {
        $startTs = strtotime($appointment['start_time'] ?? '');
        if ($startTs === false) {
            continue;
        }

        $formatted = [
            'id' => (int)($appointment['id'] ?? 0),
            'case_id' => (int)($appointment['case_id'] ?? 0),
            'title' => $appointment['title'] ?? 'Appointment',
            'start_time' => $appointment['start_time'],
            'end_time' => $appointment['end_time'] ?? null,
            'status' => strtolower($appointment['status'] ?? 'pending'),
            'location' => $appointment['location'] ?? null,
            'attorney_name' => $appointment['attorney_name'] ?? null,
            'description' => $appointment['description'] ?? null,
            'start_label' => date('D, M j Y \a\t H:i', $startTs),
            'end_label' => $appointment['end_time'] ? date('H:i', strtotime($appointment['end_time'])) : null,
        ];

        if ($startTs >= $nowTs) {
            $upcomingAppointments[] = $formatted;
        } else {
            $pastAppointments[] = $formatted;
        }
    }

    usort($upcomingAppointments, fn($a, $b) => strtotime($a['start_time']) <=> strtotime($b['start_time']));
    usort($pastAppointments, fn($a, $b) => strtotime($b['start_time']) <=> strtotime($a['start_time']));
}

function render_status_badge(string $status): array
{
    $map = [
        'pending' => ['label' => 'Pending', 'class' => 'badge-soft-warning'],
        'scheduled' => ['label' => 'Scheduled', 'class' => 'badge-soft-primary'],
        'confirmed' => ['label' => 'Confirmed', 'class' => 'badge-soft-success'],
        'completed' => ['label' => 'Completed', 'class' => 'badge-soft-success'],
        'cancelled' => ['label' => 'Cancelled', 'class' => 'badge-soft-danger'],
        'declined' => ['label' => 'Declined', 'class' => 'badge-soft-danger'],
        'rescheduled' => ['label' => 'Rescheduled', 'class' => 'badge-soft-info'],
    ];

    $status = strtolower($status);
    return $map[$status] ?? ['label' => ucfirst($status ?: 'Pending'), 'class' => 'badge-soft-secondary'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Appointments Overview | MerLaws</title>
    <link rel="apple-touch-icon" sizes="180x180" href="../../favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../../favicon/favicon-32x32.png">
    <link rel="manifest" href="../../favicon/site.webmanifest">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/default.css">
    <link rel="stylesheet" href="../../assets/css/responsive.css">
    <style>
        :root {
            --merlaws-primary: #AC132A;
            --merlaws-primary-dark: #8a0f22;
            --merlaws-surface: #f7f9fc;
            --merlaws-border: #e2e8f0;
        }

        body {
            background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #1a202c;
        }

        .appointments-wrapper {
            padding: 2.5rem 1rem 3rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-heading {
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
            color: white;
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 0 18px 38px -20px rgba(172, 19, 42, 0.4);
            margin-bottom: 2.5rem;
            position: relative;
            overflow: hidden;
        }

        .page-heading::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 320"><defs><pattern id="g" width="20" height="20" patternUnits="userSpaceOnUse"><path d="M20 0L0 0 0 20" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23g)"/></svg>');
            opacity: 0.5;
        }

        .page-heading-content {
            position: relative;
            z-index: 1;
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            align-items: flex-start;
            justify-content: space-between;
        }

        .page-heading h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.25rem;
            margin-bottom: 0.75rem;
        }

        .page-heading p {
            font-size: 1rem;
            opacity: 0.85;
            margin-bottom: 0;
        }

        .badges-summary {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .summary-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255,255,255,0.15);
            border-radius: 999px;
            padding: 0.45rem 0.9rem;
            font-size: 0.85rem;
            letter-spacing: 0.02em;
        }

        .filter-card {
            background: white;
            border-radius: 20px;
            padding: 1.75rem 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
            border: 1px solid #edf2f7;
        }

        .filter-card h2 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #1f2937;
        }

        .tab-nav {
            background: white;
            border-radius: 16px;
            padding: 0.4rem;
            display: inline-flex;
            gap: 0.35rem;
            box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.2);
        }

        .tab-button {
            border: none;
            background: transparent;
            padding: 0.65rem 1.4rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.9rem;
            color: #64748b;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .tab-button.active {
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
            color: white;
            box-shadow: 0 8px 18px rgba(172, 19, 42, 0.25);
        }

        .appointments-section {
            display: none;
        }

        .appointments-section.active {
            display: block;
            animation: fadeIn 0.25s ease;
        }

        .appointment-card {
            background: white;
            border-radius: 18px;
            padding: 1.5rem 1.75rem;
            margin-bottom: 1rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .appointment-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 36px rgba(15, 23, 42, 0.12);
        }

        .appointment-header {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .appointment-title {
            font-size: 1.15rem;
            font-weight: 600;
            margin-bottom: 0.15rem;
        }

        .appointment-meta {
            display: flex;
            gap: 1.2rem;
            font-size: 0.9rem;
            color: #475569;
            flex-wrap: wrap;
        }

        .appointment-meta span {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        .badge-soft-primary { color: #1d4ed8; background: rgba(59, 130, 246, 0.15); }
        .badge-soft-success { color: #047857; background: rgba(16, 185, 129, 0.18); }
        .badge-soft-warning { color: #b45309; background: rgba(251, 191, 36, 0.2); }
        .badge-soft-danger { color: #b91c1c; background: rgba(248, 113, 113, 0.2); }
        .badge-soft-info { color: #0369a1; background: rgba(14, 165, 233, 0.18); }
        .badge-soft-secondary { color: #334155; background: rgba(148, 163, 184, 0.2); }

        .appointment-body {
            margin-top: 1rem;
            color: #475569;
            font-size: 0.95rem;
        }

        .appointment-footer {
            margin-top: 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .appointment-links {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .appointment-link {
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--merlaws-primary);
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .appointment-link:hover {
            color: var(--merlaws-primary-dark);
        }

        .empty-state {
            background: white;
            border-radius: 18px;
            padding: 2.5rem 2rem;
            border: 1px dashed #cbd5e1;
            text-align: center;
            color: #475569;
        }

        .empty-state i {
            font-size: 2rem;
            color: var(--merlaws-primary);
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #1f2937;
        }

        .empty-state p {
            margin-bottom: 1.5rem;
        }

        .cta-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, var(--merlaws-primary), var(--merlaws-primary-dark));
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 12px 24px rgba(172, 19, 42, 0.25);
        }

        .cta-button:hover {
            color: white;
            background: linear-gradient(135deg, var(--merlaws-primary-dark), var(--merlaws-primary));
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(4px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .appointments-wrapper {
                padding: 1rem 0.75rem 2rem;
            }

            .page-heading {
                padding: 1.5rem;
                border-radius: 16px;
                margin-bottom: 1.5rem;
            }

            .page-heading h1 {
                font-size: 1.75rem;
            }

            .page-heading p {
                font-size: 0.9rem;
            }

            .page-heading-content {
                flex-direction: column;
                gap: 1rem;
            }

            .cta-button {
                width: 100%;
                justify-content: center;
                min-height: 48px;
            }

            .filter-card {
                padding: 1.25rem;
                border-radius: 16px;
            }

            .filter-card h2 {
                font-size: 1rem;
            }

            .tab-nav {
                width: 100%;
                justify-content: center;
            }

            .tab-button {
                flex: 1;
                padding: 0.75rem 1rem;
                font-size: 0.85rem;
                min-height: 44px;
            }

            .appointment-card {
                padding: 1.25rem;
                border-radius: 16px;
            }

            .appointment-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .appointment-title {
                font-size: 1.05rem;
            }

            .appointment-meta {
                flex-direction: column;
                gap: 0.5rem;
            }

            .appointment-footer {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .appointment-links {
                width: 100%;
                flex-direction: column;
            }

            .appointment-link {
                width: 100%;
                padding: 0.75rem;
                justify-content: center;
                min-height: 44px;
            }

            .empty-state {
                padding: 2rem 1.5rem;
            }

            .empty-state h3 {
                font-size: 1.15rem;
            }

            .badges-summary {
                flex-direction: column;
                gap: 0.5rem;
            }
        }

        @media (max-width: 480px) {
            .appointments-wrapper {
                padding: 0.75rem 0.5rem 1.5rem;
            }

            .page-heading {
                padding: 1.25rem;
            }

            .page-heading h1 {
                font-size: 1.5rem;
            }

            .filter-card {
                padding: 1rem;
            }

            .appointment-card {
                padding: 1rem;
            }

            .form-select {
                font-size: 16px;
                padding: 12px 16px;
                min-height: 48px;
            }
        }
    </style>
</head>
<body>
<?php
$headerPath = __DIR__ . '/../../include/header.php';
if (file_exists($headerPath)) {
    echo file_get_contents($headerPath);
}
?>

<div class="appointments-wrapper">
    <div class="page-heading">
        <div class="page-heading-content">
            <div>
                <div class="summary-pill" style="background: rgba(255,255,255,0.22); margin-bottom: 0.5rem;">
                    <i class="fas fa-calendar-alt"></i>
                    Appointments
                </div>
                <h1>Manage Your Consultations</h1>
                <p>Review confirmed sessions, keep track of upcoming commitments, and revisit past appointments across all your active cases.</p>
                <div class="badges-summary">
                    <span class="summary-pill">
                        <i class="fas fa-clock"></i>
                        <?= count($upcomingAppointments) ?> upcoming
                    </span>
                    <span class="summary-pill">
                        <i class="fas fa-history"></i>
                        <?= count($pastAppointments) ?> completed
                    </span>
                    <span class="summary-pill">
                        <i class="fas fa-folder-open"></i>
                        <?= count($cases) ?> case<?= count($cases) === 1 ? '' : 's' ?>
                    </span>
                </div>
            </div>
            <a href="/app/appointments/create.php" class="cta-button">
                <i class="fas fa-plus"></i>
                Schedule New Appointment
            </a>
        </div>
    </div>

    <?php if (empty($cases)): ?>
        <div class="empty-state">
            <i class="fas fa-folder-open"></i>
            <h3>No Cases Yet</h3>
            <p>You need at least one active case before scheduling appointments. Create a case to get started.</p>
            <a href="/app/cases/create.php" class="cta-button">
                <i class="fas fa-plus-circle"></i>
                Create a Case
            </a>
        </div>
    <?php else: ?>
        <div class="filter-card">
            <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between">
                <div>
                    <h2>Filter by case</h2>
                    <p class="text-muted mb-0">Switch between matters to see the appointments that relate to each specific case.</p>
                </div>
                <form method="get" class="d-flex gap-2 flex-wrap">
                    <input type="hidden" name="view" value="<?= $defaultTab === 'history' ? 'history' : 'upcoming' ?>">
                    <select name="case_id" class="form-select" onchange="this.form.submit()" style="min-width: 240px;">
                        <option value="all" <?= $selectedCaseId ? '' : 'selected' ?>>All active cases</option>
                        <?php foreach ($caseMap as $id => $title): ?>
                            <option value="<?= (int)$id ?>" <?= $selectedCaseId === (int)$id ? 'selected' : '' ?>>
                                <?= e($title) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div class="tab-nav" role="tablist">
                <button class="tab-button <?= $defaultTab === 'upcoming' ? 'active' : '' ?>" type="button" data-target="#tab-upcoming" data-view="upcoming">
                    <i class="fas fa-calendar-check me-1"></i> Upcoming
                </button>
                <button class="tab-button <?= $defaultTab === 'history' ? 'active' : '' ?>" type="button" data-target="#tab-history" data-view="history">
                    <i class="fas fa-history me-1"></i> History
                </button>
            </div>
            <span class="text-muted" style="font-size: 0.9rem;">
                Times are shown in your account timezone.
            </span>
        </div>

        <div class="appointments-section <?= $defaultTab === 'upcoming' ? 'active' : '' ?>" id="tab-upcoming" role="tabpanel">
            <?php if (empty($upcomingAppointments)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-day"></i>
                    <h3>No Upcoming Appointments</h3>
                    <p>Once you schedule or confirm an appointment, it will appear here for quick reference.</p>
                    <a href="/app/appointments/create.php" class="cta-button">
                        <i class="fas fa-plus"></i>
                        Schedule Appointment
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($upcomingAppointments as $appointment): ?>
                    <?php $statusBadge = render_status_badge($appointment['status']); ?>
                    <div class="appointment-card">
                        <div class="appointment-header">
                            <div>
                                <div class="appointment-title"><?= e($appointment['title']) ?></div>
                                <div class="appointment-meta">
                                    <span><i class="fas fa-clock"></i> <?= e($appointment['start_label']) ?><?= $appointment['end_label'] ? ' - ' . e($appointment['end_label']) : '' ?></span>
                                    <span><i class="fas fa-folder-open"></i> <?= e($caseMap[$appointment['case_id']] ?? 'Case') ?></span>
                                    <?php if (!empty($appointment['location'])): ?>
                                        <span><i class="fas fa-map-marker-alt"></i> <?= e($appointment['location']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($appointment['attorney_name'])): ?>
                                        <span><i class="fas fa-user-tie"></i> <?= e($appointment['attorney_name']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="status-badge <?= e($statusBadge['class']) ?>">
                                <?= e($statusBadge['label']) ?>
                            </span>
                        </div>
                        <?php if (!empty($appointment['description'])): ?>
                            <div class="appointment-body">
                                <?= nl2br(e($appointment['description'])) ?>
                            </div>
                        <?php endif; ?>
                        <div class="appointment-footer">
                            <div class="appointment-links">
                                <a href="/app/cases/view.php?id=<?= (int)$appointment['case_id'] ?>#appointments" class="appointment-link">
                                    <i class="fas fa-folder"></i> View case timeline
                                </a>
                                <a href="/app/appointments/create.php?case_id=<?= (int)$appointment['case_id'] ?>&slot=<?= urlencode(substr($appointment['start_time'], 0, 16)) ?>" class="appointment-link">
                                    <i class="fas fa-calendar-plus"></i> Reschedule
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="appointments-section <?= $defaultTab === 'history' ? 'active' : '' ?>" id="tab-history" role="tabpanel">
            <?php if (empty($pastAppointments)): ?>
                <div class="empty-state">
                    <i class="fas fa-clipboard-check"></i>
                    <h3>No Past Appointments</h3>
                    <p>Your appointment history will appear here once sessions have been completed.</p>
                    <a href="/app/messages/" class="appointment-link">
                        <i class="fas fa-envelope"></i> Contact your case team
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($pastAppointments as $appointment): ?>
                    <?php $statusBadge = render_status_badge($appointment['status']); ?>
                    <div class="appointment-card">
                        <div class="appointment-header">
                            <div>
                                <div class="appointment-title"><?= e($appointment['title']) ?></div>
                                <div class="appointment-meta">
                                    <span><i class="fas fa-clock-rotate-left"></i> <?= e($appointment['start_label']) ?><?= $appointment['end_label'] ? ' - ' . e($appointment['end_label']) : '' ?></span>
                                    <span><i class="fas fa-folder-open"></i> <?= e($caseMap[$appointment['case_id']] ?? 'Case') ?></span>
                                    <?php if (!empty($appointment['attorney_name'])): ?>
                                        <span><i class="fas fa-user-tie"></i> <?= e($appointment['attorney_name']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="status-badge <?= e($statusBadge['class']) ?>">
                                <?= e($statusBadge['label']) ?>
                            </span>
                        </div>
                        <?php if (!empty($appointment['description'])): ?>
                            <div class="appointment-body">
                                <?= nl2br(e($appointment['description'])) ?>
                            </div>
                        <?php endif; ?>
                        <div class="appointment-footer">
                            <div class="appointment-links">
                                <a href="/app/cases/view.php?id=<?= (int)$appointment['case_id'] ?>#appointments" class="appointment-link">
                                    <i class="fas fa-folder"></i> Review case notes
                                </a>
                            </div>
                            <span class="text-muted" style="font-size: 0.85rem;">
                                Completed <?= e(date('M j, Y', strtotime($appointment['start_time']))) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$footerPath = __DIR__ . '/../../include/footer.html';
if (file_exists($footerPath)) {
    echo file_get_contents($footerPath);
}
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tab-button');
    const sections = document.querySelectorAll('.appointments-section');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const target = this.dataset.target;
            const view = this.dataset.view || 'upcoming';

            tabs.forEach(btn => btn.classList.toggle('active', btn === this));
            sections.forEach(section => {
                section.classList.toggle('active', section.id === target.substring(1));
            });

            const url = new URL(window.location);
            url.searchParams.set('view', view);
            const caseSelect = document.querySelector('select[name="case_id"]');
            if (caseSelect) {
                const value = caseSelect.value || 'all';
                url.searchParams.set('case_id', value);
            }
            history.replaceState({}, '', url);
        });
    });
});
</script>
</body>
</html>


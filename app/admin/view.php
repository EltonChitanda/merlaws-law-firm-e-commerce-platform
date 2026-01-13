<?php
// app/admin/view.php - Professional Case Management View
require __DIR__ . '/../config.php';
require __DIR__ . '/../csrf.php';
require_permission('case:view');

$pdo = db();
$case_id = (int)($_GET['id'] ?? 0);

if ($case_id <= 0) {
    redirect('cases.php');
}

// Fetch case details with related information
$stmt = $pdo->prepare("
    SELECT c.*,
           u.name AS client_name,
           u.email AS client_email,
           u.phone AS client_phone,
           u.address AS client_address,
           u.city AS client_city,
           u.medical_aid AS client_medical_aid,
           u.emergency_contact_name,
           u.emergency_contact_phone,
           au.name AS attorney_name,
           au.email AS attorney_email,
           au.phone AS attorney_phone
    FROM cases c
    JOIN users u ON c.user_id = u.id
    LEFT JOIN users au ON c.assigned_to = au.id
    WHERE c.id = ?
");
$stmt->execute([$case_id]);
$case = $stmt->fetch();

if (!$case) {
    $_SESSION['error'] = 'Case not found.';
    redirect('cases.php');
}

// Fetch case documents
$docs_stmt = $pdo->prepare("
    SELECT cd.*, u.name AS uploaded_by_name
    FROM case_documents cd
    JOIN users u ON cd.uploaded_by = u.id
    WHERE cd.case_id = ?
    ORDER BY cd.uploaded_at DESC
");
$docs_stmt->execute([$case_id]);
$documents = $docs_stmt->fetchAll();

// Fetch case services/tasks
$services_stmt = $pdo->prepare("SELECT * FROM case_services WHERE case_id = ? ORDER BY due_date ASC");
$services_stmt->execute([$case_id]);
$services = $services_stmt->fetchAll();

// Fetch case appointments
$appts_stmt = $pdo->prepare("
    SELECT ca.*, au.name AS attorney_name
    FROM case_appointments ca
    LEFT JOIN users au ON ca.attorney_id = au.id
    WHERE ca.case_id = ?
    ORDER BY ca.start_time DESC
");
$appts_stmt->execute([$case_id]);
$appointments = $appts_stmt->fetchAll();

// Fetch case notes/logs
$notes_stmt = $pdo->prepare("
    SELECT cn.*, u.name AS created_by_name
    FROM case_notes cn
    JOIN users u ON cn.created_by = u.id
    WHERE cn.case_id = ?
    ORDER BY cn.created_at DESC
");
$notes_stmt->execute([$case_id]);
$notes = $notes_stmt->fetchAll();


require __DIR__ . '/../template/header.php';
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-4">Case #<?= e($case['id']) ?>: <?= e($case['title']) ?></h1>
    <p class="mb-6 text-gray-600">Client: <?= e($case['client_name']) ?> | Status: <span class="font-semibold text-blue-600"><?= e($case['status']) ?></span></p>

    <!-- Tab Navigation (Tabs: Details, Documents, Services, Appointments, Notes) -->
    <div class="border-b border-gray-200">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="case-tabs" role="tablist">
            <li class="mr-2" role="presentation">
                <!-- Added data-tab attribute and tab-link class for JS control -->
                <a href="#details" id="details-tab" data-tab="details" class="tab-link inline-block p-4 border-b-2 rounded-t-lg" type="button" role="tab" aria-controls="details" aria-selected="true">Case Details</a>
            </li>
            <li class="mr-2" role="presentation">
                <a href="#documents" id="documents-tab" data-tab="documents" class="tab-link inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300" type="button" role="tab" aria-controls="documents" aria-selected="false">Documents (<?= count($documents) ?>)</a>
            </li>
            <li class="mr-2" role="presentation">
                <a href="#services" id="services-tab" data-tab="services" class="tab-link inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300" type="button" role="tab" aria-controls="services" aria-selected="false">Services/Tasks (<?= count($services) ?>)</a>
            </li>
            <li class="mr-2" role="presentation">
                <a href="#appointments" id="appointments-tab" data-tab="appointments" class="tab-link inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300" type="button" role="tab" aria-controls="appointments" aria-selected="false">Appointments (<?= count($appointments) ?>)</a>
            </li>
            <li role="presentation">
                <a href="#notes" id="notes-tab" data-tab="notes" class="tab-link inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300" type="button" role="tab" aria-controls="notes" aria-selected="false">Notes/Logs (<?= count($notes) ?>)</a>
            </li>
        </ul>
    </div>

    <!-- Tab Content -->
    <div id="tab-content" class="pt-4">
        <!-- Case Details Tab Content -->
        <div class="tab-pane p-4 bg-white shadow-sm rounded-lg" id="details" role="tabpanel" aria-labelledby="details-tab">
            <h3 class="text-xl font-semibold mb-3">General Case Information</h3>
            <p><strong>Description:</strong> <?= nl2br(e($case['description'])) ?></p>
            <p><strong>Assigned To:</strong> <?= e($case['attorney_name'] ?: 'Unassigned') ?></p>
            <p><strong>Created At:</strong> <?= date('Y-m-d H:i', strtotime($case['created_at'])) ?></p>

            <h3 class="text-xl font-semibold mt-4 mb-3">Client Information</h3>
            <p><strong>Name:</strong> <?= e($case['client_name']) ?></p>
            <p><strong>Email:</strong> <?= e($case['client_email']) ?></p>
            <p><strong>Phone:</strong> <?= e($case['client_phone']) ?></p>
            <p><strong>Address:</strong> <?= e($case['client_address']) ?>, <?= e($case['client_city']) ?></p>
            <p><strong>Medical Aid:</strong> <?= e($case['client_medical_aid'] ?: 'N/A') ?></p>
            <p><strong>Emergency Contact:</strong> <?= e($case['emergency_contact_name']) ?> (<?= e($case['emergency_contact_phone']) ?>)</p>
        </div>

        <!-- Documents Tab Content -->
        <div class="tab-pane hidden p-4 bg-white shadow-sm rounded-lg" id="documents" role="tabpanel" aria-labelledby="documents-tab">
            <h3 class="text-xl font-semibold mb-3">Case Documents</h3>
            <!-- Document Upload Form Placeholder -->
            <form method="POST" action="upload_document.php" enctype="multipart/form-data" class="mb-4 p-4 border rounded-lg bg-gray-50">
                <?= csrf_field() ?>
                <input type="hidden" name="case_id" value="<?= $case_id ?>">
                <label for="document_file" class="block text-sm font-medium text-gray-700">Upload New Document</label>
                <input type="file" name="document_file" id="document_file" required class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-white">
                <label for="description" class="block text-sm font-medium text-gray-700 mt-2">Description</label>
                <input type="text" name="description" id="description" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <button type="submit" class="mt-3 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Upload</button>
            </form>

            <?php if (empty($documents)): ?>
                <p>No documents found for this case.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($documents as $doc): ?>
                        <div class="flex justify-between items-center p-3 border rounded-md">
                            <div>
                                <p class="font-semibold"><?= e($doc['description']) ?></p>
                                <p class="text-sm text-gray-500">
                                    Uploaded by: <?= e($doc['uploaded_by_name']) ?> on <?= date('Y-m-d H:i', strtotime($doc['uploaded_at'])) ?>
                                </p>
                            </div>
                            <a href="<?= e($doc['file_path']) ?>" target="_blank" class="text-indigo-600 hover:text-indigo-900 font-medium">View File</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Services/Tasks Tab Content -->
        <div class="tab-pane hidden p-4 bg-white shadow-sm rounded-lg" id="services" role="tabpanel" aria-labelledby="services-tab">
            <h3 class="text-xl font-semibold mb-3">Case Services and Tasks</h3>
            <?php if (empty($services)): ?>
                <p>No services or tasks recorded for this case.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($services as $service): ?>
                        <div class="p-3 border rounded-md <?= $service['status'] == 'Completed' ? 'bg-green-50' : 'bg-yellow-50' ?>">
                            <p class="font-semibold"><?= e($service['task_name']) ?></p>
                            <p class="text-sm text-gray-600">Due: <?= date('Y-m-d', strtotime($service['due_date'])) ?></p>
                            <p class="text-sm text-gray-600">Status: <span class="font-medium"><?= e($service['status']) ?></span></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <!-- Add New Service/Task button/link here -->
        </div>

        <!-- Appointments Tab Content -->
        <div class="tab-pane hidden p-4 bg-white shadow-sm rounded-lg" id="appointments" role="tabpanel" aria-labelledby="appointments-tab">
            <h3 class="text-xl font-semibold mb-3">Case Appointments</h3>
            <?php if (empty($appointments)): ?>
                <p>No appointments recorded for this case.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($appointments as $appt): ?>
                        <div class="p-3 border rounded-md <?= $appt['status'] == 'Accepted' ? 'bg-blue-50' : ($appt['status'] == 'Declined' ? 'bg-red-50' : 'bg-gray-100') ?>">
                            <p class="font-semibold"><?= e($appt['title']) ?></p>
                            <p class="text-sm text-gray-600">
                                Start: <?= date('Y-m-d H:i', strtotime($appt['start_time'])) ?><br>
                                End: <?= date('Y-m-d H:i', strtotime($appt['end_time'])) ?>
                            </p>
                            <p class="text-sm text-gray-600">Status: <span class="font-medium"><?= e($appt['status']) ?></span></p>
                            <?php if ($appt['status'] == 'Pending'): ?>
                                <div class="mt-2 space-x-2">
                                    <button onclick="caseApptAccept(<?= $appt['id'] ?>)" class="text-sm bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600">Accept</button>
                                    <button onclick="showDeclinePrompt(<?= $appt['id'] ?>)" class="text-sm bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">Decline</button>
                                    <button onclick="showProposePrompt(<?= $appt['id'] ?>, '<?= e($appt['start_time']) ?>', '<?= e($appt['end_time']) ?>')" class="text-sm bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600">Propose New Time</button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Notes/Logs Tab Content -->
        <div class="tab-pane hidden p-4 bg-white shadow-sm rounded-lg" id="notes" role="tabpanel" aria-labelledby="notes-tab">
            <h3 class="text-xl font-semibold mb-3">Case Notes and Activity Log</h3>
            <!-- New Note Form Placeholder -->
            <form method="POST" action="add_note.php" class="mb-4 p-4 border rounded-lg bg-gray-50">
                <?= csrf_field() ?>
                <input type="hidden" name="case_id" value="<?= $case_id ?>">
                <label for="content" class="block text-sm font-medium text-gray-700">Add New Note</label>
                <textarea name="content" id="content" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                <button type="submit" class="mt-3 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Save Note</button>
            </form>

            <?php if (empty($notes)): ?>
                <p>No notes or log entries found for this case.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($notes as $note): ?>
                        <div class="p-3 border rounded-md">
                            <p class="font-semibold text-gray-800"><?= nl2br(e($note['content'])) ?></p>
                            <p class="text-xs text-gray-500 mt-1">
                                By: <?= e($note['created_by_name']) ?> on <?= date('Y-m-d H:i', strtotime($note['created_at'])) ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../template/footer.php'; ?>

<!-- Custom CSS for tab state appearance -->
<style>
.tab-link {
    color: #4b5563; /* text-gray-600 */
    border-color: transparent;
    transition: all 0.15s ease-in-out;
}
.tab-link:hover {
    color: #111827; /* text-gray-900 */
    border-color: #e5e7eb; /* border-gray-200 */
}
.active-tab {
    color: #4f46e5 !important; /* text-indigo-600 */
    border-color: #4f46e5 !important; /* border-indigo-600 */
    font-weight: 600;
}
.tab-pane.hidden {
    display: none;
}
/* Styling for the custom modal (to replace browser alerts/prompts) */
.custom-modal {
    position: fixed;
    inset: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 50;
}
.modal-content {
    background-color: white;
    padding: 1.5rem;
    border-radius: 0.5rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    max-width: 90%;
    width: 400px;
}
</style>

<!-- JavaScript to handle tab switching functionality -->
<script>
/**
 * Tab Switching Logic
 * 1. Prevents default link navigation (The fix for unclickable tabs).
 * 2. Toggles 'active-tab' class on the links.
 * 3. Toggles 'hidden' class on the content panes.
 * 4. Handles deep-linking via URL hash.
 */
document.addEventListener('DOMContentLoaded', () => {
    const tabLinks = document.querySelectorAll('.tab-link');
    const tabPanes = document.querySelectorAll('.tab-pane');

    // Function to switch tab content and styling
    function switchTab(tabId) {
        // Deactivate all tabs and hide all panes
        tabLinks.forEach(link => {
            link.classList.remove('active-tab');
            link.setAttribute('aria-selected', 'false');
        });
        tabPanes.forEach(pane => {
            pane.classList.add('hidden');
        });

        // Activate the clicked tab and show the corresponding pane
        const clickedTab = document.querySelector(`.tab-link[data-tab="${tabId}"]`);
        const targetPane = document.getElementById(tabId);

        if (clickedTab && targetPane) {
            clickedTab.classList.add('active-tab');
            clickedTab.setAttribute('aria-selected', 'true');
            targetPane.classList.remove('hidden');

            // Update URL hash without page reload for deep linking
            if (window.history.pushState) {
                window.history.pushState(null, null, `#${tabId}`);
            } else {
                location.hash = tabId;
            }
        }
    }

    // Add event listeners to handle the click
    tabLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault(); // <-- The crucial fix: stops the page from jumping/reloading
            const tabId = e.currentTarget.getAttribute('data-tab');
            switchTab(tabId);
        });
    });

    // Check URL hash on load to open the correct tab
    const hash = window.location.hash.substring(1);
    const initialTab = hash || 'details'; // Default to 'details' if no hash is present
    switchTab(initialTab);
});

// Existing JS for form modification tracking (kept as is)
let formModified = false;
document.querySelectorAll('input, select, textarea').forEach(input => {
    input.addEventListener('change', () => {
        formModified = true;
    });
});

window.addEventListener('beforeunload', function(e) {
    if (formModified) {
        e.preventDefault();
        e.returnValue = '';
        return '';
    }
});

// Reset form modified flag on submit
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', () => {
        formModified = false;
    });
});
</script>

<!-- Scripts for Appointment Management (Modified to use custom modals) -->
<script>
// --- Custom Modal Functions (replacing alert/prompt/confirm) ---

/** Shows a simple message box */
function showMessageBox(title, message, isError = false) {
    const existingModal = document.querySelector('.custom-modal');
    if (existingModal) existingModal.remove();

    const modalHtml = `
        <div class="custom-modal">
            <div class="modal-content">
                <p class="text-lg font-semibold mb-3 ${isError ? 'text-red-600' : 'text-gray-800'}">${title}</p>
                <p class="text-gray-700 mb-4">${message}</p>
                <button onclick="this.closest('.custom-modal').remove()" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Close</button>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

/** Shows a prompt modal and returns a promise */
function showPromptModal(title, initialValue = '') {
    return new Promise(resolve => {
        const existingModal = document.querySelector('.custom-modal');
        if (existingModal) existingModal.remove();

        const modalHtml = `
            <div class="custom-modal">
                <div class="modal-content">
                    <p class="text-lg font-semibold mb-3 text-gray-800">${title}</p>
                    <input type="text" id="prompt-input" value="${initialValue}" class="w-full p-2 border border-gray-300 rounded-md mb-4">
                    <div class="flex justify-end space-x-3">
                        <button id="prompt-cancel" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">Cancel</button>
                        <button id="prompt-ok" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">OK</button>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        const modal = document.querySelector('.custom-modal');
        const input = document.getElementById('prompt-input');
        const okBtn = document.getElementById('prompt-ok');
        const cancelBtn = document.getElementById('prompt-cancel');

        if (input) input.focus();

        const cleanUp = (result) => {
            if (modal) modal.remove();
            resolve(result);
        };

        okBtn.onclick = () => cleanUp(input.value);
        cancelBtn.onclick = () => cleanUp(null);
        input.onkeydown = (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                okBtn.click();
            }
        };
    });
}

// --- Appointment API Call Functions ---

async function caseApptCall(action, payload){
    const form = new URLSearchParams();
    form.set('action', action);
    for (const k in payload) form.set(k, payload[k]);
    const csrf = document.getElementById('_csrf');
    if (csrf) form.set('csrf_token', csrf.value);

    try {
        const res = await fetch('../api/appointments.php', { method:'POST', body: form, credentials:'same-origin' });
        const j = await res.json();
        if (!j.success) {
            showMessageBox('Operation Failed', j.error || 'The appointment operation failed due to an unknown error.', true);
            return;
        }
        location.reload();
    } catch (e) {
        showMessageBox('Network Error', 'Could not connect to the server to perform the operation.', true);
    }
}

function caseApptAccept(id){ return caseApptCall('accept', { appointment_id: id }); }

async function showDeclinePrompt(id){
    const reason = await showPromptModal('Optional reason for decline:', '');
    if (reason === null) return; // User cancelled
    return caseApptCall('decline', { appointment_id: id, reason });
}

async function showProposePrompt(id, curStart, curEnd){
    const s = await showPromptModal('New start (YYYY-MM-DD HH:MM:SS)', curStart.replace('T',' ').slice(0,19));
    if (!s) return; // User cancelled or entered empty string
    const e = await showPromptModal('New end (YYYY-MM-DD HH:MM:SS)', curEnd.replace('T',' ').slice(0,19));
    if (!e) return; // User cancelled or entered empty string
    return caseApptCall('propose', { appointment_id: id, start_time: s, end_time: e });
}
</script>
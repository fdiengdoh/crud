<?php
// app/admin/view-subscribers.php
use App\Helpers\AuthHelper;
use App\Helpers\CsrfHelper;
use App\Controllers\SubscribersController;

// Ensure only admin users can access this page.
AuthHelper::requireAdmin($auth, $link->getUrl('/user'));

$pageTitle = "Admin - View Subscribers";
$subController = new SubscribersController();

// Initialize status variables
$errorMessage = $_GET['error'] ?? '';
$successMessage = $_GET['msg'] ?? '';
$subscribers = [];

// --- 1. HANDLE POST ACTIONS (Security First) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Global CSRF Check
    if (!CsrfHelper::isValid($_POST['csrf_token'] ?? '')) {
        header('Location: ' . $link->getUrl('/admin/view-subscribers') . '?error=' . urlencode('Security token mismatch. Please refresh.'));
        exit();
    }

    // A. Handle CSV Import
    if (isset($_POST['import_subscribers'])) {
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['csv_file']['tmp_name'];
            if (pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION) === 'csv') {
                $imported = 0;
                if (($handle = fopen($fileTmpPath, "r")) !== FALSE) {
                    $header = fgetcsv($handle); // Skip header
                    while (($data = fgetcsv($handle)) !== FALSE) {
                        if (count($data) >= 2) {
                            $name = trim($data[0]);
                            $email = filter_var(trim($data[1]), FILTER_SANITIZE_EMAIL);
                            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                if ($subController->addSubscriber($email, $name)) $imported++;
                            }
                        }
                    }
                    fclose($handle);
                    header('Location: ' . $link->getUrl('/admin/view-subscribers') . '?msg=' . urlencode("Imported $imported subscribers."));
                    exit();
                }
            }
        }
    }

    // B. Handle Bulk/Single Unsubscribe
    if (isset($_POST['bulk_action']) && $_POST['bulk_action'] === 'unsubscribe') {
        $emails = $_POST['selected_emails'] ?? [];
        $singleEmail = $_POST['email'] ?? ''; // Added this check

        // If it's a single action, put that email into our processing array
        if (!empty($singleEmail)) {
            $emails = [$singleEmail];
        }

        if (!empty($emails)) {
            $count = 0;
            foreach ($emails as $email) {
                if ($subController->unsubscribe(filter_var($email, FILTER_SANITIZE_EMAIL))) {
                    $count++;
                }
            }
            header('Location: ' . $link->getUrl('/admin/view-subscribers') . '?msg=' . urlencode("Successfully unsubscribed $count user(s)."));
            exit();
        }
    }
    
    // C. Handle Single Verify (Converted to POST)
    if (isset($_POST['bulk_action']) && $_POST['bulk_action'] === 'verify') {
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $token = $_POST['token'] ?? '';
        if ($subController->verify($email, $token)) {
            header('Location: ' . $link->getUrl('/admin/view-subscribers') . '?msg=' . urlencode("Subscriber verified."));
            exit();
        }
    }
}

// Fetch subscribers for the table
$subscribers = $subController->getSubscribers();

include APP_DIR . '/admin/header-auth.php';
?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Subscriber Management</h2>
        <span class="badge bg-primary"><?= count($subscribers) ?> Total</span>
    </div>

    <?php if ($errorMessage): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($errorMessage) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($successMessage): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($successMessage) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-4 border-info">
        <div class="card-header bg-info text-white">
            <i class="bi bi-file-earmark-arrow-up"></i> Import via CSV
        </div>
        <div class="card-body">
            <p class="card-text">Upload a CSV file containing 'Name' and 'Email' columns to import new subscribers.</p>
<p class="card-text text-muted"><strong>File Format:</strong> Your CSV should have a header row, with at least 'Name' and 'Email' columns (case-insensitive). <br>Example: <code>Name,Email<br>John Doe,john.doe@example.com<br>Jane Smith,jane.smith@example.com</code></p>
            <form action="<?= $link->getUrl('/admin/view-subscribers'); ?>" method="POST" enctype="multipart/form-data" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <div class="col-auto">
                    <input class="form-control" type="file" name="csv_file" accept=".csv" required>
                </div>
                <div class="col-auto">
                    <button type="submit" name="import_subscribers" class="btn btn-info text-white">Upload CSV</button>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($subscribers)): ?>
        <div class="alert alert-light border">No subscribers found.</div>
    <?php else: ?>
        <form method="POST" action="<?= $link->getUrl('/admin/view-subscribers'); ?>" id="subscriber-form">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            
            <div class="mb-3">
                <button type="submit" name="bulk_action" value="unsubscribe" class="btn btn-danger btn-sm" onclick="return confirm('Unsubscribe selected users?');">
                    <i class="bi bi-trash"></i> Bulk Unsubscribe
                </button>
            </div>

            <div class="table-responsive shadow-sm">
                <table class="table table-hover align-middle bg-white">
                    <thead class="table-dark">
                        <tr>
                            <th width="40"><input type="checkbox" id="select-all" class="form-check-input"></th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subscribers as $sub): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="selected_emails[]" value="<?= $sub['email']; ?>" class="sub-checkbox form-check-input">
                                </td>
                                <td><strong><?= htmlspecialchars($sub['full_name']); ?></strong></td>
                                <td><?= htmlspecialchars($sub['email']); ?></td>
                                <td>
                                    <?php if ($sub['is_verified']): ?>
                                        <span class="badge bg-success-subtle text-success border border-success">Verified</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td><small class="text-muted"><?= date('M d, Y', strtotime($sub['subscribed_at'])); ?></small></td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <?php if (!$sub['is_verified']): ?>
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                onclick="submitSingleAction('verify', '<?= addslashes($sub['email']) ?>', '<?= addslashes($sub['token']) ?>')">
                                                Verify
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="submitSingleAction('unsubscribe', '<?= addslashes($sub['email']) ?>')">
                                            Unsubscribe
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <input type="hidden" name="email" id="single-email">
            <input type="hidden" name="token" id="single-token">
        </form>
    <?php endif; ?>
</div>

<script>
// Select All Logic
document.getElementById('select-all').onclick = function() {
    let checkboxes = document.querySelectorAll('.sub-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
}

// Fixed Single Action Helper
function submitSingleAction(action, email, token = '') {
    if (confirm('Are you sure you want to ' + action + '?')) {
        const form = document.getElementById('subscriber-form');
        
        // 1. Set the email and token into the hidden fields
        document.getElementById('single-email').value = email;
        document.getElementById('single-token').value = token;
        
        // 2. Clear any existing checkboxes to prevent bulk-unsubscribing others by accident
        let checkboxes = document.querySelectorAll('.sub-checkbox');
        checkboxes.forEach(cb => cb.checked = false);

        // 3. Create a hidden input to act as the "Clicked Button"
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'bulk_action'; // This matches your PHP: isset($_POST['bulk_action'])
        actionInput.value = action;
        
        form.appendChild(actionInput);
        form.submit();
    }
}
</script>

<?php include APP_DIR . '/admin/footer-auth.php'; ?>
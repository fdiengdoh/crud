
<?php
// app/admin/view-logs.php

use App\Helpers\AuthHelper;
use App\Helpers\CsrfHelper; // Import the Helper

// Ensure only admin users can access this page.
AuthHelper::requireAdmin($auth, $link->getUrl('/user'));

$logFile = ROOT_DIR . '/logs/error.log';
$archiveDir = ROOT_DIR . '/logs/archive/';
$message = '';

// --- Handle POST Actions (Secure) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. CSRF Validation
    if (!CsrfHelper::isValid($_POST['csrf_token'] ?? '')) {
        $message = "Security validation failed. Please refresh the page.";
    } 
    // 2. Archive Logic
    elseif (isset($_POST['archive'])) {
        if (!file_exists($logFile) || filesize($logFile) === 0) {
            $message = "Log file is already empty or does not exist.";
        } else {
            if (!is_dir($archiveDir)) {
                mkdir($archiveDir, 0755, true); // Changed to 0755 for better security than 0777
            }
            
            $dateSuffix = date('Y-m-d_H-i-s');
            $archivedLogFile = $archiveDir . 'error-' . $dateSuffix . '.log';

            if (copy($logFile, $archivedLogFile)) {
                file_put_contents($logFile, ''); // Clear the current log
                $message = "Logs archived successfully to: error-$dateSuffix.log";
            } else {
                $message = "Failed to archive logs. Check folder permissions.";
            }
        }
    }
}

// Read the log file contents after potential archiving
$logContents = (file_exists($logFile) && filesize($logFile) > 0) 
    ? file_get_contents($logFile) 
    : "The log file is currently empty.";

$pageTitle = "View Logs";
include APP_DIR . '/admin/header-auth.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-terminal"></i> System Logs</h1>
        <form method="post" onsubmit="return confirm('This will move current logs to the archive folder and clear the active log. Proceed?');">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken; ?>">
            
            <button type="submit" name="archive" class="btn btn-warning shadow-sm">
                <i class="bi bi-archive-fill"></i> Archive & Clear Logs
            </button>
        </form>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between">
            <span>Active Log: error.log</span>
            <small class="text-secondary">Location: /logs/</small>
        </div>
        <div class="card-body p-0">
            <pre class="m-0" style="background:#1e1e1e; color:#d4d4d4; padding:20px; white-space:pre-wrap; max-height:600px; overflow:auto; font-family: 'Courier New', Courier, monospace; font-size: 0.9rem;">
<?= htmlspecialchars($logContents, ENT_QUOTES, 'UTF-8') ?>
            </pre>
        </div>
    </div>
</div>

<?php include APP_DIR . '/admin/footer-auth.php'; ?>
<?php
/* app/admin/view-logs.php

use App\Helpers\AuthHelper;

// Ensure only admin users can access this page.
AuthHelper::requireAdmin($auth, $link->getUrl('/user'));

$logFile = ROOT_DIR . '/logs/error.log';
$archiveDir = ROOT_DIR . '/logs/archive/';

// Handle log archiving if the user clicks the "Archive Logs" button.
if (isset($_POST['archive'])) {
    if (!file_exists($logFile)) {
        $message = "Log file not found.";
    } else {
        if (!is_dir($archiveDir)) {
            mkdir($archiveDir, 0777, true);
        }
        
        $dateSuffix = date('Y-m-d_H-i-s');
        $archivedLogFile = $archiveDir . 'error-' . $dateSuffix . '.log';

        if (rename($logFile, $archivedLogFile)) {
            file_put_contents($logFile, ''); // Create a new empty log file.
            $message = "Logs archived successfully.";
        } else {
            $message = "Failed to archive logs.";
        }
    }
}

// Read the log file contents.
$logContents = file_exists($logFile) ? file_get_contents($logFile) : "Log file is empty or does not exist.";

$pageTitle = "View Logs";
include APP_DIR . '/admin/header-auth.php';
?>

<div class="container">
    <h1>System Logs</h1>

    <?php if (!empty($message)): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message ?? ' ', ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="post">
        <button type="submit" name="archive" class="btn btn-warning" onclick="return confirm('Are you sure you want to archive the logs?');">
            Archive Logs
        </button>
    </form>

    <div class="mt-3">
        <h3>Current Log Contents</h3>
        <pre style="background:#f8f9fa; padding:15px; border:1px solid #ddd; border-radius:5px; white-space:pre-wrap; max-height:400px; overflow:auto;">
<?= htmlspecialchars($logContents ?? ' ', ENT_QUOTES, 'UTF-8') ?>
        </pre>
    </div>
</div>

<?php include APP_DIR . '/admin/footer-auth.php'; ?>
*/
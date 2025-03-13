<?php
// app/admin/view-logs.php
require_once __DIR__ . '/../../init.php';

use App\Helpers\AuthHelper;

// Ensure only admin users can access this page.
AuthHelper::requireAdmin($auth);

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
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post">
        <button type="submit" name="archive" class="btn btn-warning" onclick="return confirm('Are you sure you want to archive the logs?');">
            Archive Logs
        </button>
    </form>

    <div class="mt-3">
        <h3>Current Log Contents</h3>
        <pre style="background:#f8f9fa; padding:15px; border:1px solid #ddd; border-radius:5px; white-space:pre-wrap; max-height:400px; overflow:auto;">
<?= htmlspecialchars($logContents) ?>
        </pre>
    </div>
</div>

<?php include APP_DIR . '/admin/footer-auth.php'; ?>

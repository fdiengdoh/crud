<?php
// app/admin/view-subscribers.php
$pageTitle = "Admin - View Subscribers";
include APP_DIR . '/admin/header-auth.php';

use App\Helpers\AuthHelper;
use App\Controllers\SubscribersController;

// Ensure only admin users can access this page.
AuthHelper::requireAdmin($auth, $link->getUrl('/user'));

// Initialize variables
$subscribers = [];
$errorMessage = '';
$successMessage = ''; // To display success messages for admin actions
$importResults = []; // To store results of the CSV import

try {
    // Instantiate your SubscribersController.
    $subController = new SubscribersController();

    // --- Handle CSV Import (POST request for file upload) ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_subscribers'])) {
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['csv_file']['tmp_name'];
            $fileName = $_FILES['csv_file']['name'];
            $fileSize = $_FILES['csv_file']['size'];
            $fileType = $_FILES['csv_file']['type'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowedExts = ['csv'];
            $maxFileSize = 2 * 1024 * 1024; // 2 MB

            if (!in_array($fileExt, $allowedExts)) {
                $errorMessage = 'Invalid file type. Only CSV files are allowed.';
            } elseif ($fileSize > $maxFileSize) {
                $errorMessage = 'File size exceeds the maximum limit of 2MB.';
            } else {
                $importedCount = 0;
                $skippedCount = 0;
                $failedCount = 0;
                $lineNumber = 1; // Track line numbers for error reporting

                if (($handle = fopen($fileTmpPath, "r")) !== FALSE) {
                    // Read the first row (header)
                    $header = fgetcsv($handle);
                    // Standardize header column names to lower case for flexible matching
                    $header = array_map('strtolower', array_map('trim', $header));

                    // Find column indices for 'name' and 'email'
                    $nameCol = array_search('name', $header);
                    $emailCol = array_search('email', $header);

                    if ($nameCol === false || $emailCol === false) {
                        $errorMessage = 'CSV header must contain "Name" and "Email" columns.';
                    } else {
                        while (($data = fgetcsv($handle)) !== FALSE) {
                            $lineNumber++; // Increment for data rows
                            // Ensure row has enough columns
                            if (count($data) > max($nameCol, $emailCol)) {
                                $name = trim($data[$nameCol]);
                                $email = filter_var(trim($data[$emailCol]), FILTER_SANITIZE_EMAIL);

                                if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                    // Use the addSubscriber method from your controller
                                    if ($subController->addSubscriber($email, $name)) {
                                        $importedCount++;
                                    } else {
                                        // addSubscriber returns false if it failed or if subscriber already exists
                                        // You might want more granular feedback here from addSubscriber
                                        $skippedCount++; // Assume skipped if already exists or minor failure
                                        error_log("CSV Import: Failed/skipped adding email '{$email}' (Line: {$lineNumber})");
                                    }
                                } else {
                                    $failedCount++;
                                    error_log("CSV Import: Invalid email or name data on line {$lineNumber}. Email: '{$email}', Name: '{$name}'");
                                }
                            } else {
                                $failedCount++;
                                error_log("CSV Import: Insufficient columns on line {$lineNumber}.");
                            }
                        }
                        fclose($handle);

                        $importResults[] = "CSV import complete: Imported {$importedCount} new subscribers.";
                        if ($skippedCount > 0) {
                            $importResults[] = "Skipped {$skippedCount} existing or invalid entries.";
                        }
                        if ($failedCount > 0) {
                            $importResults[] = "Failed to process {$failedCount} entries due to missing data or other errors. Check logs for details.";
                        }
                        $successMessage = implode('<br>', $importResults); // Combine messages

                        // Redirect after successful import to clear POST data
                        header('Location: ' . $link->getUrl('/admin/view-subscribers') . '?import_status=success');
                        exit();
                    }
                } else {
                    $errorMessage = 'Could not open the uploaded CSV file.';
                }
            }
        } elseif (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $errorMessage = 'File upload error: ' . $_FILES['csv_file']['error'];
        } else {
            // No file uploaded, or other non-critical POST
        }
    }
    // --- End CSV Import Handling ---


    // --- Handle Admin Actions (Verify/Unsubscribe) via GET request ---
    if (isset($_GET['action'])) {
        $action = $_GET['action'];

        if ($action === 'verify') {
            $email = filter_var($_GET['email'] ?? '', FILTER_SANITIZE_EMAIL);
            $token = htmlspecialchars($_GET['token'] ?? ''); // Sanitize token

            if (!$email || empty($token)) {
                $errorMessage = 'Invalid email or token provided for verification.';
            } else {
                if ($subController->verifySubscriber($email, $token)) { // Changed verify to verifySubscriber
                    $successMessage = 'Subscriber successfully marked as verified!';
                } else {
                    $errorMessage = 'Failed to verify subscriber or subscriber not found/token mismatch.';
                }
            }
        } elseif ($action === 'unsubscribe') { // Changed 'delete' to 'unsubscribe'
            $emailToUnsubscribe = filter_var($_GET['email'] ?? '', FILTER_SANITIZE_EMAIL); // Get email for unsubscribe

            if (!$emailToUnsubscribe) {
                $errorMessage = 'Invalid email provided for unsubscription.';
            } else {
                if ($subController->unsubscribe($emailToUnsubscribe)) { // Call unsubscribe with email
                    $successMessage = 'Subscriber successfully unsubscribed!';
                } else {
                    $errorMessage = 'Failed to unsubscribe subscriber or subscriber not found.';
                }
            }
        } else {
            $errorMessage = 'Invalid action specified.';
        }

        // After processing the action, redirect to clean the URL
        header('Location: ' . $link->getUrl('/admin/view-subscribers'));
        exit();
    }
    // --- End Admin Actions Handling ---

    // Check for success message after import redirect
    if (isset($_GET['import_status']) && $_GET['import_status'] === 'success') {
        $successMessage = "CSV import completed successfully! Check the table below for newly added subscribers.";
    }


    // Fetch all subscribers to display the table (after any actions are processed)
    $subscribers = $subController->getSubscribers();

} catch (Exception $e) {
    error_log('Error on admin page: ' . $e->getMessage());
    $errorMessage = 'An internal error occurred. Please try again later.';
}

?>
<div class="container">
    <h2 class="mb-4">All Subscribers</h2>

    <?php if ($errorMessage): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $errorMessage; ?>
        </div>
    <?php endif; ?>

    <?php if ($successMessage): ?>
        <div class="alert alert-success" role="alert">
            <?php echo $successMessage; ?>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <h4>Import Subscribers (CSV)</h4>
        </div>
        <div class="card-body">
            <p class="card-text">Upload a CSV file containing 'Name' and 'Email' columns to import new subscribers.</p>
            <p class="card-text text-muted"><strong>File Format:</strong> Your CSV should have a header row, with at least 'Name' and 'Email' columns (case-insensitive). <br>Example: <code>Name,Email<br>John Doe,john.doe@example.com<br>Jane Smith,jane.smith@example.com</code></p>
            <form action="<?php echo $link->getUrl('/admin/view-subscribers'); ?>" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="csv_file" class="form-label">Select CSV File:</label>
                    <input class="form-control" type="file" id="csv_file" name="csv_file" accept=".csv" required>
                </div>
                <button type="submit" name="import_subscribers" class="btn btn-info">
                    <i class="bi bi-cloud-arrow-up-fill"></i> Import CSV <i class="bi bi-filetype-csv"></i>
                </button>
            </form>
        </div>
    </div>

    <?php if (empty($subscribers)): ?>
        <div class="alert alert-info" role="alert">
            No subscribers found yet.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Full Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Verified</th>
                        <th scope="col">Subscribed On</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subscribers as $index => $subscriber): ?>
                        <tr>
                            <th scope="row"><?php echo $index + 1; ?></th>
                            <td><?php echo $subscriber['full_name']; ?></td>
                            <td><?php echo $subscriber['email']; ?></td>
                            <td>
                                <?php if ($subscriber['is_verified']): ?>
                                    <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Verified</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i> Not Verified</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('Y-m-d H:i', strtotime($subscriber['subscribed_at'])); ?></td>
                            <td>
                                <?php if (!$subscriber['is_verified']): ?>
                                <a href="<?php echo $link->getUrl('/admin/view-subscribers') . '?action=verify&email=' . urlencode($subscriber['email'] ?? '') . '&token=' . urlencode($subscriber['verification_token'] ?? ''); ?>" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-user-check me-1"></i> Verify
                                </a>
                                <?php endif; ?>
                                <a href="<?php echo $link->getUrl('/admin/view-subscribers') . '?action=unsubscribe&email=' . urlencode($subscriber['email']); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to unsubscribe this subscriber? This cannot be undone.');">
                                    <i class="fas fa-user-times me-1"></i> Unsubscribe
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include APP_DIR . '/admin/footer-auth.php'; ?>
<?php
// app/admin/compose-newsletter.php

use App\Helpers\AuthHelper;
use App\Controllers\SubscribersController;
use App\Mailer; // Assuming your Mailer class is in the App\ namespace

// Ensure only admin users can access this page.
AuthHelper::requireAdmin($auth, $link->getUrl('/user'));

// --- PRE-FORMATTED NEWSLETTER CONTENT ---
$defaultBodyContent = <<<HTML
<table border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="#f6f8f1">
<tbody>
<tr>
<td><table class="content" style="width: 100%; max-width: 600px;" border="0" width="100%" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
<tbody>
<tr>
<td class="header" style="padding: 40px 30px 20px 30px;" bgcolor="#c7d8a7">
<table border="0" width="70" cellspacing="0" cellpadding="0" align="left">
<tbody>
<tr>
<td style="padding: 0 20px 20px 0;" height="70"><img class="fix" style="height: auto;" src="https://fdiengdoh.com/assets/image/fd_logo.webp" alt="" width="70" height="70" border="0"></td>
</tr>
</tbody>
</table>
<table class="col425" style="width: 100%; max-width: 425px;" border="0" cellspacing="0" cellpadding="0" align="left">
<tbody>
<tr>
<td height="70">
<table border="0" width="100%" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td class="subhead" style="font-size: 15px; color: #ffffff; font-family: sans-serif; letter-spacing: 10px; padding: 0 0 0 3px;">GREETINGS! FROM</td>
</tr>
<tr>
<td class="h1" style="color: #153643; font-family: sans-serif; font-size: 33px; line-height: 38px; font-weight: bold; padding: 5px 0 0 0;">Farlando Diengdoh</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
<tr>
<td class="innerpadding borderbottom" style="padding: 30px 30px 30px 30px; border-bottom: 1px solid #f2eeed;">
<table border="0" width="100%" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td class="h2" style="color: #153643; font-family: sans-serif; padding: 0 0 15px 0; font-size: 24px; line-height: 28px; font-weight: bold;">Wishing You a Happy New Year!</td>
</tr>
<tr>
<td class="bodycopy" style="color: #153643; font-family: sans-serif; font-size: 16px; line-height: 22px;">The year 2020 has been a year of uncertainty. This new year, I wish you and your family the best.</td>
</tr>
</tbody>
</table>
</td>
</tr>
<tr>
<td class="innerpadding borderbottom" style="padding: 30px 30px 30px 30px; border-bottom: 1px solid #f2eeed;">
<table border="0" width="115" cellspacing="0" cellpadding="0" align="left">
<tbody>
<tr>
<td style="padding: 0 20px 20px 0;" height="115"><img class="fix" style="height: auto;" src="http://fdiengdoh.com/assets/image/article.webp" alt="" width="115" height="115" border="0"></td>
</tr>
</tbody>
</table>
<table class="col380" style="width: 100%; max-width: 380px;" border="0" cellspacing="0" cellpadding="0" align="left">
<tbody>
<tr>
<td>
<table border="0" width="100%" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td class="bodycopy" style="color: #153643; font-family: sans-serif; font-size: 16px; line-height: 22px;">From my family to your family, I wish you a year of happiness, growth and prosperity. Looking forward to a better year ahead. Don't forget to keep in touch.</td>
</tr>
<tr>
<td style="padding: 20px 0 0 0;">
<table class="buttonwrapper" border="0" cellspacing="0" cellpadding="0" bgcolor="#1e4c7c">
<tbody>
<tr>
<td class="button" style="text-align: center; font-size: 18px; font-family: sans-serif; font-weight: bold; padding: 0 30px 0 30px;" align="center" height="45"><a style="color: #ffffff; text-decoration: none;" href="https://www.fdiengdoh.com" target="_blank" rel="noopener">Visit my Website</a></td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
<tr>
<td class="innerpadding borderbottom" style="padding: 30px 30px 30px 30px; border-bottom: 1px solid #f2eeed;"><img class="fix" style="height: auto;" src="https://cdn.fdh.pw/images/mawphanlur.jpg" alt="" width="100%" border="0"></td>
</tr>
<tr>
<td class="innerpadding bodycopy" style="padding: 30px 30px 30px 30px; color: #153643; font-family: sans-serif; font-size: 16px; line-height: 22px;">Here is a photo I took in the year 2019 when there is no Covid-19 lockdown yet. The location is Mawphanlur Natural Lake in West Khasi Hills.</td>
</tr>
<tr>
<td class="footer" style="padding: 20px 30px 15px 30px;" bgcolor="#44525f">
<table border="0" width="100%" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td class="footercopy" style="font-family: sans-serif; font-size: 14px; color: #ffffff;" align="center">2025 © Farlando Diengdoh, Shillong<br> 
<span style="color:#ccc">If you wish not to receive such emails you can <a href="%unsubscribe_link%" class="unsubscribe" style="text-decoration:none"><font color="#ccc">Click Here to Unsubscribe</font></a></span>
</td>
</tr>
<tr>
<td style="padding: 20px 0 0 0;" align="center">
<table border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="text-align: center; padding: 0 10px 0 10px;" width="37"><a href="http://www.facebook.com/fdphy"> <img style="height: auto;" src="https://cdn.fdiengdoh.com/fb.png" alt="Facebook" width="37" height="37" border="0"> </a></td>
<td style="text-align: center; padding: 0 10px 0 10px;" width="37"><a href="http://www.youtube.com/fdphy"> <img style="height: auto;" src="https://cdn.fdiengdoh.com/yt.png" alt="Instagram" width="37" height="37" border="0"> </a></td>
<td style="text-align: center; padding: 0 10px 0 10px;" width="37"><a href="http://www.facebook.com/fdphy"> <img style="height: auto;" src="https://cdn.fdiengdoh.com/fb.png" alt="Facebook" width="37" height="37" border="0"> </a></td>
<td style="text-align: center; padding: 0 10px 0 10px;" width="37"><a href="http://www.twitter.com/fdiengdoh"> <img style="height: auto;" src="https://cdn.fdiengdoh.com/x.png" alt="Twitter" width="37" height="37" border="0"> </a></td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
HTML;

// Initialize variables for form and feedback
$subject = '';
$body = $defaultBodyContent;
$successMessage = '';
$errorMessage = '';
$allSubscribers = []; // All subscribers fetched from DB
$subscribersToDisplay = []; // Subscribers to show in the checkbox list
$selectedSubscriberEmails = []; // To pre-check checkboxes on error or re-load

try {
    $subController = new SubscribersController();
    $allSubscribers = $subController->getSubscribers(); // Fetch all subscribers

    // Decide which subscribers to display for selection.
    // For a newsletter, typically you'd want to select from verified subscribers.
    // However, for maximum flexibility, let's display ALL and let the admin choose.
    // You can change this to display only verified: $subscribersToDisplay = array_filter($allSubscribers, function($s){ return $s['is_verified'] == 1; });
    $subscribersToDisplay = $allSubscribers;


    // --- Handle Form Submission (POST request) ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_newsletter'])) {
        $subject = trim($_POST['subject'] ?? '');
        $body = trim($_POST['body'] ?? ''); // This holds raw HTML from TinyMCE
        $selectedSubscriberEmails = $_POST['selected_subscribers'] ?? []; // Array of selected emails

        // Basic server-side validation for form input
        if (empty($subject)) {
            $errorMessage = 'Email subject cannot be empty.';
        } elseif (empty($body)) {
            $errorMessage = 'Email body cannot be empty.';
        } elseif (empty($selectedSubscriberEmails)) {
            $errorMessage = 'Please select at least one subscriber to send the newsletter.';
        } else {
            $mailer = new Mailer();
            $emailsSent = 0;
            $emailsFailed = 0;
            $subscribersToSendTo = [];

            // Build the list of actual subscriber objects from the selected emails
            foreach ($allSubscribers as $subscriber) {
                // Ensure the selected email actually corresponds to a valid subscriber in your DB.
                // This prevents injecting arbitrary emails.
                if (in_array($subscriber['email'], $selectedSubscriberEmails)) {
                    // You might want to ONLY send to verified subscribers even if they are selected.
                    // Add a check here: if ($subscriber['is_verified']) { ... }
                    $subscribersToSendTo[] = $subscriber;
                }
            }

            if (empty($subscribersToSendTo)) {
                 $errorMessage = 'No valid or verified subscribers were selected for sending.';
            } else {
                foreach ($subscribersToSendTo as $subscriber) {
                    $toEmail = $subscriber['email'];
                    $toName = htmlspecialchars($subscriber['full_name'] ?? 'Valued Subscriber'); // Sanitize and fallback

                    // --- DYNAMIC REPLACEMENTS ---//
                    // Personalize the full name
                    $processedBody = str_replace('%full_name%', $toName, $body);

                    // Construct the unique unsubscribe URL for the current subscriber
                    $unsubscribeLink = $link->getUrl('/subscriber') . '?unsubscribe=true&email=' . urlencode($toEmail);
                    
                    // Replace the unsubscribe link in the HTML body
                    $processedBody = str_replace('%unsubscribe_link%', $unsubscribeLink, $processedBody);

                    // Create a plain text version for the altBody, applying both replacements
                    $processedAltBody = strip_tags($body); // Start with plain text version of original body
                    $processedAltBody = str_replace('%full_name%', $toName, $processedAltBody);
                    $processedAltBody = str_replace('%unsubscribe_link%', $unsubscribeLink, $processedAltBody);
                    // --- END DYNAMIC REPLACEMENTS ---

                    try {
                        $mailer->sendEmail($toEmail, $toName, $subject, $processedBody, $processedAltBody);
                        $emailsSent++;
                    } catch (Exception $e) {
                        error_log("Failed to send email to {$toEmail}: " . $e->getMessage());
                        $emailsFailed++;
                    }
                }

                if ($emailsSent > 0) {
                    $successMessage = "Newsletter sent to {$emailsSent} subscribers successfully. " .
                                      ($emailsFailed > 0 ? "({$emailsFailed} emails failed to send)." : "");
                    // Clear form fields and reset body to default content on success
                    $subject = '';
                    $body = $defaultBodyContent;
                    $selectedSubscriberEmails = []; // Clear selections
                } else {
                    $errorMessage = "No emails were sent. " .
                                    ($emailsFailed > 0 ? "({$emailsFailed} emails failed to send)." : "Check subscriber selections.");
                }
            }
        }

        // Post/Redirect/Get pattern
        $redirectUrl = $link->getUrl('/admin/compose-newsletter');
        if (empty($errorMessage)) {
             header('Location: ' . $redirectUrl . '?status=success');
        }
        exit();
    }
    
    // Check for status messages after redirect (only for success from PRG)
    if (isset($_GET['status']) && $_GET['status'] === 'success') {
        $successMessage = "Newsletter sending process completed. Please check server logs for details if any failures occurred.";
        // Ensure body is also reset to default after a successful redirect
        $body = $defaultBodyContent;
    }

} catch (Exception $e) {
    error_log('Error on compose-newsletter page: ' . $e->getMessage());
    $errorMessage = 'An internal error occurred. Please try again later.';
}

// Set a page title for the header if desired
$pageTitle = "Admin Panel - Compose Newsletter";
$_TSCRIPTS = "<script src=\"" . LOGIN_URL . "/js/vendor/tinymce/tinymce.min.js\" referrerpolicy=\"origin\"></script>
  <script>
    tinymce.init({
      selector: '#body',
      height:600,
      license_key: 'gpl',
      plugins: 'lists image link code codesample charmap',
      toolbar: 'undo redo | bold italic underline subscript superscript | charmap | bullist numlist | link image codesample | code',
      codesample_languages: [
        { text: 'HTML', value: 'html' },
        { text: 'JavaScript', value: 'javascript' },
        { text: 'CSS', value: 'css' },
        { text: 'PHP', value: 'php' }
      ],
      convert_urls : false,
      automatic_uploads: true,
      extended_valid_elements: 'i[class, style],span[class, style]',
      images_upload_url: '" . LOGIN_URL . "/admin/upload-image',
      images_file_types: 'jpg,svg,webp,png,gif,bmp',
      sandbox_iframes: false,
    });
  </script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('selectAllSubscribers');
        const subscriberCheckboxes = document.querySelectorAll('input[name=\"selected_subscribers[]\"]');

        selectAllCheckbox.addEventListener('change', function() {
            subscriberCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });

        // Optional: If individual checkboxes are unchecked, uncheck 'Select All'
        subscriberCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (!this.checked) {
                    selectAllCheckbox.checked = false;
                } else {
                    // Check if all are now checked, then check 'Select All'
                    const allChecked = Array.from(subscriberCheckboxes).every(cb => cb.checked);
                    selectAllCheckbox.checked = allChecked;
                }
            });
        });
    });
  </script>";

include APP_DIR . '/admin/header-auth.php';
?>
<div class="container">
    <h2 class="mb-4">Compose Newsletter</h2>

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

    <p class="mb-4">
        Total Subscribers: <span class="badge bg-primary fs-6"><?php echo count($allSubscribers); ?></span>
        Verified Subscribers: <span class="badge bg-success fs-6"><?php echo count(array_filter($allSubscribers, function($s){ return $s['is_verified'] == 1; })); ?></span>
    </p>

    <form method="POST" action="<?php echo $link->getUrl('/admin/compose-newsletter'); ?>">
        <div class="mb-3">
            <label for="subject" class="form-label">Subject</label>
            <input type="text" class="form-control" id="subject" name="subject" value="<?php echo htmlspecialchars($subject); ?>" required>
        </div>
        <div class="mb-3">
            <label for="body" class="form-label">Email Body (HTML Editor)</label>
            <textarea class="form-control" id="body" name="body" rows="20"><?php echo $body; ?></textarea>
            <small class="form-text text-muted">Use <code>%full_name%</code> for personalization and <code>%unsubscribe_link%</code> to insert the unsubscribe link.</small>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h4>Select Recipients</h4>
            </div>
            <div class="card-body">
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="selectAllSubscribers">
                    <label class="form-check-label" for="selectAllSubscribers">
                        <strong>Select All Subscribers</strong>
                    </label>
                </div>
                <div class="subscriber-list" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 5px;">
                    <?php if (empty($subscribersToDisplay)): ?>
                        <p class="text-muted">No subscribers available for selection.</p>
                    <?php else: ?>
                        <?php foreach ($subscribersToDisplay as $subscriber): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       name="selected_subscribers[]" 
                                       value="<?php echo htmlspecialchars($subscriber['email']); ?>" 
                                       id="subscriber_<?php echo htmlspecialchars($subscriber['id']); ?>"
                                       <?php echo (in_array($subscriber['email'], $selectedSubscriberEmails) ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="subscriber_<?php echo htmlspecialchars($subscriber['id']); ?>">
                                    <?php echo htmlspecialchars($subscriber['full_name'] ?: 'N/A'); ?> (<?php echo htmlspecialchars($subscriber['email']); ?>)
                                    <?php if ($subscriber['is_verified']): ?>
                                        <span class="badge bg-success ms-2">Verified</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark ms-2">Not Verified</span>
                                    <?php endif; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <button type="submit" name="send_newsletter" class="btn btn-primary">Send Newsletter</button>
    </form>
</div>

<?php include APP_DIR . '/admin/footer-auth.php'; ?>
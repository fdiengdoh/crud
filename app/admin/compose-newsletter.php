<?php
// app/admin/compose-newsletter.php

use App\Helpers\AuthHelper;
use App\Controllers\SubscribersController;
use App\Mailer; // Assuming your Mailer class is in the App\ namespace

// Ensure only admin users can access this page.
AuthHelper::requireAdmin($auth, $link->getUrl('/user'));

// --- PRE-FORMATTED NEWSLETTER CONTENT ---
// This is a simple HTML email template with placeholders for dynamic content like %full_name% and %unsubscribe_link%. You can customize this template as needed.
// Taken from https://github.com/leemunroe/responsive-html-email-template and modified for our use case.
$defaultBodyContent = <<<HTML
<!doctype html>
<html lang="en">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Simple Transactional Email</title>
    <style media="all" type="text/css">
@media all {
  .btn-primary table td:hover {
    background-color: #ec0867 !important;
  }

  .btn-primary a:hover {
    background-color: #ec0867 !important;
    border-color: #ec0867 !important;
  }
}
@media only screen and (max-width: 640px) {
  .main p,
.main td,
.main span {
    font-size: 16px !important;
  }

  .wrapper {
    padding: 8px !important;
  }

  .content {
    padding: 0 !important;
  }

  .container {
    padding: 0 !important;
    padding-top: 8px !important;
    width: 100% !important;
  }

  .main {
    border-left-width: 0 !important;
    border-radius: 0 !important;
    border-right-width: 0 !important;
  }

  .btn table {
    max-width: 100% !important;
    width: 100% !important;
  }

  .btn a {
    font-size: 16px !important;
    max-width: 100% !important;
    width: 100% !important;
  }
}
@media all {
  .ExternalClass {
    width: 100%;
  }

  .ExternalClass,
.ExternalClass p,
.ExternalClass span,
.ExternalClass font,
.ExternalClass td,
.ExternalClass div {
    line-height: 100%;
  }

  .apple-link a {
    color: inherit !important;
    font-family: inherit !important;
    font-size: inherit !important;
    font-weight: inherit !important;
    line-height: inherit !important;
    text-decoration: none !important;
  }

  #MessageViewBody a {
    color: inherit;
    text-decoration: none;
    font-size: inherit;
    font-family: inherit;
    font-weight: inherit;
    line-height: inherit;
  }
}
</style>
  </head>
  <body style="font-family: Helvetica, sans-serif; -webkit-font-smoothing: antialiased; font-size: 16px; line-height: 1.3; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; background-color: #f4f5f6; margin: 0; padding: 0;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #f4f5f6; width: 100%;" width="100%" bgcolor="#f4f5f6">
      <tr>
        <td style="font-family: Helvetica, sans-serif; font-size: 16px; vertical-align: top;" valign="top">&nbsp;</td>
        <td class="container" style="font-family: Helvetica, sans-serif; font-size: 16px; vertical-align: top; max-width: 600px; padding: 0; padding-top: 24px; width: 600px; margin: 0 auto;" width="600" valign="top">
          <div class="content" style="box-sizing: border-box; display: block; margin: 0 auto; max-width: 600px; padding: 0;">

            <!-- START CENTERED WHITE CONTAINER -->
            <span class="preheader" style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;">This is preheader text. Some clients will show this text as a preview.</span>
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="main" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background: #ffffff; border: 1px solid #eaebed; border-radius: 16px; width: 100%;" width="100%">

              <!-- START MAIN CONTENT AREA -->
              <tr>
                <td class="wrapper" style="font-family: Helvetica, sans-serif; font-size: 16px; vertical-align: top; box-sizing: border-box; padding: 24px;" valign="top">
                  <p style="font-family: Helvetica, sans-serif; font-size: 16px; font-weight: normal; margin: 0; margin-bottom: 16px;">Hi there %full_name%</p>
                  <p style="font-family: Helvetica, sans-serif; font-size: 16px; font-weight: normal; margin: 0; margin-bottom: 16px;">Sometimes you just want to send a simple HTML email with a simple design and clear call to action. This is it.</p>
                  <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; box-sizing: border-box; width: 100%; min-width: 100%;" width="100%">
                    <tbody>
                      <tr>
                        <td align="left" style="font-family: Helvetica, sans-serif; font-size: 16px; vertical-align: top; padding-bottom: 16px;" valign="top">
                          <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: auto;">
                            <tbody>
                              <tr>
                                <td style="font-family: Helvetica, sans-serif; font-size: 16px; vertical-align: top; border-radius: 4px; text-align: center; background-color: #0867ec;" valign="top" align="center" bgcolor="#0867ec"> <a href="http://htmlemail.io" target="_blank" style="border: solid 2px #0867ec; border-radius: 4px; box-sizing: border-box; cursor: pointer; display: inline-block; font-size: 16px; font-weight: bold; margin: 0; padding: 12px 24px; text-decoration: none; text-transform: capitalize; background-color: #0867ec; border-color: #0867ec; color: #ffffff;">Call To Action</a> </td>
                              </tr>
                            </tbody>
                          </table>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  <p style="font-family: Helvetica, sans-serif; font-size: 16px; font-weight: normal; margin: 0; margin-bottom: 16px;">This is a really simple email template. It's sole purpose is to get the recipient to click the button with no distractions.</p>
                  <p style="font-family: Helvetica, sans-serif; font-size: 16px; font-weight: normal; margin: 0; margin-bottom: 16px;">Good luck! Hope it works.</p>
                </td>
              </tr>

              <!-- END MAIN CONTENT AREA -->
              </table>

            <!-- START FOOTER -->
            <div class="footer" style="clear: both; padding-top: 24px; text-align: center; width: 100%;">
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;" width="100%">
                <tr>
                  <td class="content-block" style="font-family: Helvetica, sans-serif; vertical-align: top; color: #9a9ea6; font-size: 16px; text-align: center;" valign="top" align="center">
                    <span class="apple-link" style="color: #9a9ea6; font-size: 16px; text-align: center;">Company Inc, 7-11 Commercial Ct, Belfast BT1 2NB</span>
                    <br> Don't like these emails? <a href="%unsubscribe_link%" style="text-decoration: underline; color: #9a9ea6; font-size: 16px; text-align: center;">Unsubscribe</a>.
                  </td>
                </tr>
                <tr>
                  <td class="content-block powered-by" style="font-family: Helvetica, sans-serif; vertical-align: top; color: #9a9ea6; font-size: 16px; text-align: center;" valign="top" align="center">
                    Powered by <a href="http://htmlemail.io" style="color: #9a9ea6; font-size: 16px; text-align: center; text-decoration: none;">HTMLemail.io</a>
                  </td>
                </tr>
              </table>
            </div>

            <!-- END FOOTER -->
            
<!-- END CENTERED WHITE CONTAINER --></div>
        </td>
        <td style="font-family: Helvetica, sans-serif; font-size: 16px; vertical-align: top;" valign="top">&nbsp;</td>
      </tr>
    </table>
  </body>
</html>
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

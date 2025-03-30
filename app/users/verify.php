<?php
// app/users/verify.php
require_once __DIR__ . '/../../init.php';


// Retrieve selector and token from URL query parameters
$selector = $_GET['selector'] ?? '';
$token    = $_GET['token'] ?? '';

$message = '';

if (empty($selector) || empty($token)) {
    $message = "Invalid verification link.";
} else {
    try {
        $auth->confirmEmail($selector, $token);
        $message = "Email verified successfully. You can now log in.";
    } catch (\Delight\Auth\InvalidSelectorTokenPairException $e) {
        $message = "Invalid verification link.";
    } catch (\Delight\Auth\TokenExpiredException $e) {
        $message = "Verification link has expired.";
    } catch (\Delight\Auth\EmailAlreadyVerifiedException $e) {
        $message = "Email already verified. Please log in.";
    } catch (\Delight\Auth\TooManyRequestsException $e) {
        $message = "Too many requests. Please try again later.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 600px;
            margin: 50px auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Email Verification</h1>
        <div class="alert alert-info" role="alert">
            <?php echo htmlspecialchars($message ?? ' ', ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <a href="<?= $link->getUrl('/users/login') ?>" class="btn btn-primary">Go to Login</a>
    </div>
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

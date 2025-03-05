<?php
// public/reset-password.php
require_once __DIR__ . '/../init.php';

use App\Controllers\AuthController;

$authController = new AuthController();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selector    = trim($_POST['selector'] ?? '');
    $token       = trim($_POST['token'] ?? '');
    $newPassword = trim($_POST['newPassword'] ?? '');
    $message     = $authController->resetPassword($selector, $token, $newPassword);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .form-container { max-width: 500px; margin: 50px auto; }
  </style>
</head>
<body>
  <div class="container form-container">
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
      <div class="alert alert-info" role="alert">
        <?php echo htmlspecialchars($message); ?>
      </div>
      <a href="<?= BASE_URL ?>/login" class="btn btn-primary">Back to Login</a>
    <?php else: 
      // For GET, retrieve selector and token from query parameters.
      $selector = trim($_GET['selector'] ?? '');
      $token    = trim($_GET['token'] ?? '');
    ?>
      <h1 class="mb-4">Reset Password</h1>
      <form id="resetPasswordForm" method="post" action="" class="needs-validation" novalidate>
        <input type="hidden" name="selector" value="<?php echo htmlspecialchars($selector); ?>">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <div class="mb-3">
          <label for="newPassword" class="form-label">New Password</label>
          <input type="password" class="form-control" id="newPassword" name="newPassword" placeholder="New Password" required minlength="6">
          <div class="invalid-feedback">
            Please provide a new password (minimum 6 characters).
          </div>
        </div>
        <button type="submit" class="btn btn-primary">Reset Password</button>
      </form>
    <?php endif; ?>
  </div>
  
  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (function () {
      'use strict'
      const form = document.getElementById('resetPasswordForm');
      if (form) {
        form.addEventListener('submit', function (event) {
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
          }
          form.classList.add('was-validated');
        }, false);
      }
    })();
  </script>
</body>
</html>

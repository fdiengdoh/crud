<?php
// public/login.php
require_once __DIR__ . '/../../init.php';
use App\Controllers\AuthController;

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $authController = new AuthController();
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    // Check if "remember me" was checked
    $remember = isset($_POST['remember']) && $_POST['remember'] === 'on';
    $message  = $authController->login($email, $password, $remember);
    if($message == "success"){
      header("Location: " . BASE_URL . "/my-posts");
      exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .form-container { max-width: 500px; margin: 50px auto; }
  </style>
</head>
<body>
  <div class="container form-container">
    <?php if (!empty($message)): ?>
      <div class="alert alert-info" role="alert">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>
    <h1 class="mb-4">Login</h1>
    <form id="loginForm" method="post" action="" class="needs-validation" novalidate>
      <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>
        <div class="invalid-feedback">Please provide a valid email.</div>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
        <div class="invalid-feedback">Please provide your password.</div>
      </div>
      <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="remember" name="remember">
        <label class="form-check-label" for="remember">Remember Me</label>
      </div>
      <div class="mb-3">
        <a href="<?= BASE_URL ?>/forgot-password" class="small">Forgot Password?</a>
      </div>
      <button type="submit" class="btn btn-primary">Login</button>
    </form>
  </div>
  
  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (function () {
      'use strict'
      const form = document.getElementById('loginForm');
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    })();
  </script>
</body>
</html>

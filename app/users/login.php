<?php
// users/login.php
use App\Controllers\AuthController;
use App\Helpers\CsrfHelper; // Import the Helper

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validate CSRF Token
    if (!CsrfHelper::isValid($_POST['csrf_token'] ?? '')) {
        $message = "Security token mismatch. Please refresh the page.";
    } else {
        $authController = new AuthController();
        $email    = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        
        // Check if "remember me" was checked
        $remember = isset($_POST['remember']) && $_POST['remember'] === 'on';
        
        // Attempt login
        $result = $authController->login($email, $password, $remember);
        
        if ($result === "success") {
            header("Location: " . $link->getUrl('/users'));
            exit;
        } else {
            $message = $result; // Show the error from the controller
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    .form-container { 
        max-width: 450px; 
        margin: 80px auto; 
        background: white; 
        padding: 30px; 
        border-radius: 10px; 
        shadow: 0 4px 6px rgba(0,0,0,0.1); 
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="form-container border shadow-sm">
        <?php if (!empty($message)): ?>
          <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
          </div>
        <?php endif; ?>

        <h1 class="mb-4 text-center">Login</h1>
        
        <form id="loginForm" method="post" action="" class="needs-validation" novalidate>
          <input type="hidden" name="csrf_token" value="<?= $csrfToken; ?>">

          <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required autofocus>
            <div class="invalid-feedback">Please provide a valid email.</div>
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            <div class="invalid-feedback">Please provide your password.</div>
          </div>

          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check">
              <input type="checkbox" class="form-check-input" id="remember" name="remember">
              <label class="form-check-label" for="remember">Remember Me</label>
            </div>
            <a href="<?= $link->getUrl('/users/forgot-password') ?>" class="small text-decoration-none">Forgot Password?</a>
          </div>

          <button type="submit" class="btn btn-primary w-100 py-2">Sign In</button>
        </form>
    </div>
  </div>
  
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
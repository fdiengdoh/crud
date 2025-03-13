<?php
// public/register.php
require_once __DIR__ . '/../../init.php';

use App\Controllers\AuthController;

// Initialize message variable
$message = '';

// If the form is submitted, process registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $authController = new AuthController();
    
    // Retrieve and sanitize form input
    $email    = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $verificationLink = $link->getUrl('/users/verify');
    
    // Call the register method from the controller
    $message = $authController->register($email, $username, $password, $verificationLink);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* Optional: center the form on the page */
    .form-container {
      max-width: 500px;
      margin: 50px auto;
    }
  </style>
</head>
<body>
  <div class="container form-container">
    <?php if (!empty($message)): ?>
      <div class="alert alert-info" role="alert">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <h1 class="mb-4">Register</h1>
    <form id="registerForm" method="post" action="" class="needs-validation" novalidate>
      <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <input 
          type="email" 
          class="form-control" 
          id="email" 
          name="email" 
          placeholder="Enter email" 
          required>
        <div class="invalid-feedback">
          Please provide a valid email.
        </div>
      </div>
      
      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input 
          type="text" 
          class="form-control" 
          id="username" 
          name="username" 
          placeholder="Enter username" 
          required>
        <div class="invalid-feedback">
          Please provide a username.
        </div>
      </div>
      
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input 
          type="password" 
          class="form-control" 
          id="password" 
          name="password" 
          placeholder="Password" 
          required minlength="6">
        <div class="invalid-feedback">
          Please provide a password (minimum 6 characters).
        </div>
      </div>
      
      <button type="submit" class="btn btn-primary">Register</button>
    </form>
  </div>
  
  <!-- Bootstrap 5 JS Bundle (includes Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Bootstrap 5 custom validation
    (function () {
      'use strict'
      const form = document.getElementById('registerForm');
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

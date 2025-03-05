<?php
// public/install.php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Database;
use Delight\Auth\Auth;
use App\Controllers\CategoryController;

// Load environment variables from .env in the project root
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// If the form is not submitted, display the installation form with Bootstrap styling.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <title>Application Installation</title>
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <!-- Bootstrap 5 CSS -->
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
      <div class="container mt-5">
        <div class="card shadow-sm">
          <div class="card-header">
            <h2 class="mb-0">Install Application</h2>
          </div>
          <div class="card-body">
            <form method="post" action="">
              <div class="mb-3">
                <label for="admin_email" class="form-label">Admin Email:</label>
                <input type="email" name="admin_email" id="admin_email" class="form-control" required>
              </div>
              <div class="mb-3">
                <label for="admin_username" class="form-label">Admin Username:</label>
                <input type="text" name="admin_username" id="admin_username" class="form-control" required>
              </div>
              <div class="mb-3">
                <label for="admin_password" class="form-label">Admin Password:</label>
                <input type="password" name="admin_password" id="admin_password" class="form-control" required>
              </div>
              <div class="d-grid">
                <button type="submit" class="btn btn-primary">Install</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      
      <!-- Bootstrap 5 JS Bundle (includes Popper) -->
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
    exit;
}

// Process installation after form submission

// Retrieve admin credentials from user input
$adminEmail    = trim($_POST['admin_email']);
$adminUsername = trim($_POST['admin_username']);
$adminPassword = trim($_POST['admin_password']);

// Retrieve database connection details from .env
$host       = $_ENV['DB_HOST'] ?? 'localhost';
$dbName     = $_ENV['DB_NAME'] ?? '';
$dbUser     = $_ENV['DB_USER'] ?? '';
$dbPassword = $_ENV['DB_PASS'] ?? '';
$featuredCategory = $_ENV['FEATURED_POST'] ?? 'featured-post';

if (empty($dbName) || empty($dbUser)) {
    die("Database name and user must be set in the .env file.");
}

// Establish a PDO connection using the .env settings
try {
    $dsn = "mysql:host={$host};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPassword, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// 1. Install database authentication tables using the modified SQL file from delight-im/auth
$coreSqlFile = __DIR__ . '/../MySQL.sql';
if (!file_exists($coreSqlFile)) {
    die("Database installation SQL file not found at: {$coreSqlFile}");
}
$coreSqlContent = file_get_contents($coreSqlFile);
if ($coreSqlContent === false) {
    die("Failed to read the Database installation SQL file.");
}
$coreSqlCommands = array_filter(array_map('trim', explode(';', $coreSqlContent)));
try {
    foreach ($coreSqlCommands as $command) {
        if (!empty($command)) {
            $pdo->exec($command);
        }
    }
    echo '<div class="container mt-5"><div class="alert alert-success">Database tables installed successfully.</div>';
} catch (PDOException $e) {
    die("Error installing database tables: " . $e->getMessage());
}

// 2. Create the default admin user using delight-im/auth
$auth = new Auth($pdo);
try {
    $verificationLink = '';
    $userId = $auth->register($adminEmail, $adminPassword, $adminUsername, function ($selector, $token) use (&$verificationLink) {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $verificationLink = $protocol . $host . "/verify?selector=" . urlencode($selector) . "&token=" . urlencode($token);
    });
    
    // Attempt auto-confirmation (if desired; otherwise, manual verification)
    try {
        $auth->confirmEmail($selector, $token);
    } catch (\Exception $e) {
        echo '<div class="alert alert-warning">Error: Unable to auto-verify new admin user, please use the link below to manually verify.</div>';
    }
    
    // Assign default admin role (modify as needed)
    $auth->admin()->addRoleForUserById($userId, \Delight\Auth\Role::ADMIN);
    
    echo '<div class="alert alert-success">Default admin user created with ID: ' . $userId . '</div>';
    echo '<div class="alert alert-info">Verification link: <a href="' . $verificationLink . '">' . $verificationLink . '</a></div>';
} catch (\Delight\Auth\InvalidEmailException $e) {
    die('<div class="alert alert-danger">Error: Invalid email address.</div>');
} catch (\Delight\Auth\InvalidPasswordException $e) {
    die('<div class="alert alert-danger">Error: Invalid password.</div>');
} catch (\Delight\Auth\UserAlreadyExistsException $e) {
    echo '<div class="alert alert-warning">Default admin user already exists.</div>';
} catch (\Delight\Auth\TooManyRequestsException $e) {
    die('<div class="alert alert-danger">Error: Too many requests. Please try again later.</div>');
}

try{
  $category = new CategoryController();
  $category->createCategory($featuredCategory,$featuredCategory);
  echo '<div class="alert alert-success">Featured Category Created with : ' . $featuredCategory . '</div>';
}catch(\Exception $e){
  echo '<div class="alert alert-danger">Unable to Create Category</div>';
}

echo '<br><br><div class="container"><div class="alert alert-success">Installation complete. For security, please remove or secure this script.</div></div>';

<?php
/**
 * init.php
 *
 * Central initialization for all public files.
 * - Sets error reporting based on environment.
 * - Loads Composer’s autoloader.
 * - Loads environment variables.
 * - Sets default timezone.
 * - Initializes common objects: DB connection and Auth instance.
 * - Auto-defines all environment variables as constants.
 */
// Load Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Define paths for convenience
define('ROOT_DIR', __DIR__);
define('APP_DIR', ROOT_DIR . '/app');
define('PUBLIC_DIR', ROOT_DIR . '/public_html');

// Load environment variables from the .env file located in the project root
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


// Auto-define all environment variables as constants
foreach ($_ENV as $key => $value) {
    if (!defined($key)) {
        define($key, $value);
    }
}

// Define environment constant: 'development' or 'production'
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development'); // Change to 'production' on live servers.
}

// Configure error reporting
if (ENVIRONMENT === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} elseif (ENVIRONMENT === 'live') {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', ROOT_DIR . '/logs/error.log');
}

// Optionally, ensure BASE_URL is defined (fallback to 'http://localhost' if not set)
if (!defined('BASE_URL')) {
    define('BASE_URL', $_ENV['BASE_URL'] ?? 'http://localhost');
}

// defining what categories to show in home page
define('HOME_CATEGORIES', explode(",", HOME_POST));

// Set the default timezone (adjust as needed)
date_default_timezone_set('UTC');

// Initialize common objects

use App\Database;
$pdo = Database::getConnection();

use Delight\Auth\Auth;
$auth = new Auth($pdo);

// Now, $pdo, $auth, and all .env variables (as constants) are available globally.

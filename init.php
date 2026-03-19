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

//Set cookie to work on subdomains if needed
//\ini_set('session.cookie_domain','example.com');

// Define paths for convenience
define('ROOT_DIR', __DIR__);
define('APP_DIR', ROOT_DIR . '/app');
define('PUBLIC_DIR', ROOT_DIR . '/public_html');
define('CACHE_CONFIG', ROOT_DIR . '/cache_config/config.php');
define('CACHE_DIR', ROOT_DIR . '/cache_config/cache');

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

// 1. Set Error Handler to log url
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    // Check if error reporting is disabled for this type of error
    if (!(error_reporting() & $errno)) {
        return false;
    }

    // Capture the current URL, checking if it's available
    $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'CLI_OR_UNKNOWN_URI';

    // Format the error message to include the URL
    $log_message = sprintf(
        "[%s] URL: %s | PHP Error: %s in %s on line %d\n",
        date('Y-m-d H:i:s'),
        $request_uri,
        $errstr,
        $errfile,
        $errline
    );

    // Append the formatted message to the log file
    error_log($log_message, 3, ROOT_DIR . '/logs/error.log');

    // Prevent PHP's default error handler from running
    return true;
}
// 2. Set the custom error handler
set_error_handler("customErrorHandler");

// Configure error reporting
if (ENVIRONMENT === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} elseif (ENVIRONMENT === 'live') {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    error_reporting(E_ALL);
    ini_set('error_log', ROOT_DIR . '/logs/error.log');
}

// Optionally, ensure BASE_URL is defined (fallback to 'http://localhost' if not set)
if (!defined('BASE_URL')) {
    define('BASE_URL', $_ENV['BASE_URL'] ?? 'http://localhost');
}
define('HOME_CATEGORIES', explode(",", HOME_POST));

// Set the default timezone (adjust as needed)
date_default_timezone_set('Asia/Kolkata');

// Initialize common objects

use App\Database;
$pdo = Database::getConnection();

use Delight\Auth\Auth;
$auth = new Auth($pdo);

// Now, $pdo, $auth, and all .env variables (as constants) are available globally.

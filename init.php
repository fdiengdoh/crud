<?php
/**
 * Sample init.php file
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

//Set cookie to work on subdomains if you want to
//\ini_set('session.cookie_domain','yourdomain.com');

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

//Add routes
use App\Helpers\Link;
$link = new Link();

//Sample links for routing via index page
$link->routes = [
    ''                          => ['url' => BASE_URL . '/', 'file' => APP_DIR . '/home.php'],
    '/'                         => ['url' => BASE_URL . '/', 'file' => APP_DIR . '/home.php'],
    //Admin Area
    '/admin/categories'         => ['url' => LOGIN_URL . '/admin/categories', 'file' => APP_DIR . '/admin/admin-categories.php'],
    '/admin/flag-comment'       => ['url' => LOGIN_URL . '/admin/flag-comment', 'file' => APP_DIR . '/admin/admin-flag-comment.php'],
    '/admin'                    => ['url' => LOGIN_URL . '/admin', 'file' => APP_DIR . '/admin/admin-panel.php'],
    '/admin/upload-image'       => ['url' => LOGIN_URL . '/admin/upload-image', 'file' => APP_DIR . '/admin/upload-image.php'],
    '/admin/view-logs'          => ['url' => LOGIN_URL . '/admin/view-logs', 'file' => APP_DIR . '/admin/view-logs.php'],

    //Users Area
    '/users'                    => ['url' => LOGIN_URL . '/users', 'file' => APP_DIR . '/users/my-posts.php'],
    '/users/edit-profile'       => ['url' => LOGIN_URL . '/users/edit-profile', 'file' => APP_DIR . '/users/edit-profile.php'],
    '/users/forgot-password'    => ['url' => LOGIN_URL . '/users/forgot-password', 'file' => APP_DIR . '/users/forgot-password.php'],
    '/users/post-create'        => ['url' => LOGIN_URL . '/users/post-create', 'file' => APP_DIR . '/users/post-create.php'],
    '/users/post-delete'        => ['url' => LOGIN_URL . '/users/post-delete', 'file' => APP_DIR . '/users/post-delete.php'],
    '/users/post-edit'          => ['url' => LOGIN_URL . '/users/post-edit', 'file' => APP_DIR . '/users/post-edit.php'],
    '/users/reset-password'     => ['url' => LOGIN_URL . '/users/reset-password', 'file' => APP_DIR . '/users/reset-password.php'],
    '/users/register'           => ['url' => LOGIN_URL . '/users/register', 'file' => APP_DIR . '/users/register.php'],
    '/users/verify'             => ['url' => LOGIN_URL . '/users/verify', 'file' => APP_DIR . '/users/verify.php'],
    '/users/login'              => ['url' => LOGIN_URL . '/users/login', 'file' => APP_DIR . '/users/login.php' ],
    '/ajax-handler'             => ['url' => BASE_URL  . '/ajax-handler', 'file' => APP_DIR . '/users/ajax_handler.php'],

    //General Authentication Related
    
    '/logout'                   => ['url' => LOGIN_URL . '/logout', 'file' => null ],
    '/report-comment'           => ['url' => BASE_URL . '/report-comment', 'file' => APP_DIR . '/report-comment.php'],
];

<?php
// public/index.php
header('Content-type: text/html; charset=utf-8');
require_once __DIR__ . '/../init.php';  // Global initialization

use App\Helpers\Link;
use App\Utils\Cache;
use App\Controllers\AuthController;

// Load cache configuration and instantiate the Cache utility.
$config = require (CACHE_CONFIG);
$cache = new Cache($config);

// Get the current URL path.
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestUri = rtrim($requestUri, '/');

$TScripts = ''; // additional top scripts
$BScripts = ''; // additional bottom scripts

// Determine if we should force a refresh (e.g. via query param or header).
$forceRefresh = isset($_GET['refresh']) || (isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'no-cache');

// Only attempt to serve from cache for GET requests.
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !$forceRefresh && $cache->isCached($requestUri)) {
    echo $cache->getCache($requestUri);
    exit;
}

// Start output buffering to capture dynamic output.
ob_start();
if ($requestUri === '/logout') {
    $authController = new AuthController();
    $authController->logout();
    header("Location: " . $link->getUrl('/users/login'));
    exit;
}
// Handle static routes using the Link helper
if (array_key_exists($requestUri, $link->routes)) {
    // Special case: if request is '/login' and user is logged in, redirect to /my-posts.
    if ($requestUri === '/users/login' && isset($auth) && $auth->isLoggedIn()) {
        header("Location: " . $link->getUrl('/users'));
        exit;
    }
    require $link->getFile($requestUri);
    exit;
}

// Routing logic (static/dynamic routes) remains unchanged:
if ($requestUri === '' || $requestUri === '/') {
    require APP_DIR . '/home.php';
} elseif (strpos($requestUri, '/profile/') === 0) {
    $username = substr($requestUri, strlen('/profile/'));
    $_GET['username'] = $username;
    require APP_DIR . '/users/profile.php';
} elseif(strpos($requestUri, '/report-comment') === 0){
    require APP_DIR . '/report-comment.php';
} elseif (strpos($requestUri, '/search/label/') === 0) {
    // Remove the prefix
    $parts = explode('/', $requestUri);
    
    // Category URL structure: /search/label/{categoryname}/{page?}
    // $parts[0] is empty, $parts[1] is "search", $parts[2] is "label", $parts[3] is category name, $parts[4] is page no
    $categorySlug = $parts[3] ?? '';
    
    if (isset($parts[4]) && is_numeric($parts[4])) {
        //Get the page number
        $_GET['page'] = (int)$parts[4];
    } else {
        $_GET['page'] = 1;
    }
    $_GET['slug'] = $categorySlug;
    require APP_DIR . '/category.php';
} elseif ($requestUri === '/logout') {
    // Example: handle logout.
    require APP_DIR . '/logout.php';
} else {
    // Assume request is for a blog post.
    $_GET['slug'] = ltrim($requestUri, '/');
    require APP_DIR . '/single-post.php';
}

// End output buffering and capture output.
$output = ob_get_contents();
ob_end_flush();

// Only store cache for GET requests.
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !$forceRefresh && $config['cache_enabled']) {
    $cache->storeCache($requestUri, $output);
}

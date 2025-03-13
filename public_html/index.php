<?php
// public/index.php
header('Content-type: text/html; charset=utf-8');
require_once __DIR__ . '/../init.php';  // Global initialization

use App\Helpers\Link;
use App\Controllers\AuthController;

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestUri = rtrim($requestUri, '/');

// Instantiate the Link helper (which already contains our default routes)
//$link = new Link();

$TScripts = ''; // additional top scripts
$BScripts = ''; // additional bottom scripts

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

// Handle dynamic routes
if (strpos($requestUri, '/profile/') === 0) {
    // Public profile pages, e.g. /profile/username
    $username = substr($requestUri, strlen('/profile/'));
    $_GET['username'] = $username;
    require APP_DIR . '/users/profile.php';
    exit;
} elseif ($requestUri === '/profile') {
    // If exactly /profile, redirect logged-in user to their own profile, else redirect to login.
    if ($auth->isLoggedIn()) {
        $username = $auth->getUsername() ?? '';
        if (!empty($username)) {
            header("Location: " . BASE_URL . "/profile/" . urlencode($username));
            exit;
        }
    }
    header("Location: " . $link->getUrl('/users/login'));
    exit;
} elseif (strpos($requestUri, '/search/label/') === 0) {
    // Category pages
    $slug = substr($requestUri, strlen('/search/label/'));
    $_GET['slug'] = $slug;
    require APP_DIR . '/category.php';
    exit;
}else {
    // Assume the request is for a blog post (pretty URL handling)
    $_GET['slug'] = ltrim($requestUri, '/');
    require APP_DIR . '/single-post.php';
    exit;
}

// If no route matches, serve a 404 error page.
http_response_code(404);
require APP_DIR . '/404.php';
exit;

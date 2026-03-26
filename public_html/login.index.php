<?php
// public_html/index.php
/*
 * Sample index file for login app if you use a separate LOGIN_URL from BASE_URL in .env file
*/
header('Content-type: text/html; charset=utf-8');
//header("Access-Control-Allow-Origin: https://example.com");
require_once '/../init.php';  // Global initialization

use App\Controllers\AuthController; // Import AuthController for handling logout and authentication checks
use App\Helpers\Link; // Import Link helper for routing
use App\Controllers\PostController; // Import PostController for fetching recent and popular posts

// Get link ready for routing
$link = new Link();

$link->routes = [
    ''                          => ['url' => BASE_URL . '/', 'file' => APP_DIR . '/home.php'],
    '/'                         => ['url' => BASE_URL . '/', 'file' => APP_DIR . '/home.php'],
    //Admin Area
    '/admin/categories'         => ['url' => LOGIN_URL . '/admin/categories', 'file' => APP_DIR . '/admin/admin-categories.php'],
    '/admin/flag-comment'       => ['url' => LOGIN_URL . '/admin/flag-comment', 'file' => APP_DIR . '/admin/admin-flag-comment.php'],
    '/admin'                    => ['url' => LOGIN_URL . '/admin', 'file' => APP_DIR . '/admin/admin-panel.php'],
    '/admin/upload-image'       => ['url' => LOGIN_URL . '/admin/upload-image', 'file' => APP_DIR . '/admin/upload-image.php'],
    '/admin/view-logs'          => ['url' => LOGIN_URL . '/admin/view-logs', 'file' => APP_DIR . '/admin/view-logs.php'],
    '/admin/view-subscribers'   => ['url' => LOGIN_URL . '/admin/view-subscribers', 'file' => APP_DIR . '/admin/view-subscribers.php'],
    '/admin/compose-newsletter' => ['url' => LOGIN_URL . '/admin/compose-newsletter', 'file' => APP_DIR . '/admin/compose-newsletter.php'],

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
    '/subscriber'               => ['url' => BASE_URL . '/subscriber', 'file' => APP_DIR . '/subscriber.php'],
    '/sitemap.html'             => ['url' => BASE_URL  . '/sitemap.html', 'file' => APP_DIR . '/sitemap.php'],
];

// Get the PostController instances (needed for database calls)
$postController = new PostController();

$TScripts = ''; // additional top scripts
$BScripts = ''; // additional bottom scripts
$recentPosts = $postController->getRecentPosts(RECENT_POST); //Get recent posts for sidebar
$popularPosts = $postController->getPopularPosts(POPULAR_POST); //Get popular posts for sidebar

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestUri = rtrim($requestUri, '/');

// 1. Handle Logout first
if ($requestUri === '/logout') {
    (new AuthController())->logout();
    header("Location: " . $link->getUrl('/users/login'));
    exit;
}

$isLoggedIn = (isset($auth) && $auth->isLoggedIn());

// 2. ROOT DOMAIN LOGIC (login.fdiengdoh.com/)
if ($requestUri === '' || $requestUri === '/') {
    if ($isLoggedIn) {
        header("Location: " . $link->getUrl('/users')); 
    } else {
        require APP_DIR . '/users/login.php'; 
    }
    exit;
}

// 3. Define Publicly Accessible Routes
$publicRoutes = [
    '/users/login',
    '/users/register',
    '/users/forgot-password',
    '/users/reset-password',
    '/users/verify',
    '/ajax-handler',
    '/subscriber',
    '/report-comment'
];

// 4. ROUTING LOGIC (Static Routes)
if (array_key_exists($requestUri, $link->routes)) {
    // Redirect logged-in users away from login/register
    if ($isLoggedIn && ($requestUri === '/users/login' || $requestUri === '/users/register')) {
        header("Location: " . $link->getUrl('/users'));
        exit;
    }

    // Auth Check for Private Routes (Admin/User area)
    if (!in_array($requestUri, $publicRoutes) && !$isLoggedIn) {
        header("Location: " . $link->getUrl('/users/login'));
        exit;
    }

    $file = $link->getFile($requestUri);
    if ($file) {
        require $file;
        exit;
    }
}

// 5. Handle Dynamic Profiles
if (strpos($requestUri, '/profile/') === 0) {
    $_GET['username'] = substr($requestUri, strlen('/profile/'));
    require APP_DIR . '/users/profile.php';
    exit;
}

// 6. Handle Fallback (Blog Post Slugs)
if ($requestUri !== '') {
    $slug = ltrim($requestUri, '/');
    $postController = new PostController();
    
    // VALIDATION: Only require single-post if the slug exists in DB
    // This prevents "extra" URLs from loading a broken page
    if ($postController->show($slug)) { 
        $_GET['slug'] = $slug;
        require APP_DIR . '/single-post.php';
        exit;
    }
}

// 7. FINAL SAFETY NET: REDIRECT TO MAIN DOMAIN
// If it's not a route, not a profile, and not a valid post slug:
header("Location: " . BASE_URL);
exit();

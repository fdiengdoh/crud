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

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestUri = rtrim($requestUri, '/');
if ($requestUri === '/logout') {
    $authController = new AuthController();
    $authController->logout();
    header("Location: " . $link->getUrl('/users/login'));
    exit;
}elseif (isset($auth) && $auth->isLoggedIn()) {
    $TScripts = ''; // additional top scripts
    $BScripts = ''; // additional bottom scripts
    $recentPosts = $postController->getRecentPosts(RECENT_POST); //Get recent posts for sidebar
    $popularPosts = $postController->getPopularPosts(POPULAR_POST); //Get popular posts for sidebar
    
    if($requestUri === ''){
        header("Location: " . $link->getUrl('/users'));
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
    }else {
        // Assume the request is for a blog post (pretty URL handling)
        $_GET['slug'] = ltrim($requestUri, '/');
        require APP_DIR . '/single-post.php';
        exit;
    }
}elseif($requestUri === '/users/login' || $requestUri  === ''){
    require $link->getFile('/users/login');
    exit;
}else{
    header("Location: " . BASE_URL );
}

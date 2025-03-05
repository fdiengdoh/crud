<?php
// public/index.php

require_once __DIR__ . '/../init.php';  // Global initialization

use App\Controllers\AuthController;

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestUri = rtrim($requestUri, '/');

// Use a switch-case to handle explicit routes
switch ($requestUri) {
    case '':
    case '/':
        require APP_DIR . '/home.php';
        exit;
    case '/login':
        if (!isset($auth) || !$auth->isLoggedIn()) {
            require APP_DIR . '/admin/login.php';
            exit;
        } else {
            header("Location: " . BASE_URL . "/my-posts");
            exit;
        }
    case '/register':
        require APP_DIR . '/register.php';
        exit;
    case '/verify':
        require APP_DIR . '/verify.php';
        exit;
    case '/forgot-password':
        require APP_DIR . '/forgot-password.php';
        exit;
    case '/reset-password':
        require APP_DIR . '/reset-password.php';
        exit;
    case '/my-posts':
        require APP_DIR . '/admin/my-posts.php';
        exit;
    case '/admin':
        require APP_DIR . '/admin/admin-panel.php';
        exit;
    case '/admin/categories':
        require APP_DIR . '/admin/admin-categories.php';
        exit;
    case '/logout':
        $authController = new AuthController();
        $authController->logout();
        header("Location: /login");
        exit;
    case '/post-create':
        require APP_DIR . '/admin/post-create.php';
        exit;
    case '/post-edit':
        require APP_DIR . '/admin/post-edit.php';
        exit;
    case '/post-delete':
        require APP_DIR . '/admin/post-delete.php';
        exit;
    case '/admin/edit-profile':
        require APP_DIR . '/admin/edit-profile.php';
        exit;
    case '/report-comment':
        require APP_DIR . '/report-comment.php';
        exit;
    case '/admin/flag-comment':
        require APP_DIR . '/admin/admin-flag-comment.php';
        exit;
    case '/admin/upload-image':
        require APP_DIR . '/admin/upload-image.php';
        exit;
    // If the URL is exactly '/profile'
    case '/profile':
        if ($auth->isLoggedIn()) {
            // Redirect logged-in users to their own public profile page
            // Assumes you have a way to get the username, e.g., $auth->getUsername() (adjust as necessary)
            $username = $auth->getUsername() ?? '';
            if (!empty($username)) {
                header("Location: " . BASE_URL . "/profile/" . urlencode($username));
                exit;
            }
        }
        // If not logged in, redirect to login page
        header("Location: " . BASE_URL . "/login");
        exit;
    default:
        // Check if URL starts with /profile/ for public user profiles
        if (strpos($requestUri, '/profile/') === 0) {
            $username = substr($requestUri, strlen('/profile/'));
            $_GET['username'] = $username;
            require APP_DIR . '/profile.php';
            exit;
        }
        // Check if URL starts with /search/label/ for categories (if needed)
        if (strpos($requestUri, '/search/label/') === 0) {
            $slug = substr($requestUri, 14); // remove '/search/label/' prefix
            $_GET['slug'] = $slug;
            require APP_DIR . '/category.php';
            exit;
        }
        // Otherwise, treat the URL as a post slug (pretty URL without any prefix)
        $_GET['slug'] = ltrim($requestUri, '/');
        require APP_DIR . '/single-post.php';
        exit;
}

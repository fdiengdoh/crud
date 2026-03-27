<?php
// ajax_handler.php
header('Content-Type: application/json');

// It is highly recommended to replace '*' with your actual domain for better security
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

use App\Controllers\PostController;
use App\Controllers\SubscribersController;
use App\Helpers\CsrfHelper;
use App\Helpers\RateLimitHelper;

$response = ['success' => false, 'message' => 'Invalid request.'];

try {
    // --- 1. HANDLE SUBSCRIPTION (POST + CSRF) ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subscribe'])) {
        // Check Rate Limit: Only 3 subscription attempts per 10 minutes per IP
        if (!RateLimitHelper::isAllowed('subscribe', 3, 600)) {
            http_response_code(429); // Too Many Requests
            echo json_encode(['success' => false, 'message' => 'Too many requests. Please try again in 10 minutes.']);
            exit;
        }
        
        // CSRF VALIDATION
        if (!CsrfHelper::isValid($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Security token expired. Please refresh.']);
            exit;
        }

        $email = htmlspecialchars(trim($_POST['email'] ?? ''));
        $full_name = htmlspecialchars(trim($_POST['full_name'] ?? ''));

        if (empty($email) || empty($full_name)) {
            echo json_encode(['success' => false, 'message' => 'Please provide name and email.']);
            exit;
        }

        $subControl = new SubscribersController();
        // Passing BASE_URL for the verification link in the email
        $response = $subControl->subscribe($email, $full_name, BASE_URL . '/subscriber');
    }

    // --- 2. HANDLE VIEW INCREMENT (GET) ---
    // Kept as GET to match your existing frontend logic
    else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['increment-view'])) {
        $postController = new PostController();
        $isAdminOrAuthor = $auth->hasRole(\Delight\Auth\Role::ADMIN) || $auth->hasRole(\Delight\Auth\Role::AUTHOR);
        $slug = $_GET['slug'] ?? null;
        
        if (!$slug) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing post identifier']);
            exit;
        }

        if (!$isAdminOrAuthor) {
            $post = $postController->show($slug);
            if (!isset($_SESSION['viewed_posts'])) {
                $_SESSION['viewed_posts'] = [];
            }
            
            $postKey = $post['slug'] ?? $slug;
            $today = date('Y-m-d');
            
            if ($post) {
                if (!isset($_SESSION['viewed_posts'][$postKey]) || $_SESSION['viewed_posts'][$postKey] !== $today) {
                    $_SESSION['viewed_posts'][$postKey] = $today;
                    $postController->incrementViews($post['id']);
                    $response = ['success' => true, 'views' => ($post['views'] + 1), 'message' => 'View incremented.'];
                } else {
                    $response = ['success' => true, 'views' => $post['views'], 'message' => 'Already viewed today.'];
                }
            } else {
                http_response_code(404);
                $response = ['success' => false, 'error' => 'Post not found.'];
            }
        } else {
            $post = $postController->show($slug);
            $response = ['success' => true, 'views' => ($post['views'] ?? '0'), 'message' => 'Admin view.'];
        }
    }
} catch (Exception $e) {
    error_log('AJAX Error: ' . $e->getMessage());
    http_response_code(500);
    $response = ['success' => false, 'message' => 'Server error occurred.'];
}

echo json_encode($response);
exit();

<?php
// ajax_handler.php
// 1. Set Content-Type Header and Handle CORS
header('Content-Type: application/json');

// IMPORTANT: In production, replace '*' with your actual frontend domain(s)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS'); // Allow GET for current setup
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With'); // Headers client might send

// Handle preflight requests for CORS (if using methods other than GET/POST with simple headers)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Initialize a default response array for all actions
$response = [
    'success' => false,
    'message' => 'An unknown error occurred or no action specified.'
];

use App\Controllers\PostController;         // Post Controller
use App\Controllers\SubscribersController;  // Subscribers Controller (NEW)

// --- Handle Different AJAX Actions ---
try {
    // --- Handle 'subscribe' action (NEW LOGIC) ---
    if (isset($_GET['subscribe']) && $_GET['subscribe'] === 'true') {
        // Retrieve and Sanitize Input Data
        $email = $_GET['email'] ?? '';
        $full_name = $_GET['full_name'] ?? '';

        $email = htmlspecialchars(trim($email));
        $full_name = htmlspecialchars(trim($full_name));

        // Instantiate SubscriberController (as per your example)
        $subControl = new SubscribersController(); // Assumes constructor handles DB connection

        // Call the subscribe method
        // return arrray ['success' => bool, 'message' => string]
        $response = $subControl->subscribe($email, $full_name, BASE_URL . '/subscriber');

    }
    // --- Handle 'increment-view' action (EXISTING LOGIC) ---
    else if(isset($_GET['increment-view']) ){
        //initialize first
        $postController = new PostController();

        // Check if the current user is an admin or author
        $isAdminOrAuthor = $auth->hasRole(\Delight\Auth\Role::ADMIN) || $auth->hasRole(\Delight\Auth\Role::AUTHOR);
        
        // Get the post ID or slug from the query string
        $slug = $_GET['slug'] ?? null;
        
        if (!$slug) {
            http_response_code(400); // Set HTTP status code for bad request
            echo json_encode(['success' => false, 'error' => 'Missing post identifier', 'views' => '0']);
            exit; // Exit immediately for bad requests
        }
        
        // Only count views for non-admin/author users
        if (!$isAdminOrAuthor) {
            //get post information
            $post = $postController->show($slug);
            // Initialize session storage for viewed posts if not set.
            // This is safe even if session_start() is global; it just ensures the array exists.
            if (!isset($_SESSION['viewed_posts'])) {
                $_SESSION['viewed_posts'] = [];
            }
            
            $postKey = $post['slug'];
            $today = date('Y-m-d');
            
            //set success to false unless true
            $sendvar = ['success' => false, 'views' => ($post['views'] ?? '0')]; // Ensure views is set, even if post is null
            
            if ($post) {
                // If this post hasn't been viewed today, then increment views.
                if (!isset($_SESSION['viewed_posts'][$postKey]) || $_SESSION['viewed_posts'][$postKey] !== $today) {
                    $_SESSION['viewed_posts'][$postKey] = $today;
                    $success = $postController->incrementViews($post['id']);
                    $sendvar = ['success' => true, 'views' => ($post['views'] + 1), 'message' => 'View incremented.'];
                } else {
                    // Post already viewed today, still success, return current views
                    $sendvar = ['success' => true, 'views' => $post['views'], 'message' => 'Post already viewed today.'];
                }
            } else {
                // Post not found for increment-view
                http_response_code(404); // Not Found
                $sendvar = ['success' => false, 'error' => 'Post not found.', 'views' => '0'];
            }
        } else {
            // Admin/Author, no increment, just send current views if post exists
            $post = $postController->show($slug);
            $sendvar = ['success' => true, 'views' => ($post['views'] ?? '0'), 'message' => 'Admin/Author view, no increment.'];
        }
        // Use $sendvar from the existing logic to populate the final $response
        $response = $sendvar;
    }
    // --- No specific action requested ---
    else {
        // If neither 'subscribe' nor 'increment-view' is set, or they are set to something else
        $response['message'] = 'No valid AJAX action specified.';
        http_response_code(400); // Bad Request
    }

} catch (Exception $e) {
    // Catch any unexpected exceptions from controllers or other code
    error_log('Fatal error in ajax_handler: ' . $e->getMessage());
    $response['message'] = 'A critical server error occurred. Please try again.';
    http_response_code(500); // Internal Server Error
}

// 4. Send JSON Response
echo json_encode($response);
exit(); // Ensure no extra output
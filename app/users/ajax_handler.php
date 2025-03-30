<?php 
// app/users/ajax_handler.php
header('Content-Type: application/json');

use App\Controllers\PostController;

//initialize first
$postController = new PostController();

// Check if the current user is an admin or author
$isAdminOrAuthor = $auth->hasRole(\Delight\Auth\Role::ADMIN) || $auth->hasRole(\Delight\Auth\Role::AUTHOR);

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

//If increment-view is requested
if(isset($_GET['increment-view']) ){
    // Get the post ID or slug from the query string
    $slug = $_GET['slug'] ?? null;
    
    if (!$slug) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing post identifier', 'views' => '0']);
        exit;
    }
    // Only count views for non-admin/author users
    if (!$isAdminOrAuthor) {
        //get post information
        $post = $postController->show($slug);
        // Initialize session storage for viewed posts if not set.
        if (!isset($_SESSION['viewed_posts'])) {
            $_SESSION['viewed_posts'] = [];
        }
        
        $postKey = $post['slug'];
        $today = date('Y-m-d');
        
        //set success to false unless true
        $sendvar = ['success' => false, 'views' => $post['views']];
        
        if ($post) {
            // If this post hasn't been viewed today, then increment views.
            if (!isset($_SESSION['viewed_posts'][$postKey]) || $_SESSION['viewed_posts'][$postKey] !== $today) {
                $_SESSION['viewed_posts'][$postKey] = $today;
                $success = $postController->incrementViews($post['id']);
                $sendvar = ['success' => true, 'views' => $post['views']];
            }
        }
    }
    //send response
    echo json_encode($sendvar);
}

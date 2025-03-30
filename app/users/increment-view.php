<?php
// app/users/increment-view.php
header('Content-Type: application/json');

use App\Controllers\PostController;

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get the post ID or slug from the query string
$postId = $_GET['id'] ?? null;
$slug = $_GET['slug'] ?? null;

if (!$postId && !$slug) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing post identifier']);
    exit;
}

$postController = new PostController();

// Increment the view count. You may choose to call your own logic to handle uniqueness.
if ($postId) {
    $success = $postController->incrementViews($postId);
} else {
    // If slug is provided, you may fetch the post first
    $post = $postController->show($slug);
    if ($post) {
        $success = $postController->incrementViews($post['id']);
    } else {
        $success = false;
    }
}

echo json_encode(['success' => $success]);

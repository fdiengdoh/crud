<?php
// public/post-delete.php
require_once __DIR__ . '/../../init.php';

use App\Controllers\PostController;

// Ensure the user is logged in
if (!$auth->isLoggedIn()) {
    header("Location: " . $link->getUrl('/users/login'));
    exit;
}

$userId = $auth->getUserId();
$postController = new PostController();

// Get the post ID from the query string
$postId = $_GET['id'] ?? null;
if (!$postId) {
    header("Location: " . $link->getUrl('/users') . "/?msg=Post ID not provided");
    exit;
}

// Retrieve the post to verify ownership
$post = $postController->show($postId);
if (!$post || $post['user_id'] != $userId) {
    header("Location: " . $link->getUrl('/users') . "/?msg=Unauthorized access");
    exit;
}

// Instead of deleting the post, update its status to 'draft'
$stmt = $pdo->prepare("UPDATE posts SET status = 'draft', updated_at = NOW() WHERE id = ?");
$result = $stmt->execute([$postId]);

if ($result) {
    header("Location: " . $link->getUrl('/users') . "/?msg=Post moved to draft successfully");
} else {
    header("Location: " . $link->getUrl('/users') . "/?msg=Error updating post status");
}
exit;

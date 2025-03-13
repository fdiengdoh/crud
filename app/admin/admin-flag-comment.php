<?php
// app/flag-comment.php
require_once __DIR__ . '/../../init.php';

use App\Controllers\CommentController;
use App\Helpers\AuthHelper;

// Ensure only admin users can access this page
AuthHelper::requireAdmin($auth);

$commentController = new CommentController();
$action = $_GET['action'] ?? '';
$commentId = $_GET['id'] ?? null;

if ($commentId && $action) {
    if ($action === 'approve') {
        $commentController->approveComment($commentId);
    } elseif ($action === 'delete') {
        $commentController->deleteComment($commentId);
    }
}

// Redirect back to the admin panel
header("Location: " . BASE_URL . "/admin");
exit;

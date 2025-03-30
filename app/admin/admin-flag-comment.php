<?php
// app/admin/admin-flag-comment.php
require_once __DIR__ . '/../../init.php';

use App\Controllers\CommentController;
use App\Helpers\AuthHelper;
use App\Utils\Cache;

// Load cache configuration and instantiate the Cache utility.
$config = require CACHE_DIR . '/config.php';
$cache = new Cache($config);

// Ensure only admin users can access this page
AuthHelper::requireAdmin($auth);

$commentController = new CommentController();
$action = $_GET['action'] ?? '';
$commentId = $_GET['id'] ?? null;
$slug = $commentController->getSlug($commentId);


if ($commentId && $action) {
    if ($action === 'approve') {
        $commentController->approveComment($commentId);
    } elseif ($action === 'delete') {
        $commentController->deleteComment($commentId);
    }
}
$cache->clearCache( '/' . $slug['slug']);

// Redirect back to the admin panel
header("Location: " . $link->getUrl("/admin"));
exit;

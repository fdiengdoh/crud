<?php
// public/post-delete.php

use App\Controllers\PostController;
use App\Utils\Cache;
use App\Helpers\CsrfHelper;

// Load cache configuration and instantiate the Cache utility.
$config = require (CACHE_CONFIG);
$cache = new Cache($config);

// Ensure the user is logged in
if (!$auth->isLoggedIn()) {
    header("Location: " . $link->getUrl('/users/login'));
    exit;
}

// CSRF Validation (Crucial change)
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !CsrfHelper::isValid($_POST['csrf_token'] ?? '')) {
    header("Location: " . $link->getUrl('/users') . "/?msg=Security validation failed");
    exit;
}

$userId = $auth->getUserId();
$postController = new PostController();

// Get the post ID from the query string
$postId = $_POST['id'] ?? null;
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
$cache->clearCache('/' . $post['slug']);
$result = $postController->delete($postId);

if ($result) {
    header("Location: " . $link->getUrl('/users') . "/?msg=Post moved to draft successfully");
} else {
    header("Location: " . $link->getUrl('/users') . "/?msg=Error updating post status");
}
exit;

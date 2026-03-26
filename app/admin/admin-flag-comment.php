<?php
// app/admin/flag-comment.php

use App\Controllers\CommentController;
use App\Helpers\AuthHelper;
use App\Utils\Cache;
use App\Helpers\CsrfHelper;

// Load cache configuration and instantiate the Cache utility.
$config = require CACHE_CONFIG;
$cache = new Cache($config);

// 1. GATE 1: Authorization
AuthHelper::requireAdmin($auth);

// 2. GATE 2: CSRF Validation (New)
// Only allow POST requests for moderation to prevent unauthorized URL triggers
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !CsrfHelper::isValid($_POST['csrf_token'] ?? '')) {
    header("Location: " . $link->getUrl("/admin") . "?msg=" . urlencode("Security validation failed"));
    exit;
}

$commentController = new CommentController();
$action = $_POST['action'] ?? '';
$commentIds = $_POST['ids'] ?? []; // This is now an array

if (!empty($commentIds) && is_array($commentIds) && $action) {
    $processedCount = 0;

    foreach ($commentIds as $id) {
        $id = intval($id);
        
        // 1. Get slug to clear cache for the specific post
        $slugData = $commentController->getSlug($id);
        
        // 2. Perform Action
        if ($action === 'approve') {
            $result = $commentController->approveComment($id);
        } elseif ($action === 'delete') {
            $result = $commentController->deleteComment($id);
        }

        // 3. Clear Cache for this specific post if successful
        if ($result && !empty($slugData['slug'])) {
            $cache->clearCache('/' . $slugData['slug']);
            $processedCount++;
        }
    }
    
    $msg = "Successfully " . ($action === 'approve' ? "approved" : "deleted") . " $processedCount comments.";
} else {
    $msg = "No comments selected.";
}

header("Location: " . $link->getUrl("/admin") . "?msg=" . urlencode($msg));
exit;
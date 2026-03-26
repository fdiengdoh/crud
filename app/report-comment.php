<?php
// app/report-comment.php

use App\Controllers\CommentController;
use App\Utils\Cache;
use App\Helpers\CsrfHelper; // Import the Helper

// Load cache configuration and instantiate the Cache utility.
$config = require CACHE_CONFIG;
$cache = new Cache($config);

$commentController = new CommentController();
$commentId = $_POST['id'] ?? null;
$message = '';

// 1. CSRF VALIDATION
// This ensures the report was actually submitted from your single-post.php form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CsrfHelper::isValid($_POST['csrf_token'] ?? '')) {
        // If security fails, redirect back with an error and stop execution
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? BASE_URL) . '?report-msg=Security mismatch');
        exit;
    }

    if ($commentId) {
        // Fetch slug before reporting if needed for cache clearing
        $slugData = $commentController->getSlug($commentId);
        
        // Execute the report logic
        if ($commentController->reportComment($commentId)) {
            $message = 'Comment Reported';
            
            // 2. CACHE CLEARING
            // Only clear cache if we have a valid slug and the report succeeded
            if ($slugData && isset($slugData['slug'])) {
                $cache->clearCache('/' . $slugData['slug']);
            }
        } else {
            $message = 'Error reporting comment';
        }
    }
}

// Redirect back to the referring page with the status message
$redirectUrl = $_SERVER['HTTP_REFERER'] ?? BASE_URL;
// Remove existing query strings to avoid stacking them
$cleanRedirect = strtok($redirectUrl, '?'); 

header("Location: " . $cleanRedirect . "?report-msg=" . urlencode($message));
exit;

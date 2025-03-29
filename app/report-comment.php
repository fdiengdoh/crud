<?php
// app/report-comment.php
require_once __DIR__ . '/../init.php';

use App\Controllers\CommentController;
use App\Utils\Cache;

// Load cache configuration and instantiate the Cache utility.
$config = require CACHE_DIR . '/config.php';
$cache = new Cache($config);

$commentController = new CommentController();
$commentId = $_POST['id'] ?? null;
$slug = $commentController->getSlug($commentId);


if ($commentId) {
    $commentController->reportComment($commentId);
    
}
$cache->clearCache('/'.$slug['slug']);
// Redirect back to the referring page or home if not set
header("Location: " . ($_SERVER['HTTP_REFERER'] .'?report-msg=Comment Reported' ?? BASE_URL . '/'));
exit;

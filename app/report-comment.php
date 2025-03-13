<?php
// app/report-comment.php
require_once __DIR__ . '/../init.php';

use App\Controllers\CommentController;

$commentController = new CommentController();
$commentId = $_GET['id'] ?? null;

if ($commentId) {
    $commentController->reportComment($commentId);
}

// Redirect back to the referring page or home if not set
header("Location: " . ($_SERVER['HTTP_REFERER'] .'?report-msg=Comment Reported' ?? BASE_URL . '/'));
exit;

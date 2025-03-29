<?php
// public/upload-image.php

require_once __DIR__ . '/../../init.php';

use App\AuthConstants;
use App\Controllers\MediaController;

// Allow access only if the user is logged in and is either an Author or Admin
if (
    !$auth->isLoggedIn() ||
    (
      !$auth->hasRole(AuthConstants::ROLE_AUTHOR) &&
      !$auth->hasRole(AuthConstants::ROLE_ADMIN)
    )
) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access.']);
    exit;
}


// Process file upload
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    // Check file size (limit to 5MB for example)
    $maxFileSize = 8 * 1024 * 1024; // 2MB in bytes
    if ($_FILES['file']['size'] > $maxFileSize) {
        http_response_code(400);
        echo json_encode(['error' => 'File size exceeds limit (8MB).']);
        exit;
    }
    $uploader = new MediaController();
    $uploadedPath = $uploader->handleUpload($_FILES['file']);

    header('Content-Type: application/json');

    if ($uploadedPath) {
        echo json_encode([
            'status' => 'success',
            'location'   =>  BASE_URL . '/' . $uploadedPath['image'],
            'path' => BASE_URL . '/' . $uploadedPath['dir'],
        ]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'No file uploaded or an error occurred.']);
    }
    exit;
}
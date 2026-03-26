
<?php
// admin/upload-image.php

use App\AuthConstants;
use App\Controllers\MediaController;
use App\Helpers\CsrfHelper; // Import the Helper

// 1. Authorization Check
if (
    !$auth->isLoggedIn() ||
    (
      !$auth->hasRole(AuthConstants::ROLE_AUTHOR) &&
      !$auth->hasRole(AuthConstants::ROLE_ADMIN)
    )
) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access.']);
    exit;
}

// 2. CSRF Validation (Crucial for API endpoints)
// We check $_POST['csrf_token'] which TinyMCE will send.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CsrfHelper::isValid($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'CSRF token validation failed.']);
        exit;
    }
}

// 3. Process file upload
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    // Check file size (limit to 8MB)
    $maxFileSize = 8 * 1024 * 1024; 
    if ($_FILES['file']['size'] > $maxFileSize) {
        http_response_code(400);
        header('Content-Type: application/json');
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
        echo json_encode(['error' => 'The file could not be saved to the server.']);
    }
    exit;
} else {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No file was received by the server.']);
    exit;
}
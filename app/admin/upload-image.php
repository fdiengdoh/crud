<?php
// public/upload-image.php
require_once __DIR__ . '/../../init.php';

use App\AuthConstants;

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
        echo json_encode(['error' => 'File size exceeds limit (2MB).']);
        exit;
    }
    
    // Define the destination directory (using PUBLIC_DIR and assets folder)
    $uploadDir = PUBLIC_DIR . '/assets/image/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $fileTmpPath = $_FILES['file']['tmp_name'];
    $originalName = $_FILES['file']['name'];
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    
    // Allowed file extensions
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array(strtolower($extension), $allowedExtensions)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid file type.']);
        exit;
    }
    
    // Generate a unique filename to avoid collisions
    $newFileName = uniqid('img_', true) . '.' . $extension;
    $destPath = $uploadDir . $newFileName;
    
    if (move_uploaded_file($fileTmpPath, $destPath)) {
        // Build the image URL using BASE_URL constant
        $imageUrl = BASE_URL . '/assets/image/' . $newFileName;
        echo json_encode(['location' => $imageUrl]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Could not move uploaded file.']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded or an error occurred.']);
}

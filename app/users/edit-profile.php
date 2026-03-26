<?php
// app/users/profile.php

use App\Controllers\ProfileController;
use App\Helpers\AuthHelper;
use App\Helpers\CsrfHelper; // Import the Helper

$userId = $auth->getUserId();
$profileController = new ProfileController();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validate CSRF Token
    if (!CsrfHelper::isValid($_POST['csrf_token'] ?? '')) {
        $message = "Security token mismatch. Please refresh the page.";
    } else {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName  = trim($_POST['last_name'] ?? '');
        $bio       = trim($_POST['bio'] ?? '');
        
        $result = $profileController->saveProfile($userId, $firstName, $lastName, $bio);
        
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = PUBLIC_DIR . '/assets/profile/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
            $extension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp']; // Added webp
            
            if (in_array($extension, $allowedExtensions)) {
                // Check mime type for extra security
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($fileTmpPath);
                
                if (strpos($mimeType, 'image/') === 0) {
                    $newFileName = uniqid('profile_', true) . '.' . $extension;
                    $destPath = $uploadDir . $newFileName;
                    
                    if (move_uploaded_file($fileTmpPath, $destPath)) {
                        $profilePicUrl = 'assets/profile/' . $newFileName;
                        $profileController->updateProfilePicture($userId, $profilePicUrl);
                        $message .= " Profile picture updated.";
                    }
                } else {
                    $message .= " Invalid image content.";
                }
            } else {
                $message .= " Invalid file extension.";
            }
        }
        $message = $result ? "Profile updated successfully. " . $message : "Update failed. " . $message;
    }
}

// Retrieve profile for display
$profile = $profileController->showProfile($userId);
$firstName = $profile['first_name'] ?? '';
$lastName  = $profile['last_name'] ?? '';
$bio       = $profile['bio'] ?? '';
$profilePicture = $profile['profile_picture'] ?? 'assets/default-profile.png';

include APP_DIR . '/admin/header-auth.php';
?>

<div class="container form-container mt-5">
  <?php if ($message): ?>
    <div class="alert alert-info"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
  <?php endif; ?>
  
  <div class="card shadow-sm p-4">
      <h1 class="mb-4">Edit Profile</h1>
      
      <div class="text-center mb-4">
        <img src="<?= BASE_URL . '/' . htmlspecialchars($profilePicture, ENT_QUOTES, 'UTF-8'); ?>" 
             alt="Profile Picture" 
             class="img-fluid rounded-circle border shadow-sm" 
             style="width: 150px; height: 150px; object-fit: cover;">
      </div>

      <form method="post" action="" class="needs-validation" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="csrf_token" value="<?= $csrfToken; ?>">

        <div class="row">
            <div class="col-md-6 mb-3">
              <label for="first_name" class="form-label">First Name</label>
              <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="last_name" class="form-label">Last Name</label>
              <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($lastName, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
        </div>

        <div class="mb-3">
          <label for="bio" class="form-label">Bio (Tell your students about yourself)</label>
          <textarea class="form-control" id="bio" name="bio" rows="3"><?= htmlspecialchars($bio, ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>

        <div class="mb-4">
          <label for="profile_picture" class="form-label">Change Profile Picture</label>
          <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*">
          <small class="text-muted">Allowed: JPG, PNG, GIF, SVG, WEBP</small>
        </div>

        <button type="submit" class="btn btn-primary px-4">Save Changes</button>
      </form>
  </div>
</div>

<?php include APP_DIR . '/admin/footer-auth.php'; ?>

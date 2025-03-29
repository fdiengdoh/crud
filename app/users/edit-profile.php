<?php
// app/admin/profile.php
require_once __DIR__ . '/../../init.php';

use App\Controllers\ProfileController;
use App\Helpers\AuthHelper;

// Require authenticated user (and optionally admin check if needed)
//AuthHelper::requireAdmin($auth); // If only admins can update profiles; otherwise, you might have a separate check for self-updates.

$userId = $auth->getUserId();
$profileController = new ProfileController();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');
    $bio       = trim($_POST['bio'] ?? '');
    
    // Update profile text details first.
    $result = $profileController->saveProfile($userId, $firstName, $lastName, $bio);
    
    // Process profile picture upload if a file is provided.
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = PUBLIC_DIR . '/assets/profile/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
        $originalName = $_FILES['profile_picture']['name'];
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif','svg'];
        if (in_array(strtolower($extension), $allowedExtensions)) {
            $newFileName = uniqid('profile_', true) . '.' . $extension;
            $destPath = $uploadDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                // Build the relative URL; adjust if necessary.
                $profilePicUrl = 'assets/profile/' . $newFileName;
                // Update profile picture in the database.
                $profileController->updateProfilePicture($userId, $profilePicUrl);
                $message .= " Profile picture updated successfully.";
            } else {
                $message .= " Failed to upload profile picture.";
            }
        } else {
            $message .= " Invalid file type for profile picture.";
        }
    }
    $message = $result ? "Profile updated successfully." . $message : "Failed to update profile." . $message;
}

// Retrieve the updated profile information
$profile = $profileController->showProfile($userId);
$firstName = $profile['first_name'] ?? '';
$lastName  = $profile['last_name'] ?? '';
$bio       = $profile['bio'] ?? '';
$profilePicture = $profile['profile_picture'] ?? '/assets/default-profile.png';

$pageTitle = "Profile - Blog App";
include APP_DIR . '/admin/header-auth.php';
?>

<div class="container form-container mt-5">
  <?php if ($message): ?>
    <div class="alert alert-info"><?php echo htmlspecialchars($message ?? ' ', ENT_QUOTES, 'UTF-8'); ?></div>
  <?php endif; ?>
  <h1 class="mb-4">Your Profile</h1>
  <div class="mb-4">
    <img src="<?= BASE_URL . '/' . htmlspecialchars($profilePicture ?? ' ', ENT_QUOTES, 'UTF-8'); ?>" alt="Profile Picture" class="img-fluid rounded-circle" style="max-width: 150px;">
  </div>
  <form method="post" action="" class="needs-validation" enctype="multipart/form-data" novalidate>
    <div class="mb-3">
      <label for="first_name" class="form-label">First Name</label>
      <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($firstName ?? ' ', ENT_QUOTES, 'UTF-8'); ?>" required>
      <div class="invalid-feedback">Please enter your first name.</div>
    </div>
    <div class="mb-3">
      <label for="last_name" class="form-label">Last Name</label>
      <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($lastName ?? ' ', ENT_QUOTES, 'UTF-8'); ?>" required>
      <div class="invalid-feedback">Please enter your last name.</div>
    </div>
    <div class="mb-3">
      <label for="bio" class="form-label">Bio</label>
      <textarea class="form-control" id="bio" name="bio" rows="3"><?php echo htmlspecialchars($bio ?? ' ', ENT_QUOTES, 'UTF-8'); ?></textarea>
    </div>
    <div class="mb-3">
      <label for="profile_picture" class="form-label">Profile Picture</label>
      <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*">
    </div>
    <button type="submit" class="btn btn-primary">Save Profile</button>
  </form>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  (function () {
    'use strict';
    const form = document.querySelector('.needs-validation');
    form.addEventListener('submit', function (event) {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  })();
</script>

<?php include APP_DIR . '/admin/footer-auth.php'; ?>

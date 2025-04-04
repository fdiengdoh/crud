<?php
// app/users/profile.php
require_once __DIR__ . '/../../init.php';

use App\Controllers\ProfileController;
use App\Controllers\PostController;
use App\Controllers\CategoryController;

$username = $_GET['username'] ?? '';
if (empty($username)) {
    include APP_DIR . '/404.php';
    exit;
}

$profileController = new ProfileController();
$postController   = new PostController();
$categoryController = new CategoryController();

// Retrieve profile by username (using a join with the users table)
// Alternatively, if your profile table doesn't store the username, combine first and last names.
$profile = $profileController->getProfileByUsername($username);
if (!$profile) {
    include APP_DIR . '/404.php';
    exit;
}

// Retrieve posts contributed by this user (using their user_id)
$userId = $profile['user_id'];
$options = ['userId' => $userId, 'status' => 'published', 'limit' => 12];
$posts = $postController->index($options);

// Set page title (using full name) and include common header
$title = htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']) . " - Profile &raquo; fdiengdoh.com";
include APP_DIR . '/include/header.php';
?>
<div class="row p-2">
    <div class="col-md-4 text-center">
        <?php if (!empty($profile['profile_picture'])): ?>
            <img src="<?= BASE_URL . '/' . htmlspecialchars($profile['profile_picture'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?>" alt="Profile Picture" class="img-fluid rounded-circle" style="max-width: 150px;">
            <?php else: ?>
                <img src="<?= BASE_URL ?>/assets/default-profile.png" alt="Default Profile Picture" class="img-fluid rounded-circle" style="max-width: 150px;">
            <?php endif; ?>
    </div>
    <div class="col-md-8">
        <h1><?= htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']); ?></h1>
            <p><?= nl2br(htmlspecialchars($profile['bio'] ?? ' ', ENT_QUOTES, 'UTF-8')); ?></p>
    </div>
</div>
<hr>
<main>
    <div class="row m-0">
        <div class="col-md-8">
            <div class="container">
                <h2>Latest Posts by <?= htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']); ?></h2>
                <?php if (!empty($posts)): ?>
                <div class="row">
                    <?php foreach ($posts as $post): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <?php if (!empty($post['feature_image'])): ?>
                            <img src="<?= htmlspecialchars($post['feature_image'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?>" class="card-img-top feature-img" alt="Feature Image">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($post['title'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?></h5>
                                <p class="card-text"><?= htmlspecialchars($post['excerpt'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                            <div class="card-footer">
                                <a href="<?= BASE_URL . '/' . htmlspecialchars($post['slug'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-link">Read More</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                    <?php else: ?>
                    <p>No posts contributed yet.</p>
                    <?php endif; ?>
            </div>
        </div>
        <?php require_once(APP_DIR . '/include/sidebar.php'); ?>
                    
    </div>
    <!-- Row Ends -->
</main>
<?php include APP_DIR . '/include/footer.php'; ?>

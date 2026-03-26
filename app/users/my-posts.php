<?php
// app/my-posts.php

use App\Controllers\PostController;
use App\Controllers\CategoryController;
use App\Controllers\ProfileController;
use Delight\Auth\Role;
use App\Helpers\CsrfHelper;

$userId = $auth->getUserId();
$profile = new ProfileController();

$categories = (new CategoryController())->getAllCategories();


if(!$profile->showProfile($userId)){
  header('Location: ' . $link->getUrl('/users') . '/edit-profile');
  exit;
}

// (Additional authentication checks can be done in init.php)
// Set a page title for the header if desired
$pageTitle = "My Posts - Blog App";
include APP_DIR . '/admin/header-auth.php';

$postController = new PostController();
$options = [ 'userId' => $userId ];

isset($_GET['category']) ?  $options['category'] = $_GET['category'] : '';

$userPosts = $postController->index($options);
// Retrieve message from GET parameter if available
$message = $_GET['msg'] ?? '';
?>
  <div class="container">
    <h1 class="mb-4">My Posts</h1>
    <?php if (!empty($message)): ?>
      <div class="alert alert-info" role="alert">
        <?php echo htmlspecialchars($message ?? ' ', ENT_QUOTES, 'UTF-8'); ?>
      </div>
    <?php endif; ?>
    <p>View Posts by Categories
    <?php foreach($categories as $category): ?>
    <a href="/users?category=<?= $category['id'] ?>" class="btn btn-info btn-small p-2"><?= $category['name'] ?></a>
    <?php endforeach; ?>
    </p>
<?php if (count($userPosts) > 0): ?>
    <?php foreach ($userPosts as $post): ?>
    <div class="row">
        <div class="card p-0 mb-2 shadow-sm">
            <div class="row g-0">
                <div class="col-md-2">
                    <img src="<?= htmlspecialchars($post['feature_image'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?>" class="rounded-start" style="object-fit: cover; width:100%; height:125px;" alt="<?= htmlspecialchars($post['title'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="col-md-7 z-10 bg-light">
                    <div class="card-body">
                        <?php $class = ($post['status'] === 'published') ? "success" : "warning"; ?>
                        <h5 class="card-title">
                            <?= htmlspecialchars($post['title'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?> 
                            <small class="badge text-bg-<?= $class ?>"><?= htmlspecialchars($post['status'], ENT_QUOTES, 'UTF-8') ?></small>
                        </h5>
                        <p class="card-text small text-muted"><?= htmlspecialchars($post['excerpt'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </div>
                    
                <div class="col-md-3 bg-light border-start">
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            <a href="<?= $link->getUrl('/users/post-edit') ?>/?id=<?= $post['id']; ?>" class="btn btn-warning btn-sm" title="Edit Post">
                                <i class="bi bi-vector-pen"></i> Edit
                            </a>

                            <form action="<?= $link->getUrl('/users/post-delete') ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to move this post to draft?');">
                                <input type="hidden" name="id" value="<?= $post['id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken; ?>">
                                <button type="submit" class="btn btn-danger btn-sm" title="Move to Draft">
                                    <i class="bi bi-backspace-reverse-fill"></i> Draft
                                </button>
                            </form>

                            <a href="<?= LOGIN_URL . '/' . htmlspecialchars($post['slug'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="btn btn-primary btn-sm">
                                <i class="bi bi-browser-safari"></i> View
                            </a>
                        </div>
                        <p class="card-text mb-0">
                            <small class="text-primary me-2"><i class="bi bi-calendar-check-fill"></i> <?= date('d M Y', strtotime($post['created_at'])) ?></small>
                            <small class="text-info"><i class="bi bi-bar-chart-line"></i> <?= (int)$post['views'] ?></small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info">You have not created any posts yet.</div>
    <?php endif; ?>
  </div>
<!-- Your page content goes here -->
<?php include APP_DIR . '/admin/footer-auth.php'; ?>
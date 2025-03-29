<?php
// app/my-posts.php
require_once __DIR__ . '/../../init.php';

use App\Controllers\PostController;
use App\Controllers\CategoryController;
use App\Controllers\ProfileController;
use Delight\Auth\Role;

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
            <div class="card p-0 mb-2">
                <div class="row g-0">
                    <div class="col-md-2">
                        <img src="<?= htmlspecialchars($post['feature_image'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?>" class="rounded-start" height="125" width="100%" style="object-fit: cover;" alt="<?= htmlspecialchars($post['title'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="col-md-7 z-10 bg-light">
                        <div class="card-body">
                            <?php ($post['status'] === 'published') ? $class = "success" : $class = "warning"; ?>
                            <h5 class="card-title"><?= htmlspecialchars($post['title'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?> <small class="badge text-bg-<?= $class ?>"><?= $post['status'] ?></small></h5>
                            <p class="card-text"><?= htmlspecialchars($post['excerpt'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?></p>
                                
                        </div>
                    </div>
                        
                    <div class="col-md-3 bg-light">
                        <div class="card-body">
                            <p class="card-text">
                                <a href="<?= $link->getUrl('/users/post-edit') ?>/?id=<?php echo $post['id']; ?>" class="btn btn-warning btn-sm" title="Edit Post"><i class="bi bi-vector-pen"></i> Edit</a>
                                <a href="<?= $link->getUrl('/users/post-delete') ?>/?id=<?php echo $post['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to move this post to draft?');"><i class="bi bi-backspace-reverse-fill"></i> Draft</a>
                                <a href="<?= LOGIN_URL . '/' . htmlspecialchars($post['slug'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="btn btn-primary btn-sm"><i class="bi bi-browser-safari"></i> View</a>
                            </p>
                            <p class="card-text">
                                <span class="text-primary"><i class="bi bi-calendar-check-fill"></i> <?= date('d M Y', strtotime($post['created_at'])) ?></span>
                                <span class="text-info"><i class="bi bi-bar-chart-line"></i> <?= $post['views'] ?></span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p>You have not created any posts yet.</p>
    <?php endif; ?>
  </div>
<!-- Your page content goes here -->
<?php include APP_DIR . '/admin/footer-auth.php'; ?>
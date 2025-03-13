<?php
// app/my-posts.php
require_once __DIR__ . '/../../init.php';

use App\Controllers\PostController;
use App\Controllers\ProfileController;
use Delight\Auth\Role;

$userId = $auth->getUserId();
$profile = new ProfileController();

if(!$profile->showProfile($userId)){
  header('Location: ' . $link->getUrl('/users') . '/edit-profile');
  exit;
}

// (Additional authentication checks can be done in init.php)
// Set a page title for the header if desired
$pageTitle = "My Posts - Blog App";
include APP_DIR . '/admin/header-auth.php';

// ... (rest of your page content)


$postController = new PostController();
$userPosts = $postController->index($userId);

// Retrieve message from GET parameter if available
$message = $_GET['msg'] ?? '';
?>
  <div class="container">
    <h1 class="mb-4">My Posts</h1>
    <?php if (!empty($message)): ?>
      <div class="alert alert-info" role="alert">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>
    
    <?php if (count($userPosts) > 0): ?>
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Title</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($userPosts as $post): ?>
            <tr>
              <td>
                <a href="<?= BASE_URL ?>/<?php echo htmlspecialchars($post['slug']); ?>" class="btn btn-link">
                  <?php echo htmlspecialchars($post['title']); ?>
                </a>
              </td>
              <td>
                <?php if ($post['status'] === 'published'): ?>
                  <span class="badge bg-success">Published</span>
                <?php elseif ($post['status'] === 'draft'): ?>
                  <span class="badge bg-warning text-dark">Draft</span>
                <?php else: ?>
                  <span class="badge bg-secondary"><?php echo htmlspecialchars($post['status']); ?></span>
                <?php endif; ?>
              </td>
              <td><?php echo htmlspecialchars(date('d F Y', strtotime($post['created_at']))); ?></td>
              <td>
                <a href="<?= $link->getUrl('/users/post-edit') ?>/?id=<?php echo $post['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                <a href="<?= $link->getUrl('/users/post-delete') ?>/?id=<?php echo $post['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to move this post to draft?');">Move to Draft</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>You have not created any posts yet.</p>
    <?php endif; ?>
  </div>
<!-- Your page content goes here -->
<?php include APP_DIR . '/admin/footer-auth.php'; ?>
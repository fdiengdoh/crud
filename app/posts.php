<?php
// public/posts.php
require_once __DIR__ . '/../init.php';

use App\Controllers\PostController;

if (!$auth->isLoggedIn()) {
    header("Location: /login");
    exit;
}
$userId = $auth->getUserId();

$postController = new PostController();
$posts = $postController->index($userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Your Posts</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .container { margin-top: 30px; }
  </style>
</head>
<body>
  <div class="container">
    <h1 class="mb-4">Your Posts</h1>
    <a href="<?= BASE_URL ?>/post-create" class="btn btn-success mb-3">Create New Post</a>
    <?php if (count($posts) > 0): ?>
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Title</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($posts as $post): ?>
            <tr>
              <td><?php echo htmlspecialchars($post['title']); ?></td>
              <td><?php echo htmlspecialchars(date('d F Y', strtotime($post['created_at']))); ?></td>
              <td>
                <a href="<?= BASE_URL ?>/post-edit/?id=<?php echo $post['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                <a href="<?= BASE_URL ?>/post-delete/?id=<?php echo $post['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>You haven't created any posts yet.</p>
    <?php endif; ?>
    <a href="<?= BASE_URL ?>/" class="btn btn-secondary">Back to Dashboard</a>
  </div>
  
  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

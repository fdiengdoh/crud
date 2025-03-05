<?php
// app/admin-panel.php
require_once __DIR__ . '/../../init.php';

use App\Controllers\PostController;
use App\Controllers\CommentController;
use App\Helpers\AuthHelper;
use App\AuthConstants;

// Set a page title for the header if desired
$pageTitle = "Admin Panel";
// Require admin role (using our relaxed AuthHelper)
AuthHelper::requireAdmin($auth);

// Process user role update if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user_id'])) {
    $updateUserId = $_POST['update_user_id'];
    $newRole = $_POST['new_role'] ?? AuthConstants::ROLE_SUBSCRIBER;
    try {
        // Remove any of our defined roles from this user.
        $rolesToRemove = [
            AuthConstants::ROLE_ADMIN,
            AuthConstants::ROLE_AUTHOR,
            AuthConstants::ROLE_SUBSCRIBER
        ];
        foreach ($rolesToRemove as $role) {
            $auth->admin()->removeRoleForUserById($updateUserId, $role);
        }
        // Then add the selected role.
        $auth->admin()->addRoleForUserById($updateUserId, $newRole);
        header("Location: " . BASE_URL . "/admin?msg=" . urlencode("User role updated successfully"));
        exit;
    } catch (\Exception $e) {
        header("Location: " . BASE_URL . "/admin?msg=" . urlencode("Error updating role: " . $e->getMessage()));
        exit;
    }
}

// Process publish action if provided via GET parameter
if (isset($_GET['publish']) && is_numeric($_GET['publish'])) {
    $postController = new PostController();
    $postId = intval($_GET['publish']);
    $postController->publish($postId);
    header("Location: " . BASE_URL . "/admin?msg=" . urlencode("Post published successfully"));
    exit;
}

/*
// Process comment moderation actions if provided via GET parameters
if (isset($_GET['approve_comment']) && is_numeric($_GET['approve_comment'])) {
    require_once APP_DIR . '/flag-comment'; // Alternatively, process directly here.
}
if (isset($_GET['delete_comment']) && is_numeric($_GET['delete_comment'])) {
    require_once APP_DIR . '/delete-comment';
}*/

// Retrieve message from GET parameter if available
$msg = $_GET['msg'] ?? '';

$postController = new PostController();

// Retrieve posts with 'draft' status for review
$stmt = $pdo->prepare("SELECT * FROM posts WHERE status = 'draft' ORDER BY created_at DESC");
$stmt->execute();
$draftPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Retrieve all users for role management
$stmtUsers = $pdo->query("SELECT id, email, username, roles_mask as role FROM users ORDER BY username ASC");
$users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

$commentController = new CommentController();
$pendingComments = $commentController->getPendingComments();

include APP_DIR . '/admin/header-auth.php';
?>

<div class="container">
  <?php if ($msg): ?>
    <div class="alert alert-info" role="alert">
      <?php echo htmlspecialchars($msg); ?>
    </div>
  <?php endif; ?>
  
  <h1 class="mb-4">Admin Panel</h1>
  
  <!-- Section 1: Publish Draft Posts -->
  <h2>Publish Draft Posts</h2>
  <?php if (count($draftPosts) > 0): ?>
    <table class="table table-striped mb-5">
      <thead>
        <tr>
          <th>Title</th>
          <th>Created</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($draftPosts as $post): ?>
          <tr>
            <td><?php echo htmlspecialchars($post['title']); ?></td>
            <td><?php echo htmlspecialchars(date('d F Y', strtotime($post['created_at']))); ?></td>
            <td>
              <a href="<?= BASE_URL ?>/admin?publish=<?php echo $post['id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Publish this post?');">Publish</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No draft posts available for publishing.</p>
  <?php endif; ?>
  
  <!-- Section 2: User Role Management -->
  <h2>User Role Management</h2>
  <table class="table table-bordered mb-5">
    <thead>
      <tr>
        <th>User ID</th>
        <th>Email</th>
        <th>Username</th>
        <th>Current Role</th>
        <th>New Role</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $user): ?>
        <tr>
          <td><?php echo htmlspecialchars($user['id']); ?></td>
          <td><?php echo htmlspecialchars($user['email']); ?></td>
          <td><?php echo htmlspecialchars($user['username']); ?></td>
          <td>
            <?php
              if ($user['role'] == AuthConstants::ROLE_ADMIN) {
                  echo "Admin";
              } elseif ($user['role'] == AuthConstants::ROLE_AUTHOR) {
                  echo "Author";
              } elseif ($user['role'] == AuthConstants::ROLE_SUBSCRIBER) {
                  echo "Subscriber";
              } else {
                  echo "Unknown";
              }
            ?>
          </td>
          <td>
            <form method="post" action="<?= BASE_URL ?>/admin" class="mb-0">
              <input type="hidden" name="update_user_id" value="<?php echo $user['id']; ?>">
              <select name="new_role" class="form-select">
                <option value="<?php echo AuthConstants::ROLE_SUBSCRIBER; ?>" <?php echo ($user['role'] == AuthConstants::ROLE_SUBSCRIBER) ? 'selected' : ''; ?>>Subscriber</option>
                <option value="<?php echo AuthConstants::ROLE_AUTHOR; ?>" <?php echo ($user['role'] == AuthConstants::ROLE_AUTHOR) ? 'selected' : ''; ?>>Author</option>
                <option value="<?php echo AuthConstants::ROLE_ADMIN; ?>" <?php echo ($user['role'] == AuthConstants::ROLE_ADMIN) ? 'selected' : ''; ?>>Admin</option>
              </select>
          </td>
          <td>
              <button type="submit" class="btn btn-primary btn-sm">Update</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  
  <!-- Section 3: Comment Moderation -->
  <h2>Comment Moderation</h2>
  <?php if (!empty($pendingComments)): ?>
    <table class="table table-striped mb-5">
      <thead>
        <tr>
          <th>Comment ID</th>
          <th>Author</th>
          <th>Email</th>
          <th>Comment</th>
          <th>Posted On</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pendingComments as $comm): ?>
          <tr>
            <td><?= htmlspecialchars($comm['id']); ?></td>
            <td><?= htmlspecialchars($comm['author']); ?></td>
            <td><?= htmlspecialchars($comm['email']); ?></td>
            <td><?= htmlspecialchars(substr(strip_tags($comm['comment']), 0, 50)) . '...'; ?></td>
            <td><?= htmlspecialchars(date('d F Y', strtotime($comm['created_at']))); ?></td>
            <td>
              <a href="<?= BASE_URL ?>/admin/flag-comment/?action=approve&id=<?= $comm['id']; ?>" class="btn btn-success btn-sm">Approve</a>
              <a href="<?= BASE_URL ?>/admin/flag-comment/?action=delete&id=<?= $comm['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this comment?');">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No pending comments for moderation.</p>
  <?php endif; ?>
  
  <a href="<?= BASE_URL ?>/" class="btn btn-secondary mt-3">Back to Home</a>
  <a href="<?= BASE_URL ?>/admin/categories" class="btn btn-success mt-3">Manage Categories</a>
</div>

<?php include APP_DIR . '/admin/footer-auth.php'; ?>

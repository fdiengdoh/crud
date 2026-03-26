<?php
// app/admin/admin-panel.php

use App\Controllers\PostController;
use App\Controllers\CommentController;
use App\Helpers\AuthHelper;
use App\AuthConstants;
use App\Utils\Cache;
use App\Helpers\CsrfHelper;

// Load cache configuration and instantiate the Cache utility.
$config = require (CACHE_CONFIG);
$cache = new Cache($config);

// Set a page title for the header if desired
$pageTitle = "Admin Panel";
// Require admin role (using our relaxed AuthHelper)
AuthHelper::requireAdmin($auth, $link->getUrl('/users/login'));

// --- POST LOGIC (Secure) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Global CSRF Check for all POST actions
    if (!CsrfHelper::isValid($_POST['csrf_token'] ?? '')) {
        header("Location: " . $link->getUrl("/admin") . "?msg=" . urlencode("Security Token Expired"));
        exit;
    }

    // 1. Update User Role
    if (isset($_POST['update_user_id'])) {
        $updateUserId = $_POST['update_user_id'];
        $newRole = $_POST['new_role'] ?? AuthConstants::ROLE_SUBSCRIBER;
        try {
            $rolesToRemove = [AuthConstants::ROLE_ADMIN, AuthConstants::ROLE_AUTHOR, AuthConstants::ROLE_SUBSCRIBER];
            foreach ($rolesToRemove as $role) { $auth->admin()->removeRoleForUserById($updateUserId, $role); }
            $auth->admin()->addRoleForUserById($updateUserId, $newRole);
            header("Location: " . $link->getUrl("/admin") . "?msg=" . urlencode("User role updated successfully"));
            exit;
        } catch (\Exception $e) { /* Error handling... */ }
    }

    // 2. Clear Cache
    if (isset($_POST['action']) && $_POST['action'] === 'clear-cache') {
        $cache->clearAllCache();
        header("Location: " . $link->getUrl("/admin") . "?msg=" . urlencode("Cache cleared successfully"));
        exit;
    }

    // 3. Publish Post
    if (isset($_POST['publish_post_id'])) {
        $postController = new PostController();
        $postController->publish(intval($_POST['publish_post_id']));
        $cache->clearAllCache();
        header("Location: " . $link->getUrl("/admin") . "?msg=" . urlencode("Post published successfully"));
        exit;
    }

    // 4. Delete User
    if (isset($_POST['delete_user_id'])) {
        try {
            $auth->admin()->deleteUserById(intval($_POST['delete_user_id']));
            header("Location: " . $link->getUrl("/admin") . "?msg=" . urlencode("User deleted successfully"));
            exit;
        } catch (\Exception $e) { /* Error handling... */ }
    }
}

// Retrieve message from GET parameter if available.
$msg = $_GET['msg'] ?? '';

$postController = new PostController();

// Retrieve posts with 'draft' status for review.
$stmt = $pdo->prepare("SELECT * FROM posts WHERE status = 'draft' ORDER BY created_at DESC");
$stmt->execute();
$draftPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Retrieve all users for role management.
// Note: delight‑im/auth stores roles in the "roles_mask" column.
$stmtUsers = $pdo->query("SELECT id, email, username, roles_mask as role FROM users ORDER BY username ASC");
$users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

$commentController = new CommentController();
$pendingComments = $commentController->getPendingComments();

include APP_DIR . '/admin/header-auth.php';
?>

<div class="container">
  <?php if ($msg): ?>
    <div class="alert alert-info" role="alert">
      <?php echo htmlspecialchars($msg ?? ' ', ENT_QUOTES, 'UTF-8'); ?>
    </div>
  <?php endif; ?>
  
  <h1 class="mb-4">Admin Panel</h1>
  <form method="POST" action="<?= $link->getUrl('/admin') ?>" class="d-inline">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    <input type="hidden" name="action" value="clear-cache">
    <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Clear all cache?');">
        Clear All Cache
    </button>
  </form>
  
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
            <td><?php echo htmlspecialchars($post['title'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars(date('d F Y', strtotime($post['created_at']))); ?></td>
            <td>
              <form method="POST" action="<?= $link->getUrl('/admin') ?>" class="d-inline">
                  <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                  <input type="hidden" name="publish_post_id" value="<?= $post['id'] ?>">
                  <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Publish this post?');">
                      Publish
                  </button>
              </form>       
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No draft posts available for publishing.</p>
  <?php endif; ?>
  
  <!-- Section 2: User Role Management & Deletion -->
  <h2>User Role Management</h2>
  <table class="table table-bordered mb-5">
    <thead>
      <tr>
        <th>User ID</th>
        <th>Email</th>
        <th>Username</th>
        <th>Current Role</th>
        <th>New Role</th>

        <th>Delete User</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $user): ?>
        <tr>
          <td><?php echo htmlspecialchars($user['id'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?></td>
          <td><?php echo htmlspecialchars($user['email'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?></td>
          <td><?php echo htmlspecialchars($user['username'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?></td>
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
              <form method="post" action="<?= $link->getUrl('/admin') ?>" class="mb-0 d-flex gap-1">
                  <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                  <input type="hidden" name="update_user_id" value="<?= $user['id']; ?>">
                  <select name="new_role" class="form-select form-select-sm">
                      <option value="<?= AuthConstants::ROLE_SUBSCRIBER; ?>" <?= ($user['role'] == AuthConstants::ROLE_SUBSCRIBER) ? 'selected' : ''; ?>>Subscriber</option>
                      <option value="<?= AuthConstants::ROLE_AUTHOR; ?>" <?= ($user['role'] == AuthConstants::ROLE_AUTHOR) ? 'selected' : ''; ?>>Author</option>
                      <option value="<?= AuthConstants::ROLE_ADMIN; ?>" <?= ($user['role'] == AuthConstants::ROLE_ADMIN) ? 'selected' : ''; ?>>Admin</option>
                  </select>
                  <button type="submit" class="btn btn-primary btn-sm">Update</button>
              </form>
          </td>
          <td>
              <form method="POST" action="<?= $link->getUrl('/admin') ?>" class="d-inline">
                  <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                  <input type="hidden" name="delete_user_id" value="<?= $user['id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this user?');">
                      Delete
                  </button>
              </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  
  <!-- Section 3: Comment Moderation -->
  <h2>Comment Moderation</h2>
  <?php if (!empty($pendingComments)): ?>
    <form method="POST" action="<?= $link->getUrl('/admin/flag-comment') ?>" id="bulk-comment-form">
      <input type="hidden" name="csrf_token" value="<?= $csrfToken; ?>">
      
      <div class="mb-2">
        <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve Selected</button>
        <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm" onclick="return confirm('Delete selected comments?');">Delete Selected</button>
      </div>

      <table class="table table-striped mb-5">
        <thead>
          <tr>
            <th><input type="checkbox" id="select-all-comments"></th> <th>Author</th>
            <th>Comment</th>
            <th>Posted On</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pendingComments as $comm): ?>
            <tr>
              <td>
                <input type="checkbox" name="ids[]" value="<?= $comm['id']; ?>" class="comment-checkbox">
              </td>
              <td><?= htmlspecialchars($comm['author'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?= htmlspecialchars(strip_tags($comm['comment'])) ?></td>
              <td><?= htmlspecialchars(date('d F Y', strtotime($comm['created_at']))); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </form>
  <?php else: ?>
    <p>No pending comments for moderation.</p>
  <?php endif; ?>

  <script>
  document.getElementById('select-all-comments').onclick = function() {
      let checkboxes = document.getElementsByClassName('comment-checkbox');
      for (let checkbox of checkboxes) {
          checkbox.checked = this.checked;
      }
  }
  </script>
</div>

<?php include APP_DIR . '/admin/footer-auth.php'; ?>

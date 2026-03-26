<?php
// app/admin/admin-categories.php
use App\Controllers\CategoryController;
use App\Helpers\AuthHelper;
use App\Helpers\CsrfHelper; // Import the Helper

// Ensure only admin users can access this page
AuthHelper::requireAdmin($auth);

$pageTitle = "Admin - Categories";
$_BSCRIPTS = "<script>
  (function () {
    'use strict'
    const forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms)
      .forEach(function (form) {
        form.addEventListener('submit', function (event) {
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
          }
          form.classList.add('was-validated');
        }, false);
      });
  })();
</script>";

include APP_DIR . '/admin/header-auth.php';

$categoryController = new CategoryController();
$message = '';

// Process form submissions for creating, updating, or deleting a category.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. GLOBAL CSRF CHECK: Protects all category actions
    if (!CsrfHelper::isValid($_POST['csrf_token'] ?? '')) {
        $message = "Security error: Invalid session token. Please refresh the page.";
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'create') {
            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            if (!empty($name) && !empty($slug)) {
                if ($categoryController->createCategory($name, $slug)) {
                    $message = "Category created successfully.";
                } else {
                    $message = "Error creating category.";
                }
            } else {
                $message = "Please fill in all fields.";
            }
        } elseif ($action === 'update') {
            $id = $_POST['id'] ?? '';
            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            if (!empty($id) && !empty($name) && !empty($slug)) {
                if ($categoryController->updateCategory($id, $name, $slug)) {
                    $message = "Category updated successfully.";
                } else {
                    $message = "Error updating category.";
                }
            } else {
                $message = "Please fill in all fields for update.";
            }
        } elseif ($action === 'delete') {
            $id = $_POST['id'] ?? '';
            if (!empty($id)) {
                try {
                    if ($categoryController->deleteCategory($id)) {
                        $message = "Category deleted successfully.";
                    } else {
                        $message = "Error deleting category.";
                    }
                } catch (Exception $e) {
                    $message = "Error deleting category: " . $e->getMessage();
                }
            } else {
                $message = "Invalid category ID.";
            }
        }
    }
}

// Retrieve all categories for display.
$categories = $categoryController->getAllCategories();
?>

<div class="container">
  <?php if ($message): ?>
    <div class="alert alert-info" role="alert">
      <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
    </div>
  <?php endif; ?>
  
  <h1>Manage Categories</h1>
  
  <div class="mb-4 card p-3 shadow-sm">
    <h3>Create New Category</h3>
    <form method="post" action="" class="needs-validation" novalidate>
      <input type="hidden" name="csrf_token" value="<?= $csrfToken; ?>">
      <input type="hidden" name="action" value="create">
      
      <div class="row g-3 align-items-end">
        <div class="col-md-5">
            <label for="create-name" class="form-label">Category Name</label>
            <input type="text" class="form-control" id="create-name" name="name" required>
        </div>
        <div class="col-md-5">
            <label for="create-slug" class="form-label">Category Slug</label>
            <input type="text" class="form-control" id="create-slug" name="slug" required>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Create</button>
        </div>
      </div>
    </form>
  </div>
  
  <h3>Existing Categories</h3>
  <?php if (count($categories) > 0): ?>
    <table class="table table-bordered align-middle">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Name & Slug</th>
          <th>Created At</th>
          <th>Update</th>
          <th>Delete</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($categories as $cat): ?>
          <tr>
            <td><?php echo htmlspecialchars($cat['id'], ENT_QUOTES, 'UTF-8'); ?></td>
            <form method="post" action="" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?= $csrfToken; ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($cat['id'], ENT_QUOTES, 'UTF-8'); ?>">
                
                <td>
                    <input type="text" class="form-control form-control-sm mb-1" name="name" value="<?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    <input type="text" class="form-control form-control-sm" name="slug" value="<?php echo htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </td>
                <td><?php echo htmlspecialchars(date('d M Y', strtotime($cat['created_at']))); ?></td>
                <td>
                    <button type="submit" class="btn btn-primary btn-sm w-100">Update</button>
                </td>
            </form>
            <td>
              <form method="post" action="" onsubmit="return confirm('Are you sure you want to delete this category?');">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken; ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($cat['id'], ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit" class="btn btn-danger btn-sm w-100">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No categories available.</p>
  <?php endif; ?>
</div>

<?php include APP_DIR . '/admin/footer-auth.php'; ?>

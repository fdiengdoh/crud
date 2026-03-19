<?php
// app/admin/admin-categories.php
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

use App\Controllers\CategoryController;
use App\Helpers\AuthHelper;
AuthHelper::requireAdmin($auth);

$categoryController = new CategoryController();
$message = '';

// Process form submissions for creating, updating, or deleting a category.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
  
  <!-- Form to Create a New Category -->
  <div class="mb-4">
    <h3>Create New Category</h3>
    <form method="post" action="" class="needs-validation" novalidate>
      <input type="hidden" name="action" value="create">
      <div class="mb-3">
        <label for="create-name" class="form-label">Category Name</label>
        <input type="text" class="form-control" id="create-name" name="name" placeholder="Category Name" required>
        <div class="invalid-feedback">
          Please provide a category name.
        </div>
      </div>
      <div class="mb-3">
        <label for="create-slug" class="form-label">Category Slug</label>
        <input type="text" class="form-control" id="create-slug" name="slug" placeholder="category-slug" required>
        <div class="form-text">
          Please provide a unique slug.
        </div>
      </div>
      <button type="submit" class="btn btn-primary">Create Category</button>
    </form>
  </div>
  
  <!-- List Existing Categories with options to update or delete -->
  <h3>Existing Categories</h3>
  <?php if (count($categories) > 0): ?>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Slug</th>
          <th>Created At</th>
          <th>Update</th>
          <th>Delete</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($categories as $cat): ?>
          <tr>
            <td><?php echo htmlspecialchars($cat['id'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td>
              <form method="post" action="" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($cat['id'], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </td>
            <td>
                <input type="text" class="form-control" name="slug" value="<?php echo htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </td>
            <td><?php echo htmlspecialchars(date('d F Y', strtotime($cat['created_at']))); ?></td>
            <td>
                <button type="submit" class="btn btn-primary btn-sm">Update</button>
              </form>
            </td>
            <td>
              <form method="post" action="" onsubmit="return confirm('Are you sure you want to delete this category?');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($cat['id'], ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
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

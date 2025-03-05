<?php
// app/my-posts.php
require_once __DIR__ . '/../../init.php';

// (Additional authentication checks can be done in init.php)
// Set a page title for the header if desired
$pageTitle = "Admin - Categories";
include APP_DIR . '/admin/header-auth.php';

use App\Controllers\CategoryController;
use App\Helpers\AuthHelper;
AuthHelper::requireAdmin($auth);

$categoryController = new CategoryController();
$message = '';

// Process form submissions for creating or updating a category.
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
    }
    // (Optional) You could add update and delete actions here as well.
}

// Retrieve all categories for display.
$categories = $categoryController->getAllCategories();
// ... (rest of your page content)
?>

  <div class="container">
    <?php if ($message): ?>
      <div class="alert alert-info" role="alert">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>
    <h1>Manage Categories</h1>
    
    <!-- Form to Create a New Category -->
    <div class="mb-4">
      <h3>Create New Category</h3>
      <form method="post" action="" class="needs-validation" novalidate>
        <input type="hidden" name="action" value="create">
        <div class="mb-3">
          <label for="name" class="form-label">Category Name</label>
          <input type="text" class="form-control" id="name" name="name" placeholder="Category Name" required>
          <div class="invalid-feedback">
            Please provide a category name.
          </div>
        </div>
        <div class="mb-3">
          <label for="slug" class="form-label">Category Slug</label>
          <input type="text" class="form-control" id="slug" name="slug" placeholder="category-slug" required>
          <div class="invalid-feedback">
            Please provide a unique slug.
          </div>
        </div>
        <button type="submit" class="btn btn-primary">Create Category</button>
      </form>
    </div>
    
    <!-- List Existing Categories -->
    <h3>Existing Categories</h3>
    <?php if (count($categories) > 0): ?>
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Slug</th>
            <th>Created At</th>
            <!-- (Optional: actions for update/delete can be added) -->
          </tr>
        </thead>
        <tbody>
          <?php foreach ($categories as $cat): ?>
            <tr>
              <td><?php echo htmlspecialchars($cat['id']); ?></td>
              <td><?php echo htmlspecialchars($cat['name']); ?></td>
              <td><?php echo htmlspecialchars($cat['slug']); ?></td>
              <td><?php echo htmlspecialchars(date('d F Y', strtotime($cat['created_at']))); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No categories available.</p>
    <?php endif; ?>
    
    <a href="<?= BASE_URL ?>/" class="btn btn-secondary mt-3">Back to Home</a>
  </div>
  
  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
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
  </script>
<!-- Your page content goes here -->
<?php include APP_DIR . '/admin/footer-auth.php'; ?>

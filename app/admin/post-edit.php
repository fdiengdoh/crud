<?php
// app/admin/post-edit.php
require_once __DIR__ . '/../../init.php';

use App\Controllers\PostController;
use App\Controllers\CategoryController;
use Delight\Auth\Auth;

$userId = $auth->getUserId();
$postController = new PostController();
$categoryController = new CategoryController();
$message = '';

$postId = $_GET['id'] ?? null;
if (!$postId) {
    header("Location: " . BASE_URL . "/my-posts");
    exit;
}

$post = $postController->show($postId);
if (!$post || $post['user_id'] != $userId) {
    header("Location: " . BASE_URL . "/my-posts/?msg=" . urlencode("Unauthorized access"));
    exit;
}

// Retrieve categories assigned to this post
$assignedCategories = $postController->getCategoriesForPost($postId);
$assignedCategoryIds = array_map(function($cat) { return $cat['id']; }, $assignedCategories);

// Retrieve all categories for selection
$allCategories = $categoryController->getAllCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title         = trim($_POST['title'] ?? '');
    $content       = trim($_POST['content'] ?? '');
    $slug          = trim($_POST['slug'] ?? '');          // Optional custom slug
    $description   = trim($_POST['description'] ?? '');   // Optional description
    $keywords      = trim($_POST['keywords'] ?? '');      // Optional keywords
    $selectedCategories = $_POST['categories'] ?? [];
    $createdAt = trim($_POST['created-at'] ?? ''); // expecting format "YYYY-MM-DD HH:MM:SS"
  if (empty($createdAt)) {
      $createdAt = null;
  } 
    // Process feature image upload if a new file is provided
    $featureImage = $post['feature_image']; // default: keep existing image
    if (isset($_FILES['feature_image']) && $_FILES['feature_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = PUBLIC_DIR . '/image/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fileTmpPath = $_FILES['feature_image']['tmp_name'];
        $originalName = $_FILES['feature_image']['name'];
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $newFileName = uniqid('img_', true) . '.' . $extension;
        $destPath = $uploadDir . $newFileName;
        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $featureImage = '/image/' . $newFileName;
        }
    }
    
    // Update the post with additional fields: slug, description, keywords.
    $result = $postController->update($postId, $title, $content, $featureImage, $slug, $description, $keywords, $createdAt);
    if ($result) {
        $postController->assignCategoriesToPost($postId, $selectedCategories);
        $message = "Post updated successfully.";
    } else {
        $message = "Failed to update post.";
    }
    // Reload updated post and category assignments
    $post = $postController->show($postId);
    $assignedCategories = $postController->getCategoriesForPost($postId);
    $assignedCategoryIds = array_map(function($cat) { return $cat['id']; }, $assignedCategories);
}

$_SCRIPTS = "<!-- TinyMCE for rich text editing -->
  <script src=\"" . BASE_URL . "/js/vendor/tinymce/tinymce.min.js\" referrerpolicy=\"origin\"></script>
  <script>
    tinymce.init({
      selector: '#content',
      plugins: 'lists image link code',
      toolbar: 'undo redo | bold italic underline | bullist numlist | link image | code',
      automatic_uploads: true,
      images_upload_url: '" . BASE_URL . "/admin/upload-image',
    });
  </script>";

// Set a page title for the header if desired
$pageTitle = "My Posts - Edit Post";
include APP_DIR . '/admin/header-auth.php';
?>
<div class="container">
  <?php if ($message): ?>
    <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>
  <h1>Edit Post</h1>
  <form method="post" action="" class="needs-validation" novalidate enctype="multipart/form-data">
    <div class="mb-3">
      <label for="title" class="form-label">Title</label>
      <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
      <div class="invalid-feedback">Please enter a title.</div>
    </div>
    <div class="mb-3">
      <label for="slug" class="form-label">Custom Slug (Optional)</label>
      <input type="text" class="form-control" id="slug" name="slug" value="<?php echo htmlspecialchars($post['slug']); ?>" placeholder="e.g., my-custom-slug">
      <div class="form-text">Leave blank to auto-generate from the title.</div>
    </div>
    <div class="mb-3">
      <label for="description" class="form-label">Description (Optional)</label>
      <textarea class="form-control" id="description" name="description" rows="2" placeholder="Short description"><?php echo htmlspecialchars($post['description'] ?? ''); ?></textarea>
      <div class="form-text">If left blank, the first 100 characters of the content will be used.</div>
    </div>
    <div class="mb-3">
      <label for="keywords" class="form-label">Keywords (Optional)</label>
      <input type="text" class="form-control" id="keywords" name="keywords" value="<?php echo htmlspecialchars($post['keywords'] ?? ''); ?>" placeholder="Comma-separated keywords">
    </div>
    <div class="mb-3">
      <label for="content" class="form-label">Content</label>
      <textarea class="form-control" id="content" name="content" rows="10" required><?php echo htmlspecialchars($post['content']); ?></textarea>
      <div class="invalid-feedback">Please enter the content.</div>
    </div>
    <div class="mb-3">
      <label for="feature_image" class="form-label">Update Feature Image</label>
      <input type="file" class="form-control" id="feature_image" name="feature_image" accept="image/*">
    </div>
    <div class="mb-3">
      <label for="categories" class="form-label">Categories</label>
      <select name="categories[]" id="categories" class="form-select" multiple>
        <?php foreach ($allCategories as $cat): ?>
          <?php $selected = in_array($cat['id'], $assignedCategoryIds) ? 'selected' : ''; ?>
          <option value="<?php echo htmlspecialchars($cat['id']); ?>" <?php echo $selected; ?>>
            <?php echo htmlspecialchars($cat['name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
      <div class="form-text">Hold down Ctrl (Windows) or Command (Mac) to select multiple categories.</div>
    </div>
    <div class="mb-3">
      <label for="created_at" class="form-label">Select Date of Post (Optional)</label>
      <input type="date" id="created-at" class="form-control" name="created-at">
    </div>
    <button type="submit" class="btn btn-primary">Update Post</button>
  </form>
  <a href="<?= BASE_URL ?>/my-posts" class="btn btn-secondary mt-3">Back to My Posts</a>
</div>
  
<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  (function () {
    'use strict';
    const form = document.querySelector('.needs-validation');
    form.addEventListener('submit', function (event) {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  })();
</script>
<?php include APP_DIR . '/admin/footer-auth.php'; ?>

<?php
/*/ admin/post-edit.php
require_once __DIR__ . '/../../init.php';

use App\Controllers\PostController;
use App\Controllers\CategoryController;
use Delight\Auth\Auth;

$userId = $auth->getUserId();
$postController = new PostController();
$categoryController = new CategoryController();
$message = '';

$postId = $_GET['id'] ?? null;
if (!$postId) {
    header("Location: /my-posts");
    exit;
}

$post = $postController->show($postId);
if (!$post || $post['user_id'] != $userId) {
    header("Location: /my-posts/?msg=" . urlencode("Unauthorized access"));
    exit;
}

// Retrieve categories assigned to this post
$assignedCategories = $postController->getCategoriesForPost($postId);
$assignedCategoryIds = array_map(function($cat) { return $cat['id']; }, $assignedCategories);

// Retrieve all categories for selection
$allCategories = $categoryController->getAllCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $selectedCategories = $_POST['categories'] ?? [];
    
    // Process feature image upload if a new file is provided
    $featureImage = $post['feature_image']; // default: keep existing image
    if (isset($_FILES['feature_image']) && $_FILES['feature_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = PUBLIC_DIR . '/image/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fileTmpPath = $_FILES['feature_image']['tmp_name'];
        $originalName = $_FILES['feature_image']['name'];
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $newFileName = uniqid('img_', true) . '.' . $extension;
        $destPath = $uploadDir . $newFileName;
        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $featureImage = '/image/' . $newFileName;
        }
    }
    
    // Update the post; if a new feature image is uploaded, pass it to update()
    $result = $postController->update($postId, $title, $content, $featureImage);
    if ($result) {
        $postController->assignCategoriesToPost($postId, $selectedCategories);
        $message = "Post updated successfully.";
    } else {
        $message = "Failed to update post.";
    }
    // Reload updated post and category assignments
    $post = $postController->show($postId);
    $assignedCategories = $postController->getCategoriesForPost($postId);
    $assignedCategoryIds = array_map(function($cat) { return $cat['id']; }, $assignedCategories);
}

$_SCRIPTS = "<!-- TinyMCE for rich text editing -->
  <script src=\"/js/vendor/tinymce/tinymce.min.js\" referrerpolicy=\"origin\"></script>  <script>
    tinymce.init({
      selector: '#content',
    plugins: 'lists image link code',
    toolbar: 'undo redo | bold italic underline | bullist numlist | link image | code',
    automatic_uploads: true,
    images_upload_url: '". BASE_URL . "/admin/upload-image',
    });
  </script>";

// (Additional authentication checks can be done in init.php)
// Set a page title for the header if desired
$pageTitle = "My Posts - Edit Posts";
include APP_DIR . '/admin/header-auth.php';

// ... (rest of your page content)
?>
  <div class="container">
    <?php if ($message): ?>
      <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <h1>Edit Post</h1>
    <form method="post" action="" class="needs-validation" novalidate enctype="multipart/form-data">
      <div class="mb-3">
        <label for="title" class="form-label">Title</label>
        <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
        <div class="invalid-feedback">Please enter a title.</div>
      </div>
      <div class="mb-3">
        <label for="content" class="form-label">Content</label>
        <textarea class="form-control" id="content" name="content" rows="10" required><?php echo htmlspecialchars($post['content']); ?></textarea>
        <div class="invalid-feedback">Please enter the content.</div>
      </div>
      <div class="mb-3">
        <label for="feature_image" class="form-label">Update Feature Image</label>
        <input type="file" class="form-control" id="feature_image" name="feature_image" accept="image/*">
      </div>
      <div class="mb-3">
        <label for="categories" class="form-label">Categories</label>
        <select name="categories[]" id="categories" class="form-select" multiple>
          <?php foreach ($allCategories as $cat): ?>
            <?php $selected = in_array($cat['id'], $assignedCategoryIds) ? 'selected' : ''; ?>
            <option value="<?php echo htmlspecialchars($cat['id']); ?>" <?php echo $selected; ?>>
              <?php echo htmlspecialchars($cat['name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <div class="form-text">Hold down Ctrl (Windows) or Command (Mac) to select multiple categories.</div>
      </div>
      <button type="submit" class="btn btn-primary">Update Post</button>
    </form>
    <a href="<?= BASE_URL ?>/my-posts" class="btn btn-secondary mt-3">Back to My Posts</a>
  </div>
  
  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (function () {
      'use strict'
      const form = document.querySelector('.needs-validation');
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    })();
  </script>
<!-- Your page content goes here -->
<?php include APP_DIR . '/admin/footer-auth.php'; ?>
/* */
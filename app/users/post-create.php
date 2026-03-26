<?php
// app/admin/post-crate.php

use App\Controllers\PostController;
use App\Controllers\CategoryController;
use App\Controllers\MediaController;
use Delight\Auth\Role;
use App\Helpers\CsrfHelper;

// Only Authors (or higher) can create posts.
if ($auth->hasRole(Role::SUBSCRIBER)) {
    header("Location: " . BASE_URL . "/users/?msg=" . urlencode("Only authors can create posts."));
    exit;
}

$userId = $auth->getUserId();

$postController = new PostController();
$categoryController = new CategoryController();
$message = '';

// Retrieve all categories
$allCategories = $categoryController->getAllCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. THE SECURITY GATE: Validate the token before doing anything else
    $token = $_POST['csrf_token'] ?? '';
    
    if (!CsrfHelper::isValid($token)) {
        // Halt execution and show an error (or log it)
        header('HTTP/1.1 403 Forbidden');
        $message = "Security Error: Invalid or expired session token. Please refresh the page.";
        // Stop the script here so no files are uploaded and no DB records are created
        exit($message); 
    }

    $title         = trim($_POST['title'] ?? '');
    $content       = trim($_POST['content'] ?? '');
    $slug          = trim($_POST['slug'] ?? '');         // Optional custom slug
    $description   = trim($_POST['description'] ?? '');  // Optional description
    $keywords      = trim($_POST['keywords'] ?? '');     // Optional keywords
    $a_script      = trim($_POST['a_script'] ?? '');      // Optional additional scripts
    $selectedCategories = $_POST['categories'] ?? [];
    $createdAt      = trim($_POST['created-at'] ?? '');     // Optional created date
    $allowComments  = trim($_POST['allow-comments'] ?? '');     // Optional created date
    
    if (empty($createdAt)) {
      $createdAt = null;
    } 

    empty($allowComments) ? $allowComments = 0 : $allowComments = 1;

    // Process file upload for feature image
    $featureImage = null; // null
    if (isset($_FILES['feature_image']) && $_FILES['feature_image']['error'] === UPLOAD_ERR_OK) {

        $uploader = new MediaController();
        $uploadedPath = $uploader->handleUpload($_FILES['feature_image']);
        if($uploadedPath){
          $featureImage = BASE_URL . '/' . $uploadedPath['image'];
        }
    }
    
    /** 
     * Create post. If $slug is empty, the controller will auto-generate it.
     * The public create function has the following args
     * @param int    $userId
     * @param string $title
     * @param string $content
     * @param string $slug          (optional) - if not provided, auto-generated from the title.
     * @param string $featureImage  (optional) - defaults to null.
     * @param string $description   (optional) - if not provided, auto-generated from content.
     * @param string $keywords      (optional) - comma-separated keywords.
     * @param string $createdAt     (optional) - if not provided, defaults to current timestamp.
     * @param string $a_script      (optional)
     * @param bool   $allowComments (optional) - whether comments are allowed (default: true).
     * create($userId, $title, $content, $slug = null, $featureImage = null, $description = '', $keywords = '', $createdAt = null, $a_script = null, $allowComments = true)
     */
    $newPostId = $postController->create($userId, $title, $content, $slug, $featureImage, $description, $keywords, $createdAt, $a_script, $allowComments);
    
    if (!empty($selectedCategories) && $newPostId) {
        $postController->assignCategoriesToPost($newPostId, $selectedCategories);
    }
    
    $message = $newPostId ? "Post created successfully. It is currently in draft mode." : "Failed to create post.";
    /** */
}

$_TSCRIPTS = "<!-- TinyMCE for rich text editing -->
  <script src=\"" . LOGIN_URL . "/js/vendor/tinymce/tinymce.min.js\" referrerpolicy=\"origin\"></script>
  <script>
    tinymce.init({
    selector: '#content',
    license_key: 'gpl',
    plugins: 'lists image link code codesample',
    toolbar: 'undo redo | bold italic underline | bullist numlist | link image codesample | code',
    codesample_languages: [
        { text: 'HTML', value: 'html' },
        { text: 'JavaScript', value: 'javascript' },
        { text: 'CSS', value: 'css' },
        { text: 'PHP', value: 'php' }
    ],
    convert_urls: false,
    automatic_uploads: true,
    extended_valid_elements: 'i[class],span[class]',
    images_file_types: 'jpg,svg,webp,png,gif,bmp',
    sandbox_iframes: false,

    /* CUSTOM UPLOAD HANDLER FOR CSRF */
    images_upload_handler: (blobInfo, progress) => new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.withCredentials = false;
        xhr.open('POST', '" . LOGIN_URL . "/admin/upload-image');

        xhr.upload.onprogress = (e) => {
            progress(e.loaded / e.total * 100);
        };

        xhr.onload = () => {
            if (xhr.status < 200 || xhr.status >= 300) {
                reject('HTTP Error: ' + xhr.status);
                return;
            }

            const json = JSON.parse(xhr.responseText);

            if (!json || typeof json.location != 'string') {
                reject('Invalid JSON: ' + (json.error || xhr.responseText));
                return;
            }

            // In Promise-style, we use resolve() instead of success()
            resolve(json.location);
        };

        xhr.onerror = () => {
            reject('Image upload failed due to a network error.');
        };

        const formData = new FormData();
        formData.append('file', blobInfo.blob(), blobInfo.filename());
        
        // Injecting your CSRF token
        formData.append('csrf_token', '" . $csrfToken . "');

        xhr.send(formData);
      })
    });
  </script>";

// Set a page title for the header if desired
$pageTitle = "My Posts - Create New Post";
$_BSCRIPTS = "<script>
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
</script>";
include APP_DIR . '/admin/header-auth.php';
?>
<div class="container">
  <?php if ($message): ?>
    <div class="alert alert-info"><?php echo htmlspecialchars($message ?? ' ', ENT_QUOTES, 'UTF-8'); ?></div>
  <?php endif; ?>
  <h1>Create New Post</h1>
  <form method="post" action="" class="needs-validation" enctype="multipart/form-data" novalidate>
    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
    <div class="mb-3">
      <label for="title" class="form-label">Title</label>
      <input type="text" class="form-control" id="title" name="title" placeholder="Post title" required>
      <div class="invalid-feedback">Please enter a title.</div>
    </div>
    <div class="mb-3">
        <label for="a_script" class="form-label">Additonal Scripts <a href="#sample" class="badge rounded-pill text-bg-dark">see sample below</a></label>
        <textarea class="form-control" id="a_script" name="a_script" row="10"><?php echo htmlspecialchars($post['a_script'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?></textarea>
    </div>
    <div class="mb-3">
      <label for="content" class="form-label">Content</label>
      <textarea class="form-control" id="content" name="content" rows="10" placeholder="Write your post here..." required></textarea>
      <div class="invalid-feedback">Please enter the post content.</div>
    </div>
    <div class="mb-3">
      <label for="feature_image" class="form-label">Feature Image</label>
      <input type="file" class="form-control" id="feature_image" name="feature_image" accept="image/*">
    </div>
    <?php if (!empty($allCategories)): ?>
      <div class="mb-3">
        <label for="categories" class="form-label">Categories</label>
        <select name="categories[]" id="categories" class="form-select" multiple>
          <?php foreach ($allCategories as $cat): ?>
            <option value="<?php echo htmlspecialchars($cat['id'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?>">
              <?php echo htmlspecialchars($cat['name'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <div class="form-text">Hold down Ctrl (Windows) or Command (Mac) to select multiple categories.</div>
      </div>
    <?php endif; ?>
    <div class="mb-3">
      <label for="slug" class="form-label">Custom Slug (Optional)</label>
      <input type="text" class="form-control" id="slug" name="slug" placeholder="e.g., my-custom-slug">
      <div class="form-text">Leave blank to auto-generate from the title.</div>
    </div>
    <div class="mb-3">
      <label for="description" class="form-label">Description (Optional)</label>
      <textarea class="form-control" id="description" name="description" rows="2" placeholder="Short description"></textarea>
      <div class="form-text">If left blank, the first 100 characters of the content will be used.</div>
    </div>
    <div class="mb-3">
      <label for="keywords" class="form-label">Keywords (Optional)</label>
      <input type="text" class="form-control" id="keywords" name="keywords" placeholder="Comma-separated keywords">
    </div>
    <div class="mb-3">
      <label class="form-check-label" for="allow-comments">Allow Comments?</label>
      <div class="form-check form-switch">
        <input class="form-check-input" style="width:4em;height:2em;" name="allow-comments" type="checkbox" role="switch" id="allow-comments">
      </div>
    </div>
    <div class="mb-3">
      <label for="created_at" class="form-label">Select Date of Post (Optional)</label>
      <input type="datetime-local" id="created-at" class="form-control" name="created-at">
    </div>
    <button type="submit" class="btn btn-primary">Create Post</button>
  </form>
      <div>
          <p id="sample"><strong>Code for hilight.js</strong></p>
        <p>
            &lt;link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.11.1/styles/default.min.css"&gt;<br>&lt;script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.11.1/highlight.min.js"&gt;&lt;/script&gt;<br>&lt;script&gt;hljs.highlightAll();&lt;/script&gt;
        </p>
        <p><strong>Code for MathJax</strong></p>
        <p>&lt;script&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; MathJax = {<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; tex: {&nbsp;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; tags:'ams',<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; inlineMath: [['$', '$'], ['\\(', '\\)']],&nbsp;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; autoload: {gensymb: ['celsius', 'degree', 'micro', 'ohm', 'perthousand']},<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; },<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; svg: {fontCache: 'global'}<br>&nbsp; &nbsp; &nbsp; &nbsp; };<br>&lt;/script&gt;<br>&lt;script rel="preload" id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-svg.js" as="script"&gt;&lt;/script&gt;</p>
    </div>
</div>

<?php include APP_DIR . '/admin/footer-auth.php'; ?>
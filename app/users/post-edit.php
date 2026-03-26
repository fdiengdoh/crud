<?php
// app/admin/post-edit.php
// require_once __DIR__ . '/../../init.php';

use App\Controllers\PostController;
use App\Controllers\MediaController;
use App\Controllers\CategoryController;
use Delight\Auth\Auth;
use App\Utils\Cache;
use App\Helpers\CsrfHelper;

// Load cache configuration and instantiate the Cache utility.
$config = require (CACHE_CONFIG);
$cache = new Cache($config);

$userId = $auth->getUserId();
$postController = new PostController();
$categoryController = new CategoryController();
$message = '';

$postId = $_GET['id'] ?? null;
if (!$postId) {
    header("Location: " . LOGIN_URL . "/my-posts");
    exit;
}

$post = $postController->show($postId);
if (!$post || $post['user_id'] != $userId) {
    header("Location: " . LOGIN_URL . "/users/?msg=" . urlencode("Unauthorized access"));
    exit;
}

if(isset($_GET['views'])){
    $views = $_GET['views'];
    $stmt = $pdo->prepare("UPDATE posts SET views = ? WHERE id = ?");
    $update = $stmt->execute([$views, $postId]);
    if($update){
        header("Location: " . LOGIN_URL . "/users/?msg=" . urlencode("Views Updated"));
    }else{
        header("Location: " . LOGIN_URL . "/users/?msg=" . urlencode("Error Updating"));
    }
    exit;
}
// Retrieve categories assigned to this post
$assignedCategories = $postController->getCategoriesForPost($postId);
$assignedCategoryIds = array_map(function($cat) { return $cat['id']; }, $assignedCategories);

// Retrieve all categories for selection
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
    $a_script      = trim($_POST['a_script'] ?? '');      // Optional additional scripts
    $slug          = trim($_POST['slug'] ?? '');          // Optional custom slug
    $description   = trim($_POST['description'] ?? '');   // Optional description
    $keywords      = trim($_POST['keywords'] ?? '');      // Optional keywords
    $selectedCategories = $_POST['categories'] ?? [];
    $createdAt = trim($_POST['created-at'] ?? '');        // expecting format "YYYY-MM-DD HH:MM:SS"
    $allowComments  = trim($_POST['allow-comments'] ?? '');     // Optional created date
    
    if (empty($createdAt)) {
        $createdAt = null;
    }
    
    empty($allowComments) ? $allowComments = 0 : $allowComments = 1;

    // Process file upload for feature image
    $featureImage = $post['feature_image']; // default: keep existing image
    if (isset($_FILES['feature_image']) && $_FILES['feature_image']['error'] === UPLOAD_ERR_OK) {

        $uploader = new MediaController();
        $uploadedPath = $uploader->handleUpload($_FILES['feature_image']);
        if($uploadedPath){
          $featureImage = BASE_URL . '/' . $uploadedPath['image'];
        }
    }
    
    /** Update the post with additional fields: slug, description, keywords.
     * * @param int         $postId
     * @param string      $title
     * @param string      $content
     * @param string|null $featureImage   If provided, update the feature image; otherwise, keep the current one.
     * @param string      $slug           (Optional) Custom slug. Auto-generated from the title if empty.
     * @param string      $description    (Optional) Custom description. Auto-generated from content if empty.
     * @param string      $keywords       (Optional) Comma-separated keywords.
     * @param string|null $createdAt      (Optional) New created_at datetime in 'Y-m-d H:i:s' format. If not provided, leaves the current value unchanged.
     * @param string|null $a_script       (Optional)
     * @param bool|null   $allowComments  (Optional) If provided, update whether comments are allowed.
     * @return bool
     * public function update($postId, $title, $content, $featureImage = null, $slug = '', $description = '', $keywords = '', $createdAt = null, $a_script = null, $allowComments = null)
     * */
    $result = $postController->update($postId, $title, $content, $featureImage, $slug, $description, $keywords, $createdAt, $a_script, $allowComments);
    $cache->clearCache('/'.$slug);
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
$pageTitle = "My Posts - Edit Post";
include APP_DIR . '/admin/header-auth.php';
?>
<div class="container">
  <?php if ($message): ?>
    <div class="alert alert-info"><?php echo htmlspecialchars($message ?? ' ', ENT_QUOTES, 'UTF-8'); ?></div>
  <?php endif; ?>
  <h1>Edit Post</h1>
  <form method="post" action="" class="needs-validation" novalidate enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
    <div class="mb-3">
      <label for="title" class="form-label">Title</label>
      <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($post['title'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?>" required>
      <div class="invalid-feedback">Please enter a title.</div>
    </div>
    <div class="mb-3">
        <label for="a_script" class="form-label">Additonal Scripts <a href="#sample" class="badge rounded-pill text-bg-dark">see sample below</a></label>
        <textarea class="form-control" id="a_script" name="a_script" row="10"><?php echo htmlspecialchars($post['a_script'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?></textarea>
    </div>
    <div class="mb-3">
      <label for="content" class="form-label">Content</label>
      <textarea class="form-control" id="content" name="content" rows="30" required><?php echo htmlspecialchars($post['content'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?></textarea>
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
          <option value="<?php echo htmlspecialchars($cat['id'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?>" <?php echo $selected; ?>>
            <?php echo htmlspecialchars($cat['name'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?>
          </option>
        <?php endforeach; ?>
      </select>
      <div class="form-text">Hold down Ctrl (Windows) or Command (Mac) to select multiple categories.</div>
    </div>
    <div class="mb-3">
      <label for="slug" class="form-label">Custom Slug (Optional)</label>
      <input type="text" class="form-control" id="slug" name="slug" value="<?php echo htmlspecialchars($post['slug'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?>" placeholder="e.g., my-custom-slug">
      <div class="form-text">Leave blank to auto-generate from the title.</div>
    </div>
    <div class="mb-3">
      <label for="description" class="form-label">Description (Optional)</label>
      <textarea class="form-control p-3" id="description" name="description" rows="2" placeholder="Short description"><?php echo htmlspecialchars($post['description'] ?? ''); ?></textarea>
      <div class="form-text">If left blank, the first 100 characters of the content will be used.</div>
    </div>
    <div class="mb-3">
      <label for="keywords" class="form-label">Keywords (Optional)</label>
      <input type="text" class="form-control" id="keywords" name="keywords" value="<?php echo htmlspecialchars($post['keywords'] ?? ''); ?>" placeholder="Comma-separated keywords">
    </div>

    <div class="mb-3">
      <label class="form-check-label" for="allow-comments">Allow Comments?</label>
      <div class="form-check form-switch">
        <input class="form-check-input" style="width:3em;height:1.5em;" name="allow-comments" type="checkbox" role="switch" id="allow-comments">
      </div>
    </div>

    <div class="mb-3">
      <label for="created_at" class="form-label">Select Date of Post (Optional)</label>
      <input type="datetime-local" id="created-at" class="form-control" name="created-at">
    </div>
    <button type="submit" class="btn btn-primary">Update Post</button>
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
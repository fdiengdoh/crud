<?php
// app/category.php
require_once __DIR__ . '/../init.php';

use App\Controllers\CategoryController;
use App\Controllers\PostController;

$categoryController = new CategoryController();
$postController = new PostController();

// Get the category slug from the URL (via /label/ route)
$slug = $_GET['slug'] ?? '';
if (!$slug) {
    include APP_DIR . '/404.php';
    exit;
}

// Fetch the category using the slug
$category = $categoryController->getCategoryBySlug($slug);
if (!$category) {
    include APP_DIR . '/404.php';
    exit;
}

// Pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
// Use environment variable POSTS_PER_PAGE, defaulting to 10 if not set
$limit = POSTS_PER_PAGE;

// Fetch posts for this category with pagination
$posts = $postController->getPosts($slug, $page, $limit);
$totalPosts = $postController->countPosts($slug);
$totalPages = ceil($totalPosts / $limit);

// Set the page title and include the header
$title = htmlspecialchars($category['name']) . " Posts &raquo; fdiengdoh.com";
$description = "Posts on the topic of " . htmlspecialchars($category['name']);
include APP_DIR . '/include/header.php';

?>
<main>
            <div class="row m-0">
               <div class="col-md-8">
                  <!-- Category List Start -->
                  <article class="p-3">
                     <h2 class="link-body-emphasis mb-1"><?= $category['name'] ?> Posts</h2>
                     <div class="blog-post-meta"></div>
                     <?php if (!empty($posts)): ?>
                     <!-- First Blog of Category -->
                     <?php foreach ($posts as $post): ?>
                     <div class="row p-3">
                        <div class="card p-0 mb-3">
                           <div class="row g-0">
                             <div class="col-md-4">
                                  <img src="<?= BASE_URL . '/' . htmlspecialchars($post['feature_image']); ?>" class="rounded-start" height="225" width="100%" style="object-fit: cover;" alt="<?= htmlspecialchars($post['title']); ?>">
                             </div>
                             <div class="col-md-8 z-10 bg-light">
                               <div class="card-body">
                                  <h5 class="card-title"><?= htmlspecialchars($post['title']); ?></h5>
                                  <p class="card-text"><?= htmlspecialchars($post['excerpt']); ?></p>
                                  <p class="card-text">
                                    <small class="text-body-secondary">Last updated on <?= date('d F Y', strtotime($post['updated_at'])) ?></small>
                                    <a href="<?= BASE_URL . '/' . htmlspecialchars($post['slug']); ?>" class="btn btn-link">Read More</a>
                                  </p>
                               </div>
                             </div>
                           </div>
                         </div>
                     </div>
                     <?php endforeach; ?>
                     <div class="blog-post-meta"></div>
                     <!-- Post Navigation -->
                     <?php if ($totalPages > 1): ?>
                     <nav aria-label="<?= $category['name'] ?> Navigation">
                        <ul class="pagination justify-content-center">
                          <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?= BASE_URL ?>/search/label/<?= htmlspecialchars($slug); ?>?page=<?= $page - 1; ?>" tabindex="-1">&laquo;</a>
                          </li>
                          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($i === $page) ? 'active' : ''; ?>">
                              <a class="page-link" href="<?= BASE_URL ?>/search/label/<?= htmlspecialchars($slug); ?>?page=<?= $i; ?>"><?= $i; ?></a>
                            </li>
                          <?php endfor; ?>
                          <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?= BASE_URL ?>/search/label/<?= htmlspecialchars($slug); ?>?page=<?= $page + 1; ?>">&raquo;</a>
                          </li>

                        </ul>
                      </nav>
                      <?php endif; ?>
                      <?php else: ?>
                        <p>No published posts found in this category.</p>
                      <?php endif; ?>
                  </article>
                  <!-- Category List Ends -->
                   <!-- Pagination of Categories -->
                  
               </div>
            <?php require_once(APP_DIR . '/include/sidebar.php'); ?>
            </div>
            <!-- Row Ends -->
         </main>

<?php include APP_DIR . '/include/footer.php'; ?>

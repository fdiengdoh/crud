<?php
// app/home.php
require_once __DIR__ . '/../init.php';

use App\Controllers\PostController;
use App\Controllers\CategoryController;

$postController = new PostController();
$categoryController = new CategoryController();

try{
   // Retrieve 5 latest featured posts from category "featured-posts"
   $featuredPosts = $postController->getPosts(FEATURED_POST);
}catch (\Exception $e){
   header ( 'Location: /install.php');
}
$featuredPosts = array_slice($featuredPosts, 0, 5);

// Retrieve all published posts (excerpts generated in controller)
$publishedPosts = $postController->getPosts();

// Retrieve all categories
$categories = HOME_CATEGORIES;

// Set the page title for header
$title = "Home &raquo; fdiengdoh.com";
$description = "Farlando Diengdoh Blogging randomly about Chemistry, Web Apps, Technology, Culture etc.";

include APP_DIR . '/include/header.php';

?>
<!--=========================== THIS PART IS ONLY FOR HOME PAGE ===========================-->
<!-- Image Carousel -->
<?php if (!empty($featuredPosts)): ?>	
<!-- Slider via Carousel Start -->
<div id="featured-carousel" class="carousel slide mb-3" data-bs-ride="carousel">

   <div class="carousel-inner">
      <?php foreach ($featuredPosts as $index => $post): ?>
      <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
         <img src="<?= BASE_URL . '/' . htmlspecialchars($post['feature_image']); ?>" class="carousel-img" alt="">
         <div class="container">
            <div class="carousel-caption rounded p-3">
               <h2 class="text-truncate"><?= htmlspecialchars($post['title']); ?></h2>
               <p class="text-truncate"><?= $post['excerpt'] ?></p>
               <p><a class="btn btn-primary" href="<?= BASE_URL . '/' . htmlspecialchars($post['slug']); ?>">Read More...</a></p>
            </div>
         </div>
      </div>
      <?php endforeach; ?>
   </div>
   <button class="carousel-control-prev" type="button" data-bs-target="#featured-carousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Previous</span>
   </button>
   <button class="carousel-control-next" type="button" data-bs-target="#featured-carousel" data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Next</span>
   </button>
</div>
<!-- Slider via Carousel Ends -->
<?php endif; ?>
<main>
<div class="row m-0">
   <!-- Main Area Content -->
   <div class="col-md-8">
      <div class="container">
         <?php foreach($categories as $category): ?>
         <?php 
            $catPosts = $postController->getPosts($category); //Get posts of a category 
            $catPosts = array_slice($catPosts, 0, 3);
         ?>
         <!-- Category Start -->
         <div class="h5 divider mb-3 py-3 text-uppercase">
            <span class="bg-dark p-2 px-3 text-white"><?= $category ?></span>
            <span class="float-end fst-italic"><a href="/search/label/<?= $category ?>">View All <i class="bi bi-chevron-double-right"></i></a></span>
         </div>
         <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
            <?php foreach ($catPosts as $catPost): ?>
            <!-- Posts -->
            <div class="col">
               <div class="card shadow-sm">
                  <img src="<?= $catPost['feature_image'] ?>" height="225" width="100%" style="object-fit: cover;" alt="<?= $catPost['title'] ?>">
                  <div class="card-body">
                     <p class="card-text"><?= $catPost['excerpt'] ?></p>
                     <div class="d-flex justify-content-end align-items-center">
                        <a href="<?= $catPost['slug'] ?>" class="btn btn-dark text-align-end">Read More</a>
                     </div>
                  </div>
               </div>
            </div>
            <!-- Posts Ends -->
            <?php endforeach; ?>
         </div>
         <!-- Category End -->
         <?php endforeach; ?>
      </div>
   </div>
   <!-- Main Area Content Ends -->
   <?php require_once(APP_DIR . '/include/sidebar.php'); ?>
   </div>
<!-- Row Ends -->
</main>


<?php include APP_DIR . '/include/footer.php'; ?>
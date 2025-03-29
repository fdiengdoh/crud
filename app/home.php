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
//Include carusel
$TScripts .= '<link rel="preload" href="/css/carousel.min.css" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">';

include APP_DIR . '/include/header.php';

//--=========================== THIS PART IS ONLY FOR HOME PAGE ===========================-
if (!empty($featuredPosts)): ?>	

<!-- Image Carousel -->
<!-- Slider via Carousel Start -->
<div id="featured-carousel" class="carousel slide mb-3" data-bs-ride="carousel">
   <div class="carousel-inner">
      <?php foreach ($featuredPosts as $index => $post): ?>
      <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
         <img  src="<?= htmlspecialchars($post['feature_image'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?>" class="carousel-img " alt="<?= $post['title'] ?>">
         <div class="container">
            <div class="carousel-caption rounded p-3">
               <h2 class="text-truncate"><?= htmlspecialchars($post['title'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?></h2>
               <p class="text-truncate"><?= $post['excerpt'] ?></p>
               <p><a class="btn btn-primary" href="<?= BASE_URL . '/' . htmlspecialchars($post['slug'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?>">Read More...</a></p>
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
            $catPosts = array_slice($catPosts, 0, 4);
         ?>
         <!-- Category Start -->
         <div class="h5 divider mb-3 py-3 text-uppercase">
            <span class="bg-dark p-2 px-3 text-white"><?= $category ?></span>
            <span class="float-end fst-italic"><a href="/search/label/<?= $category ?>">View All <i class="bi bi-chevron-double-right"></i></a></span>
         </div>
         <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
            <div class="col-md-7">
               <!-- Posts -->
               <div class="col">
                  <div class="card shadow-sm">
                     <img  src="<?= $catPosts[0]['feature_image'] ?>" height="200" width="100%" style="object-fit: cover;" alt="<?= $catPosts[0]['title'] ?>">
                     <div class="card-body">
                        <h5 class="card-text"><?= $catPosts[0]['title'] ?></h5>
                        <p class="card-text"><?= $catPosts[0]['excerpt'] ?></p>
                        <div class="d-flex justify-content-end align-items-center">
                           <a href="<?= $catPosts[0]['slug'] ?>" class="btn btn-dark text-align-end">Read More</a>
                        </div>
                     </div>
                  </div>
               </div>
               <!-- Posts Ends -->
            </div>
            <div class="col-md-5">
            <?php array_shift($catPosts); ?>
            <?php foreach ($catPosts as $catPost): ?>
               <!-- Posts -->
               <div class="col mb-2">
                  <div class="card"><a href="<?= $catPost['slug'] ?>">
                     <img src="<?= $catPost['feature_image'] ?>" class="card-img" height="120" width="100%" style="object-fit: cover;" alt="<?= $catPost['title'] ?>">
                     <div class="card-img-overlay">
                        <p class="card-title text-bg-dark opacity-75 text-white p-2"><?= $catPost['title'] ?></p>
                     </div>
                     </a>
                  </div>
               </div>
               <!-- Posts Ends -->
            <?php endforeach; ?>
            </div>
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
<?php
$postController ? $popularPosts = $postController->getPopularPosts(POPULAR_POST): $popularPosts = (new PostController())->getPopularPosts(POPULAR_POST);
$postController ? $recentPosts = $postController->getRecentPosts(RECENT_POST): $recentPosts = (new PostController())->getRecentPosts(RECENT_POST);
?>

<!-- Side Bar Start -->
<div class="col-md-4">
   <div class="p-3">
      <div class="mb-3">
         <h4 class="text-uppercase fst-italic">About Me</h4>
         <p class="mb-0 border-top">Farlando Diengdoh, randomly blogging about Chemistry, Technology, PHP, HTML etc</p>
      </div>
      <div>
         <h4 class="text-uppercase fst-italic">Recent posts</h4>
         <ul class="list-unstyled">
            <?php foreach($recentPosts as $pPost): ?>
            <li>
               <a class="d-flex flex-column flex-lg-row gap-3 align-items-start align-items-lg-center py-3 link-body-emphasis text-decoration-none border-top" href="/<?= $pPost['slug'] ?>">
                  <div class="col-lg-6">
                     <img src="<?= $pPost['feature_image'] ?>"class="v-100 w-100" style="object-fit: cover;" alt="<?= $pPost['title'] ?>">
                  </div>
                  <div class="col-lg-6">
                     <h5 class="mb-0"><?= $pPost['title'] ?></h5>
                     <small class="text-body-secondary"><?= date('d M Y', strtotime($pPost['created_at'])) ?></small>
                  </div>
               </a>
            </li>
            <?php endforeach; ?>
         </ul>
      </div>
      <div>
         <h4 class="text-uppercase fst-italic">Popular posts</h4>
         <ul class="list-unstyled">
            <?php foreach($popularPosts as $pPost): ?>
            <li>
               <a class="d-flex flex-column flex-lg-row gap-3 align-items-start align-items-lg-center py-3 link-body-emphasis text-decoration-none border-top" href="/<?= $pPost['slug'] ?>">
                  <div class="col-lg-6">
                  <img src="<?= $pPost['feature_image'] ?>" class="v-100 w-100" style="object-fit: cover;" alt="<?= $pPost['title'] ?>">
                  </div>
                  <div class="col-lg-6">
                     <h5 class="mb-0"><?= $pPost['title'] ?></h5>
                     <small class="text-body-secondary"><?= date('d M Y', strtotime($pPost['created_at'])) ?></small>
                  </div>
               </a>
            </li>
            <?php endforeach; ?>
         </ul>
      </div>
      <div>
          <h4 class="text-uppercase fst-italic border-bottom">Categories</h4>
          <?php $categories = $categoryController->getAllCategories() ; ?>
          <?php foreach ($categories as $category) : ?>
          <a class="btn btn-dark m-1" href="<?= BASE_URL ?>/search/label/<?= $category['slug'] ?>"><?= $category['name'] ?></a>
          <?php endforeach; ?>
      </div>
   </div>
</div>
<!-- Side Bar Ends -->

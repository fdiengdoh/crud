<?php
$postController ? $popularPosts = $postController->getPopularPosts(POPULAR_POST): $popularPosts = (new PostController())->getPopularPosts(POPULAR_POST);
$postController ? $recentPosts = $postController->getRecentPosts(RECENT_POST): $recentPosts = (new PostController())->getRecentPosts(RECENT_POST);
?>

<!-- Side Bar Start -->
<div class="col-md-4">
   <div class="p-3">
      <div class="mb-3">
         <h4 class="fst-italic">About Me</h4>
         <p class="mb-0">Farlando Diengdoh, randomly blogging about Chemistry, Technology, PHP, HTML etc</p>
      </div>
      <div>
         <h4 class="fst-italic">Recent posts</h4>
         <ul class="list-unstyled">
            <?php foreach($recentPosts as $pPost): ?>
            <li>
               <a class="d-flex flex-column flex-lg-row gap-3 align-items-start align-items-lg-center py-3 link-body-emphasis text-decoration-none border-top" href="/<?= $pPost['slug'] ?>">
                  <div class="col-lg-6">
                     <img src="<?= BASE_URL ?>/<?= $pPost['feature_image'] ?>"class="v-100 w-100" style="object-fit: cover;" alt="<?= $pPost['title'] ?>">
                  </div>
                  <div class="col-lg-6">
                     <h6 class="mb-0"><?= $pPost['title'] ?></h6>
                     <small class="text-body-secondary"><?= date('d M Y', strtotime($pPost['created_at'])) ?></small>
                  </div>
               </a>
            </li>
            <?php endforeach; ?>
         </ul>
      </div>
      <div>
         <h4 class="fst-italic">Popular posts</h4>
         <ul class="list-unstyled">
            <?php foreach($popularPosts as $pPost): ?>
            <li>
               <a class="d-flex flex-column flex-lg-row gap-3 align-items-start align-items-lg-center py-3 link-body-emphasis text-decoration-none border-top" href="/<?= $pPost['slug'] ?>">
                  <div class="col-lg-6">
                  <img src="<?= BASE_URL ?>/<?= $pPost['feature_image'] ?>" class="v-100 w-100" style="object-fit: cover;" alt="<?= $pPost['title'] ?>">
                  </div>
                  <div class="col-lg-6">
                     <h6 class="mb-0"><?= $pPost['title'] ?></h6>
                     <small class="text-body-secondary"><?= date('d M Y', strtotime($pPost['created_at'])) ?></small>
                  </div>
               </a>
            </li>
            <?php endforeach; ?>
         </ul>
      </div>
      <div class="p-4">
         <h4 class="fst-italic">Elsewhere</h4>
         <ol class="list-unstyled">
            <li><a href="#">GitHub</a></li>
            <li><a href="#">Twitter</a></li>
            <li><a href="#">Facebook</a></li>
         </ol>
      </div>
   </div>
</div>
<!-- Side Bar Ends -->

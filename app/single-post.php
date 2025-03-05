<?php
// app/single-post.php
require_once __DIR__ . '/../init.php';

use App\Controllers\PostController;
use App\Controllers\CommentController;

$postController = new PostController();
$commentController = new CommentController();

// Use 'slug' (or 'id' if slug not available) from the URL to retrieve the post
$identifier = $_GET['slug'] ?? $_GET['id'] ?? null;
if (!$identifier) {
    include APP_DIR . '/404.php';
    exit;
}

$post = $postController->show($identifier);
if (!$post || $post['status'] !== 'published') {
    include APP_DIR . '/404.php';
    exit;
}

// After successfully fetching the $post:
$postController->incrementViews($post['id']);

// Retrieve previous and next posts using the new methods
$prevPost = $postController->getPreviousPost($post['id']);
$nextPost = $postController->getNextPost($post['id']);

// Process new comment submission
$commentMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $author  = trim($_POST['author'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $comment = trim($_POST['comment'] ?? '');
    
    if (!empty($author) && !empty($email) && !empty($comment)) {
        $saved = $commentController->saveComment($post['id'], $author, $email, $comment);
        $commentMessage = $saved ? "Your comment was submitted." : "Failed to submit your comment.";
    } else {
        $commentMessage = "Please fill in all fields.";
    }
}

// Retrieve approved comments for display
$comments = $commentController->getApprovedComments($post['id']);

// Set the page title and include the header
$title = htmlspecialchars($post['title']) . " &raquo; fdiengdoh.com";
$description = htmlspecialchars($post['description']);
$keywords = htmlspecialchars($post['keywords']);
include APP_DIR . '/include/header.php';

?>
<main>
            <div class="row m-0">
               <div class="col-md-8">
                  <!-- Article Section Start -->
                  <article class="p-3 ">
                     <h2 class="display-5 link-body-emphasis mb-1"><?= htmlspecialchars($post['title']); ?></h2>
                     <p class="blog-post-meta">
                        <a href="<?= BASE_URL ?>/profile/<?= $post['username'] ?>"><i class="bi bi-person"></i> <?= $post['first_name'] ?> <?= $post['last_name'] ?></a>
                        <a href="<?= BASE_URL ?>/<?= $post['slug'] ?>" title="perma link" rel="bookmark"><i class="bi bi-calendar"></i> <?= date('d F Y', strtotime($post['created_at'])) ?></a>
                     </p>
                     <?php if (!empty($post['feature_image'])): ?>
                      <p><img src="<?= BASE_URL . '/' . htmlspecialchars($post['feature_image']); ?>" class="card-img-top feature-img" alt="Feature Image"></p>
                    <?php endif; ?>
                    <?= $post['content'] ?>
                     <div class="divider"></div>
                  </article>
                  <!-- Article Section Ends -->
                   <!-- Random Blog Start-->
                   <div class="container">
                     <div class="row py-2">
                        <div class="h5 text-uppercase"><i class="bi bi-vector-pen"></i> Author: <a href="<?= BASE_URL ?>/profile/<?= $post['username'] ?>"><?= $post['first_name'] . ' ' . $post['last_name'] ?></a></div>
                        <div class="col-lg-2 text-center">
                           <img src="<?= BASE_URL ?>/<?= $post['profile_picture'] ?>" class="img-fluid rounded-circle" alt="<?= $post['username'] ?>" style="max-width:100px">
                        </div>
                        <div class="col-lg-10">
                           <p class="lead p-2 text-body-secondary"><i><?= $post['bio'] ?></i></p>
                        </div>
                     </div>
                     <div class="divider"></div>
                     <div class="row py-2"> <!-- Row Start -->
                        <div class="h4">RECOMMENDED FOR YOU</div>
                        <div class="col-md"> <!-- Col Start --> 
                           <?php if($prevPost): ?>
                           <div class="card text-white my-2">
                              <img src="<?= BASE_URL ?>/<?= $prevPost['feature_image'] ?>" class="card-img" style="object-fit: cover;" alt="<?= $prevPost['title'] ?>">
                              <div class="card-img-overlay">
                                 <h5 class="card-title"><a href="<?= BASE_URL ?>/<?= $prevPost['slug'] ?>" class="btn btn-primary text-white"><?= $prevPost['title'] ?></a></h5>
                              </div>
                           </div>
                           <?php endif; ?>
                        </div><!-- Col Ends -->

                        <div class="col-md"> <!-- Col Start --> 
                           <?php if($nextPost): ?>
                           <div class="card text-white my-2">
                              <img src="<?= BASE_URL ?>/<?= $nextPost['feature_image'] ?>" class="card-img" style="object-fit: cover;" alt="<?= $nextPost['title'] ?>">
                              <div class="card-img-overlay">
                                 <h5 class="card-title"><a href="<?= BASE_URL ?>/<?= $nextPost['slug'] ?>" class="btn btn-primary text-white"><?= $nextPost['title'] ?></a></h5>
                              </div>
                           </div>
                           <?php endif; ?>
                        </div><!-- Col Ends -->

                     </div> <!-- Row Ends -->
                  </div>
                  <!-- Radom Blog End -->
<!-- Comment Sections Here --> 
 <div class="p-3">
   <!-- Display Approved Comments -->
<div class="mb-5">
  <h3><i class="bi bi-chat-fill"></i> Comments</h3>
  <?php if (!empty($comments)): ?>

    <ul class="list-group">
      <?php foreach ($comments as $comm): ?>
        <li class="list-group-item">
          <strong><?= htmlspecialchars($comm['author']); ?></strong> on <?= htmlspecialchars($comm['created_at']); ?> said:
          <p><?= nl2br(htmlspecialchars($comm['comment'])); ?></p>
          <!-- Report Link: Calls report-comment.php with the comment ID -->
          <a href="<?= BASE_URL ?>/report-comment?id=<?= $comm['id']; ?>" class="small text-danger" onclick="return confirm('Report this comment for review?');">Report</a>
          <?php if(isset($_REQUEST['report-msg'])): ?>
            <div class="alert alert-info" role="alert"><?= htmlspecialchars($_REQUEST['report-msg']) ?></div>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p>No comments yet.</p>
  <?php endif; ?>
</div>  
     <!-- Comment Form -->
  <div class="mb-5">
    <h3>Leave a Comment</h3>
    <?php if ($commentMessage): ?>
      <div class="alert alert-info"><?= htmlspecialchars($commentMessage); ?></div>
    <?php endif; ?>
    <form method="post" action="" novalidate>
      <div class="mb-3">
        <label for="author" class="form-label">Name</label>
        <input type="text" class="form-control" id="author" name="author" placeholder="Your name" required>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" placeholder="Your email" required>
      </div>
      <div class="mb-3">
        <label for="comment" class="form-label">Comment</label>
        <textarea class="form-control" id="comment" name="comment" rows="4" placeholder="Your comment" required></textarea>
      </div>
      <button type="submit" class="btn btn-primary">Submit Comment</button>
    </form>
  </div>
 </div>
 <!-- Comment Sections Ends --> 
               </div>
            <?php require_once(APP_DIR . '/include/sidebar.php'); ?>
            
            </div>
            <!-- Row Ends -->
         </main>
<?php include APP_DIR . '/include/footer.php'; ?>
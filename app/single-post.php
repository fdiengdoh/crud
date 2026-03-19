<?php
// app/single-post.php
// require_once __DIR__ . '/../init.php';

use App\Controllers\PostController;
use App\Controllers\CommentController;
use App\Controllers\CategoryController;

$postController = new PostController();
$commentController = new CommentController();
$categoryController = new CategoryController();

// Use 'slug' (or 'id' if slug not available) from the URL to retrieve the post
$identifier = $_GET['slug'] ?? false;
if (!$identifier) {
    include APP_DIR . '/404.php';
    exit;
}
$post = $postController->show($identifier);

$BScripts .= "<script>
  // This script pings the view increment endpoint after page load.
  window.addEventListener('load', function() {
      // Pass the post ID or slug; adjust as needed.
      let views = document.getElementById(\"views\");
      fetch(\"" . BASE_URL . "/ajax-handler?increment-view=true&slug=" .  $identifier ."\")
          .then(response => response.json())
          .then(data => {
              if(data.success){
                  console.log(\"View incremented.\" + data.views);
                  views.innerHTML = data.views;
              } else {
                  console.log(\"Views not incremented:\", data.views);
                  views.innerHTML = data.views;
              }
          })
          .catch(err => console.error(\"Error incrementing view:\", err));
  });
</script>";

// After successfully fetching the $post:
// Unique view counting logic
// Ensure session is started. If not, start it.
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Check if the current user is an admin or author
$isAdminOrAuthor = $auth->hasRole(\Delight\Auth\Role::ADMIN) || $auth->hasRole(\Delight\Auth\Role::AUTHOR);

if (!$post || ($post['status'] !== 'published' && !$isAdminOrAuthor)) {
    include APP_DIR . '/404.php';
    exit;
}

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
        $commentMessage = $saved ? "Your comment will be publish after moderation." : "Failed to submit your comment.";
    } else {
        $commentMessage = "Please fill in all fields.";
    }
    
    
}

// Retrieve approved comments for display
$comments = $commentController->getApprovedComments($post['id']);
$postCategories = json_decode($post['categories'],true);
// Set the page title and include the header
$title = htmlspecialchars($post['title'] ?? ' ', ENT_QUOTES, 'UTF-8') . " &raquo; fdiengdoh.com";
$description = htmlspecialchars($post['description'] ?? ' ', ENT_QUOTES, 'UTF-8');
$keywords = htmlspecialchars($post['keywords'] ?? ' ', ENT_QUOTES, 'UTF-8');
$TScripts .= $post['a_script'];

// to retrive metadata properly
$isSinglePost = true;  
include APP_DIR . '/include/header.php';

?>
<main>
            <div class="row m-0">
               <div class="col-md-8">
                  <!-- Article Section Start -->
                  <article class="p-3 ">
                     <h2 class="display-5 link-body-emphasis mb-1"><?= htmlspecialchars($post['title'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?></h2>
                     <p class="blog-post-meta">
                        <a href="<?= BASE_URL ?>/profile/<?= $post['username'] ?>" title="Author"><i class="bi bi-person"></i> <?= $post['first_name'] ?> <?= $post['last_name'] ?></a>
                        <a href="<?= BASE_URL ?>/<?= $post['slug'] ?>" title="perma link" rel="bookmark"><i class="bi bi-calendar"></i> <?= date('d M Y', strtotime($post['created_at'])) ?></a>
                        <i class="bi bi-bar-chart-line"></i> <span id="views"></span>
                        <?= $isAdminOrAuthor ?  '<a href="' . LOGIN_URL . '/users/post-edit?id=' . $post['id'] . '"><i class="bi bi-vector-pen"></i> Edit This Post</a>' : ''; ?>
                     </p>
                     <?php if ($post['feature_image'] != null ): ?>
                      <p><img loading="lazy" src="<?= htmlspecialchars($post['feature_image'] ?? ' ', ENT_QUOTES, 'UTF-8') ?>" class="card-img-top feature-img lazyload" alt="<?= $post['title'] ?>"></p>
                    <?php endif; ?>
                    <?= $post['content'] ?>
                     
                     <div class="p-2"><strong class="text-uppercase">Post Label(s): </strong>
                         <?php foreach($postCategories as $category): ?>
                         <a href="<?= BASE_URL . '/search/label/' . $category['slug'] ?>" class="btn btn-dark"><?= $category['name'] ?></a>
                         <?php endforeach; ?>
                     </div>
                     <div class="divider"></div>
                  </article>
                  <!-- Article Section Ends -->
                   <!-- Random Blog Start-->
                   <div class="container">
                     <div class="row py-2">
                        <div class="h5 text-uppercase"><i class="bi bi-vector-pen"></i> Author: <a href="<?= BASE_URL ?>/profile/<?= $post['username'] ?>"><?= $post['first_name'] . ' ' . $post['last_name'] ?></a></div>
                        <div class="col-lg-2 text-center">
                           <img loading="lazy" src="<?= BASE_URL ?>/<?= $post['profile_picture'] ?>" class="img-fluid rounded-circle lazyload" alt="<?= $post['username'] ?>" style="max-width:100px">
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
                                <a href="<?= BASE_URL ?>/<?= $prevPost['slug'] ?>" class="btn btn-link">
                                  <img loading="lazy" src="<?= $prevPost['feature_image'] ?>" class="card-img lazyload" style="object-fit: cover;width:100%; height:100%" alt="<?= $prevPost['title'] ?>">
                                  <div class="card-img-overlay">
                                     <h5 class="card-title bg-dark rounded text-white"><?= $prevPost['title'] ?></h5>
                                  </div>
                                </a>
                           </div>
                           <?php endif; ?>
                        </div><!-- Col Ends -->

                        <div class="col-md"> <!-- Col Start --> 
                           <?php if($nextPost): ?>
                           <div class="card text-white my-2">
                               <a href="<?= BASE_URL ?>/<?= $nextPost['slug'] ?>" class="btn btn-link">
                                  <img loading="lazy" src="<?= $nextPost['feature_image'] ?>" class="card-img lazyload" style="object-fit: cover;width:100%; height:100%" alt="<?= $nextPost['title'] ?>">
                                  <div class="card-img-overlay">
                                     <h5 class="card-title bg-dark rounded text-white"><?= $nextPost['title'] ?></h5>
                                  </div>
                                </a>
                           </div>
                           <?php endif; ?>
                        </div><!-- Col Ends -->

                     </div> <!-- Row Ends -->
                  </div>
                  <!-- Radom Blog End -->
                  <!-- Only show if allow_comments is true -->
                  <?php if ($post['allow_comments']): ?>
                  <!-- Comment Section with Tabs -->
                  <div class="container my-4">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" id="commentTabs" role="tablist">
                      <li class="nav-item" role="presentation">
                        <button class="nav-link active text-dark" id="native-tab" data-bs-toggle="tab" data-bs-target="#native" type="button" role="tab" aria-controls="native" aria-selected="true">
                          Comments
                        </button>
                      </li>
                      <li class="nav-item" role="presentation">
                        <button class="nav-link text-dark" id="fb-tab" data-bs-toggle="tab" data-bs-target="#fb" type="button" role="tab" aria-controls="fb" aria-selected="false">
                          Facebook Comments
                        </button>
                      </li>
                      <li class="nav-item" role="presentation">
                        <button class="nav-link text-dark" id="disqus-tab" data-bs-toggle="tab" data-bs-target="#disqus" type="button" role="tab" aria-controls="disqus" aria-selected="false">
                          Disqus Comments
                        </button>
                      </li>
                    </ul>
                    
                    <!-- Tab panes -->
                    <div class="tab-content p-3 border border-top-0 rounded-bottom" id="commentTabsContent">
                      <!-- Native Comment System -->
                      <div class="tab-pane fade show active" id="native" role="tabpanel" aria-labelledby="native-tab">
                        <!-- Existing Native Comments Display -->
                        <div class="mb-1">
                          <h3><i class="bi bi-chat-fill"></i> Comments</h3>
                          <?php if (!empty($comments)): ?>
                            <ul class="list-group">
                              <?php foreach ($comments as $comm): ?>
                                <li class="list-group-item">
                                  <strong><?= htmlspecialchars($comm['author'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?></strong>
                                  on <?= htmlspecialchars($comm['created_at'] ?? ' ', ENT_QUOTES, 'UTF-8'); ?> said:
                                  <p><?= nl2br(htmlspecialchars($comm['comment'] ?? ' ', ENT_QUOTES, 'UTF-8')); ?></p>
                                  <!-- Report Form for Comment -->
                                  <form method="post" action="<?= BASE_URL ?>/report-comment" onsubmit="return confirm('Report this comment for review?');" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($comm['id']); ?>">
                                    <input type="hidden" name="slug" value="<?= htmlspecialchars($post['slug']); ?>">
                                    <button type="submit" class="btn btn-link p-0 m-0 align-baseline small text-danger">Report</button>
                                  </form>
                                </li>
                              <?php endforeach; ?>
                            </ul>
                          <?php else: ?>
                            <p>No comments yet.</p>
                          <?php endif; ?>
                        </div>
                        <!-- Native Comment Form -->
                        <div class="mb-1">
                          <h3>Leave a Comment</h3>
                          <?php if ($commentMessage): ?>
                            <div class="alert alert-info"><?= htmlspecialchars($commentMessage ?? ' ', ENT_QUOTES, 'UTF-8'); ?></div>
                          <?php endif; ?>
                          <form method="post" action="" novalidate>
                            <div class="mb-3">
                              <label for="author" class="form-label">Name</label>
                              <input type="text" class="form-control" id="author" name="author" placeholder="Your name" required>
                              <input type="hidden" name="refresh" value="1">
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
                      
                      <!-- Facebook Comments Tab -->
                      <div class="tab-pane fade" id="fb" role="tabpanel" aria-labelledby="fb-tab">
                        <!-- Facebook Comments Plugin -->
                        <div id="fb-root"></div>
                        <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v22.0"></script>
                        <div class="fb-comments" data-href="<?= BASE_URL . '/' . htmlspecialchars($post['slug']) ?>" data-width="100%" data-numposts="5"></div>
                      </div>
                      
                      <!-- Disqus Comments Tab -->
                      <div class="tab-pane fade" id="disqus" role="tabpanel" aria-labelledby="disqus-tab">
                        <!-- Disqus Comments Embed -->
                        <div id="disqus_thread"></div>
                        <script>
                          var disqus_config = function () {
                            this.page.url = "<?= BASE_URL . '/' . htmlspecialchars($post['slug']) ?>";
                            this.page.identifier = "post_<?= $post['id'] ?>";
                          };
                          (function() { // DON'T EDIT BELOW THIS LINE
                            var d = document, s = d.createElement('script');
                            s.src = 'https://fdiengdoh.disqus.com/embed.js';
                            s.setAttribute('data-timestamp', +new Date());
                            (d.head || d.body).appendChild(s);
                          })();
                        </script>
                        <noscript>Please enable JavaScript to view the <a href="https://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
                      </div>
                    </div>
                  </div>

                  <!-- Comment Sections Ends --> 
                    <?php endif; ?>
               </div>
            <?php require_once(APP_DIR . '/include/sidebar.php'); ?>
            
            </div>
            <!-- Row Ends -->
         </main>
<?php include APP_DIR . '/include/footer.php'; ?>
<?php
// public/404.php
http_response_code(404);
require_once __DIR__ . '/../init.php';

// Set the page title for header
$title = "404 Error &raquo; fdiengdoh.com";

include APP_DIR . '/include/header.php';
?>
<main>
<div class="row">
  <!-- Main Area Content -->
  <div class="col-md-8">  
      <div class="custom-bg text-dark">
        <div class="d-flex align-items-center justify-content-center min-vh-100 px-2">
            <div class="text-center">
                <h1 class="display-1 fw-bold">404</h1>
                <p class="fs-2 fw-medium mt-4">Oops! Page not found</p>
                <p class="mt-4 mb-5">The page you're looking for doesn't exist or has been moved.</p>
                <a href="/" class="btn btn-primary fw-semibold rounded-pill px-4 py-2">
                    Go Home
                </a>
            </div>
        </div>
    </div>
  </div>
  <?php require_once(APP_DIR . '/include/sidebar.php'); ?>
   </div>
<!-- Row Ends -->
</main>


<?php include APP_DIR . '/include/footer.php'; ?>
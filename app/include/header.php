<?php
// To get metada for social network
if (isset($isSinglePost) && $isSinglePost && isset($post)) {
    // For single post pages
    $ogTitle = htmlspecialchars($post['title'] ?? ' ', ENT_QUOTES, 'UTF-8');    // Use a truncated version of the post content for description
    $ogDescription = htmlspecialchars(substr(strip_tags($post['content']), 0, 150));
    if(!empty($post['feature_image']) ) {
        $ogImage = $post['feature_image'];
    }elseif(isset($post['og_image'])){
        $ogImage = $post['og_image'];
    }else{
        $ogImage = BASE_URL . '/assets/image/default-feature.webp';
    } 
} elseif (isset($isCategoryPage) && $isCategoryPage && isset($posts) && count($posts) > 0) {
    // For category pages, use the first post's feature image if available.
    $firstPost = $posts[0];
    $ogTitle = htmlspecialchars($category['name'] ?? ' ', ENT_QUOTES, 'UTF-8') . " Posts";
    $ogDescription = "Explore posts in " . htmlspecialchars($category['name'] ?? ' ', ENT_QUOTES, 'UTF-8');    $ogImage = htmlspecialchars($firstPost['feature_image'] ?? ' ', ENT_QUOTES, 'UTF-8');} else {
    // Fallback defaults
    $ogTitle = "F. Diengdoh.com";
    $ogDescription = "Farlando Diengdoh Blogging randomly about Chemistry, Web Apps, Technology, Culture etc.";
    $ogImage = BASE_URL . '/assets/image/default-feature.webp';
}
?>
<!doctype html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="description" content="<?= isset($description) ? $description: 'Farlando Diengdoh Blogging randomly about Chemistry, Web Apps, Technology, Culture etc.' ?>">
      <meta name="keywords" content="<?= isset($keywords) ? $keywords: 'Chemistry, Web Apps, Technology, Culture, etc.' ?>">
      <meta name="author" content="Farlando Diengdoh">
      <meta property="og:title" content="<?= $ogTitle ?>">
      <meta property="og:description" content="<?= $ogDescription ?>">
      <meta property="og:image" content="<?= $ogImage ?>">
      <meta property="og:url" content="<?= BASE_URL . $_SERVER['REQUEST_URI'] ?>">
      <meta name="twitter:card" content="summary_large_image">
      <meta name="twitter:title" content="<?= $ogTitle ?>">
      <meta name="twitter:description" content="<?= $ogDescription ?>">
      <meta name="twitter:image" content="<?= $ogImage ?>">

      <title><?= $title ?></title>
      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
      <link rel="preconnect" href="https://cdn.jsdelivr.net">
      <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
      <link rel="preload" href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..800&family=Imperial+Script&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
      <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" as="style" onload="this.onload=null;this.rel='stylesheet'">
      <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
      <!-- Custom styles for this template -->
      <link rel="preload" href="/css/style.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
      <?= $TScripts ?>
   </head>
   <body class="p-0 bg-secondary">
       <!-- Wrapper Class Start -->
      <div class="container wrapper bg-white p-0">
         <!-- Top Bar -->
         <div class="top-bar">
            <div class="px-3">
               <div class="col-12 py-2 text-center text-md-end social-top sm-h1">
                  <a href="https://whatsapp.com/channel/0029Va9VuhPI1rcehvL1h91F"><i class="bi bi-whatsapp"></i></a>
                  <a href="https://facebook.com/fdphy"><i class="bi bi-facebook"></i></a>
                  <a href="https://www.twitter.com/fdphy_in"><i class="bi bi-twitter-x"></i></a>
                  <a href="https://www.instagram.com/fdphy"><i class="bi bi-instagram"></i></a>
                  <a href="https://www.youtube.com/fdphy"><i class="bi bi-youtube"></i></a>
               </div>
            </div>
         </div>
         <!-- End Top Bar -->
         <div class="p-3">
            <a href="/" class="logo">
               <svg height="40">
                  <use href="#logo"></use>
               </svg>
            </a>
         </div>
         <header>
			<?php require_once APP_DIR . '/include/nav.php' ?>
		</header>

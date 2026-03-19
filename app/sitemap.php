<?php
// app/sitemap.php


// Get published posts and categories (customize as needed)
$posts = $postController->getPosts('all',1,100); // You may want to modify to only return published posts
$categories = $categoryController->getAllCategories();

// Set header for XML content.
header("Content-Type: application/xml; charset=utf-8");

// Start XML output.
echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <!-- Home Page -->
  <url>
    <loc><?= BASE_URL ?>/</loc>
    <lastmod><?= date('Y-m-d') ?></lastmod>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
  </url>
  
  <!-- Posts -->
  <?php foreach ($posts as $post): 
    // Use updated_at if available, else created_at.
    $lastMod = isset($post['updated_at']) && !empty($post['updated_at']) 
      ? $post['updated_at'] 
      : $post['created_at'];
    ?>
    <url>
      <loc><?= BASE_URL . '/' . htmlspecialchars($post['slug']) ?></loc>
      <lastmod><?= date('Y-m-d', strtotime($lastMod)) ?></lastmod>
      <changefreq>weekly</changefreq>
      <priority>0.8</priority>
    </url>
  <?php endforeach; ?>

  <!-- Categories (using page 1 URL for each category) -->
  <?php foreach ($categories as $category): ?>
    <url>
      <loc><?= BASE_URL ?>/search/label/<?= htmlspecialchars($category['slug']) ?>/1</loc>
      <lastmod><?= date('Y-m-d') ?></lastmod>
      <changefreq>weekly</changefreq>
      <priority>0.6</priority>
    </url>
  <?php endforeach; ?>
</urlset>

<?php
// app/header-auth.php
// This header is meant for authenticated pages

if (!isset($auth) || !$auth->isLoggedIn()) {
    header("Location: " . $link->getUrl("/users/login"));
    exit;
}
isset($_TSCRIPTS) ? $TScripts = $_TSCRIPTS : $TScripts = '';
isset($_BSCRIPTS) ? $BScripts = $_BSCRIPTS : $BScripts = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle ?? ' ', ENT_QUOTES, 'UTF-8') : 'Dashboard'; ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap 5 CSS (Assuming your assets are located in public/assets) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <link rel="preload" href="/css/style.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <?= $TScripts ?>
</head>
<body>

  <!-- Navigation Bar for Authenticated Users -->
  <nav class="navbar navbar-expand-lg navbar-dark p-0 shadow-sm">
    <div class="container">
      <a class="navbar-brand" href="<?= $link->getUrl('/users') ?>"><i class="bi bi-stickies"></i> Dashboard</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#authNavbar" aria-controls="authNavbar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="authNavbar">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link text-white" href="<?= $link->getUrl('/users/post-create') ?>"><i class="bi bi-pencil-square"></i> Create Post</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="<?= $link->getUrl('/users/edit-profile') ?>"><i class="bi bi-person-fill"></i> Edit Profile</a></li>
          <?php if ($auth->hasRole(\Delight\Auth\Role::ADMIN)): ?>
            <li class="nav-item"><a class="nav-link text-white" href="<?= $link->getUrl('/admin') ?>"><i class="bi bi-hdd-stack"></i> Admin Panel</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= $link->getUrl('/admin/categories') ?>"><i class="bi bi-tags"></i> Manage Categories</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= $link->getUrl('/admin/view-logs') ?>"><i class="bi bi-eye"></i> view Logs</a></li>
          <?php endif; ?>
          <li class="nav-item"><a class="nav-link text-white" href="<?= $link->getUrl('/') ?>" target="_blank"><i class="bi bi-browser-safari"></i> View Blog</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="<?= $link->getUrl('/logout') ?>"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

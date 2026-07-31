<?php
/**
 * Shared <head> + top bar + navbar.
 * Expects (optional): $pageTitle, $activePage ('home'|'products'|'detail'), $categories
 */
$pageTitle  = $pageTitle ?? 'Vdesiconnect | Rakhi Festival Celebrations';
$activePage = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?></title>
<meta name="description" content="Celebrate Raksha Bandhan with a handpicked collection of Rakhis and gift sets, delivered across India & USA.">

<!-- Bootstrap 5 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<!-- Swiper -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<!-- AOS (scroll animations) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
<!-- Google Font -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<!-- Site CSS -->
<link rel="stylesheet" href="css/style.css">
<link rel="icon" href="img/fav.png" type="image/png">
</head>
<body class="page-loading">

<!-- Full-page loader -->
<div id="pageLoader" class="page-loader" role="status" aria-label="Loading">
  <div class="page-loader-inner">
    <img src="img/logo.png" alt="" class="page-loader-logo">
    <span class="page-loader-ring" aria-hidden="true"></span>
    <p class="page-loader-text">Loading festive picks&hellip;</p>
  </div>
</div>

<!-- Top ribbon -->
<div class="top-bar d-none d-md-block">
  <div class="wrap d-flex justify-content-center align-items-center">
    Elite Mart Collaborates with Vdesiconnect to bring you a curated collection of Rakhis and gift sets, delivered across India & USA.
   
  </div>
</div>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg sticky-top site-navbar">
  <div class="wrap d-flex align-items-center w-100">
    <a class="navbar-brand" href="index.php">
      <img src="img/logo.png" alt="Vdesiconnect logo" height="48">
    </a>
    <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="offcanvas" data-bs-target="#mainNav" aria-controls="mainNav" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="offcanvas offcanvas-end site-nav-offcanvas" tabindex="-1" id="mainNav" aria-labelledby="mainNavLabel">
      <div class="offcanvas-header">
        <a class="navbar-brand m-0" href="index.php" id="mainNavLabel">
          <img src="img/logo.png" alt="Vdesiconnect logo" height="40">
        </a>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <ul class="navbar-nav ms-lg-auto align-items-lg-center">
          <li class="nav-item"><a class="nav-link <?= $activePage === 'home' ? 'active' : '' ?>" href="index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link <?= $activePage === 'products' ? 'active' : '' ?>" href="product-list.php">All Rakhis</a></li>
          <li class="nav-item"><a class="nav-link <?= $activePage === 'order-status' ? 'active' : '' ?>" href="order-status.php">Order Status</a></li>
          <li class="nav-item"><a class="nav-link <?= $activePage === 'contact' ? 'active' : '' ?>" href="contact.php">Contact</a></li>
          
        </ul>
        <div class="site-nav-offcanvas-footer d-lg-none">
          <p class="mb-1"><i class="bi bi-truck"></i>&nbsp; Free Shipping to India &amp; USA</p>
          <p class="mb-0"><i class="bi bi-envelope"></i>&nbsp; support@vdesiconnect.com</p>
        </div>
      </div>
    </div>
  </div>
</nav>
<script>
  (function () {
    var loader = document.getElementById('pageLoader');
    if (!loader) return;
    var hide = function () {
      document.body.classList.remove('page-loading');
      loader.classList.add('page-loader-hidden');
      loader.addEventListener('transitionend', function () { loader.remove(); }, { once: true });
    };
    window.addEventListener('load', hide);
    // Safety net in case the load event is delayed by slow third-party assets.
    setTimeout(hide, 4000);
  })();
</script>

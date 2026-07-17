<?php
require __DIR__ . '/data.php';
$pageTitle  = 'Shop All Rakhis | Vdesiconnect';
$activePage = 'products';
require __DIR__ . '/components/header.php';
?>

<div class="breadcrumb-bar">
  <div class="wrap">
    <a href="index.php" class="text-decoration-none">Home</a> / <span>Rakhi Collection</span>
  </div>
</div>

<section class="py-5">
  <div class="wrap">
    <div class="section-title" data-aos="fade-up">
      <span>Full Catalog</span>
      <h2>Rakhi Collection</h2>
      <div class="underline"></div>
    </div>

    <div class="search-box" data-aos="fade-up">
      <input type="search" id="productSearch" class="form-control" placeholder="Search Rakhis...">
    </div>

    <div class="filter-bar" data-aos="fade-up">
      <button class="filter-btn" data-category="all">All</button>
      <?php foreach ($categories as $key => $label): ?>
        <button class="filter-btn" data-category="<?= $key ?>"><?= $label ?></button>
      <?php endforeach; ?>
    </div>

    <div class="row g-4" id="productGrid">
      <div class="col-12 text-center text-muted py-5">Loading Rakhis...</div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/components/footer.php'; ?>

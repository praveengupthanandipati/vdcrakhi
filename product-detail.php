<?php
require __DIR__ . '/data.php';

$id      = (int) ($_GET['id'] ?? 0);
$product = getProductById($products, $id);

if (!$product) {
    header('Location: product-list.php');
    exit;
}

$related = array_values(array_filter($products, fn($p) => $p['category'] === $product['category'] && $p['id'] !== $product['id']));

$pageTitle  = $product['name'] . ' | Vdesiconnect';
$activePage = 'detail';
require __DIR__ . '/components/header.php';
?>

<div class="breadcrumb-bar">
  <div class="wrap">
    <a href="index.php" class="text-decoration-none">Home</a> /
    <a href="product-list.php" class="text-decoration-none">Rakhis</a> /
    <a href="product-list.php?category=<?= $product['category'] ?>" class="text-decoration-none"><?= $product['category_label'] ?></a> /
    <span><?= $product['name'] ?></span>
  </div>
</div>

<section class="py-5">
  <div class="wrap">
    <div class="row g-5 align-items-center">
      <div class="col-md-6 detail-image" data-aos="fade-right">
        <img src="<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>">
      </div>
      <div class="col-md-6" data-aos="fade-left">
        <span class="badge-soft"><?= $product['category_label'] ?></span>
        <?php if ($product['badge']): ?><span class="badge-soft ms-2"><?= $product['badge'] ?></span><?php endif; ?>
        <h1 class="font-display mt-3"><?= $product['name'] ?></h1>
        <div class="detail-price mb-3">
          <span class="price-old"><?= formatPrice($product['price']) ?></span>
          <span class="price-offer"><?= formatPrice($product['offer_price']) ?></span>
        </div>
        <p class="mb-4"><?= $product['description'] ?></p>
        <a class="btn btn-maroon btn-lg" href="checkout.php?id=<?= $product['id'] ?>">
          <i class="bi bi-bag-check"></i> Buy Now — <?= formatPrice($product['offer_price']) ?>
        </a>
        <p class="small text-muted mt-3"><i class="bi bi-truck"></i> Ships to India &amp; USA &nbsp;|&nbsp; <i class="bi bi-arrow-repeat"></i> Easy returns</p>
      </div>
    </div>
  </div>
</section>

<?php if ($related): ?>
<section class="py-5 bg-light">
  <div class="wrap">
    <div class="section-title" data-aos="fade-up">
      <span>You May Also Like</span>
      <h2>More from <?= $product['category_label'] ?></h2>
      <div class="underline"></div>
    </div>
    <div class="swiper related-swiper" data-aos="fade-up">
      <div class="swiper-wrapper">
        <?php foreach ($related as $r): ?>
        <div class="swiper-slide">
          <div class="product-card">
            <a href="product-detail.php?id=<?= $r['id'] ?>" class="thumb">
              <img src="<?= $r['image'] ?>" alt="<?= htmlspecialchars($r['name']) ?>" loading="lazy">
            </a>
            <div class="body">
              <span class="cat"><?= $r['category_label'] ?></span>
              <h3><a href="product-detail.php?id=<?= $r['id'] ?>" class="text-decoration-none text-dark"><?= $r['name'] ?></a></h3>
              <div class="price-row">
                <span class="price-old"><?= formatPrice($r['price']) ?></span>
                <span class="price-offer"><?= formatPrice($r['offer_price']) ?></span>
              </div>
              <a class="btn btn-maroon buy-btn" href="checkout.php?id=<?= $r['id'] ?>">Buy Now</a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="swiper-pagination"></div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/components/footer.php'; ?>

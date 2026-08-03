<?php
require __DIR__ . '/data.php';
$pageTitle  = 'Vdesiconnect | Rakhi Festival — Celebrate the Bond of Love';
$activePage = 'home';
require __DIR__ . '/components/header.php';

$featured = array_slice($products, 0, 30); // 5 rows x 6 per row (lg breakpoint)
$gifts    = array_values(array_filter($products, fn($p) => in_array($p['category'], ['rakhi-chocolate-india', 'rakhi-sweets-india', 'rakhi-nuts-india', 'rakhi-sweets-usa'])));

$heroSlides = [
    [
        'img'   => $productImages[0 % count($productImages)],
        'title' => 'Celebrate the Bond of Love',
        'sub'   => 'Rakhi Special Edition — Exclusively for India &amp; USA, delivered with love.',
    ],
    [
        'img'   => $productImages[5 % count($productImages)],
        'title' => 'Brother &amp; Sister, Forever',
        'sub'   => 'Handpicked Rakhis that carry your wishes across every mile.',
    ],
    [
        'img'   => $productImages[10 % count($productImages)],
        'title' => 'Rakhi Gifts &amp; Sweet Combos',
        'sub'   => 'Pair every Rakhi with a gift box your sibling will love.',
    ],
    [
        'img'   => $productImages[15 % count($productImages)],
        'title' => 'Handcrafted with Tradition',
        'sub'   => 'From classic silk threads to modern designer Rakhis.',
    ],
];
?>

<!-- HERO -->
<section class="hero-swiper swiper">
  <div class="swiper-wrapper">
    <?php foreach ($heroSlides as $slide): ?>
    <div class="swiper-slide hero-slide" style="background-image:url('<?= $slide['img'] ?>')">
      <div class="wrap">
        <div class="hero-caption">
          <h1><?= $slide['title'] ?></h1>
          <p><?= $slide['sub'] ?></p>
          <a href="product-list.php" class="btn btn-gold btn-lg mt-2">Shop Now</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="swiper-pagination"></div>
  <div class="swiper-button-prev"></div>
  <div class="swiper-button-next"></div>
</section>

<!-- FEATURED RAKHIS -->
<section class="py-5">
  <div class="wrap">
    <div class="section-title" data-aos="fade-up">
      <span>Handpicked for You</span>
      <h2>✨ Featured Rakhis</h2>
      <div class="underline"></div>
    </div>
    <div class="row g-4">
      <?php foreach ($featured as $p): ?>
      <div class="col-6 col-md-4 col-lg-2" data-aos="fade-up">
        <div class="product-card">
          <a href="product-detail.php?id=<?= $p['id'] ?>" class="thumb">
            <?php if ($p['badge']): ?><span class="badge-tag"><?= $p['badge'] ?></span><?php endif; ?>
            <img src="<?= $p['image'] ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
          </a>
          <div class="body">
            <span class="cat"><?= $p['category_label'] ?></span>
            <h3><a href="product-detail.php?id=<?= $p['id'] ?>" class="text-decoration-none text-dark"><?= $p['name'] ?></a></h3>
            <div class="price-row">
              <span class="price-old"><?= formatPrice($p['price']) ?></span>
              <span class="price-offer"><?= formatPrice($p['offer_price']) ?></span>
            </div>
            <a class="btn btn-maroon buy-btn" href="checkout.php?id=<?= $p['id'] ?>">Buy Now</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-4" data-aos="fade-up">
      <a href="product-list.php" class="btn btn-maroon">Show More</a>
    </div>
  </div>
</section>

<!-- CATEGORIES -->
<section class="py-5 bg-light">
  <div class="wrap">
    <div class="section-title" data-aos="fade-up">
      <span>Explore</span>
      <h2>Shop by Category</h2>
      <div class="underline"></div>
    </div>
    <div class="row g-4">
      <?php
      $icons = [
          'single-rakhi-india'    => 'bi-flower1',
          'multiple-rakhis-india' => 'bi-collection-fill',
          'rakhi-chocolate-india' => 'bi-gift-fill',
          'rakhi-sweets-india'    => 'bi-cup-hot-fill',
          'rakhi-nuts-india'      => 'bi-basket3-fill',
          'resin-rakhi-india'     => 'bi-gem',
          'single-rakhi-usa'      => 'bi-flower2',
          'multiple-rakhi-usa'    => 'bi-stack',
          'rakhi-chocolate-usa'   => 'bi-gift',
          'rakhi-sweets-usa'      => 'bi-cup-straw',
          'rakhi-nuts-usa'        => 'bi-basket3',
          'resin-rakhi-usa'       => 'bi-gem',
      ];
      foreach ($categories as $key => $label): ?>
      <div class="col-6 col-md-4 col-lg-2" data-aos="zoom-in">
        <a href="product-list.php?category=<?= $key ?>" class="text-decoration-none">
          <div class="category-card">
            <div class="icon-circle"><i class="bi <?= $icons[$key] ?? 'bi-gift-fill' ?>"></i></div>
            <h4><?= $label ?></h4>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- RAKHI GIFTS -->
<section class="py-5">
  <div class="wrap">
    <div class="section-title" data-aos="fade-up">
      <span>Complete the Celebration</span>
      <h2>Rakhi Gifts</h2>
      <div class="underline"></div>
    </div>
    <div class="swiper gift-swiper" data-aos="fade-up">
      <div class="swiper-wrapper">
        <?php foreach ($gifts as $g): ?>
        <div class="swiper-slide gift-card">
          <a href="product-detail.php?id=<?= $g['id'] ?>">
            <img src="<?= $g['image'] ?>" alt="<?= htmlspecialchars($g['name']) ?>" loading="lazy">
            <h5><?= $g['name'] ?></h5>
            <span class="price-offer"><?= formatPrice($g['offer_price']) ?></span>
          </a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- ABOUT -->
<section id="about" class="about-section">
  <div class="wrap">
    <div class="row g-5 align-items-center">
      <div class="col-lg-5">
        <div class="about-image-wrap">
          <img src="<?= $productImages[8 % count($productImages)] ?>" alt="Handcrafted Rakhi">
          <span class="about-badge"><i class="bi bi-heart-fill"></i></span>
        </div>
      </div>
      <div class="col-lg-7">
        <span class="section-eyebrow">Our Story</span>
        <h2 class="about-heading">Celebrate Raksha Bandhan with Style</h2>
        <p>Raksha Bandhan is more than a celebration of sibling love — it's the perfect occasion to buy rakhi online and have it delivered with care, wherever your loved ones are. Whether you're shopping for tradition or something contemporary, we bring the celebration to your doorstep.</p>
        <div class="feature-list">
          <div class="feature-item">
            <span class="feature-icon"><i class="bi bi-brush"></i></span>
            <div>
              <h6>Handpicked Designs</h6>
              <p>Single &amp; multiple Rakhi packs, handpicked for you</p>
            </div>
          </div>
          <div class="feature-item">
            <span class="feature-icon"><i class="bi bi-gift"></i></span>
            <div>
              <h6>Combo Gift Packs</h6>
              <p>Rakhis paired with chocolates, sweets &amp; nuts</p>
            </div>
          </div>
          <div class="feature-item">
            <span class="feature-icon"><i class="bi bi-truck"></i></span>
            <div>
              <h6>Fast, Reliable Shipping</h6>
              <p>Delivered with love across India &amp; USA</p>
            </div>
          </div>
        </div>
        <a href="product-list.php" class="btn btn-maroon btn-lg mt-2">Shop Rakhi Collection</a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/components/footer.php'; ?>

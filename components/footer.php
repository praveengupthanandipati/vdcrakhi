<?php $extraScripts = $extraScripts ?? ''; ?>
<footer id="contact" class="site-footer">
  <div class="wrap py-5">
    <div class="row gy-4">
      <div class="col-6 col-lg-3">
        <span class="footer-logo-badge mb-3 d-inline-block">
          <img src="img/logo.png" alt="Vdesiconnect logo" height="42">
        </span>
        <p class="small">Handpicked Rakhis &amp; gift sets, shipped with love to India &amp; USA.</p>
      </div>
      <div class="col-6 col-lg-3">
        <h6>Quick Links</h6>
        <ul class="list-unstyled footer-links">
          <li><a href="faq.php">FAQ</a></li>
          <li><a href="contact.php">Contact Us</a></li>
        </ul>
      </div>
      <div class="col-6 col-lg-3">
        <h6>Shop</h6>
        <ul class="list-unstyled footer-links">
          <li><a href="product-list.php">All Rakhis</a></li>
        </ul>
        <p class="small mt-3 mb-0"><i class="bi bi-geo-alt"></i> Shipping to India &amp; USA</p>
      </div>
      <div class="col-6 col-lg-3">
        <h6>Contact</h6>
        <p class="small mb-0"><i class="bi bi-envelope"></i> support@vdesiconnect.com</p>
      </div>
    </div>
    <hr>
    <p class="small text-center mb-0">&copy; <?= date('Y') ?> Vdesiconnect. All rights reserved.</p>
  </div>
</footer>

<!-- Bootstrap Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Swiper -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<!-- AOS -->
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<!-- Site JS -->
<script src="js/main.js"></script>
<?= $extraScripts ?>
</body>
</html>

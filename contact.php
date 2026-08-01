<?php
$pageTitle  = 'Contact Us | Vdesiconnect';
$activePage = 'contact';
require __DIR__ . '/components/header.php';
?>

<div class="breadcrumb-bar">
  <div class="wrap">
    <a href="index.php" class="text-decoration-none">Home</a> / <span>Contact Us</span>
  </div>
</div>

<section class="py-5">
  <div class="wrap">
    <div class="section-title">
      <span>Get in Touch</span>
      <h2>We'd Love to Hear From You</h2>
      <div class="underline"></div>
    </div>

    <div class="row justify-content-center">
      <div class="col-lg-7">
        <div class="contact-info-card">
          <div class="contact-info-item">
            <span class="feature-icon"><i class="bi bi-telephone-fill"></i></span>
            <div>
              <h6>Phone</h6>
              <p>+91 91003 11263 (India)</p>
              <p>+1 623 265 9209 (USA)</p>
            </div>
          </div>
          <div class="contact-info-item">
            <span class="feature-icon"><i class="bi bi-envelope-fill"></i></span>
            <div>
              <h6>Email</h6>
              <p>help.vdesiconnect@gmail.com</p>
            </div>
          </div>
          <div class="contact-info-item">
            <span class="feature-icon"><i class="bi bi-geo-alt-fill"></i></span>
            <div>
              <h6>Office Address</h6>
              <p> Mallampet, Hyderabad, Telangana, India</p>
            </div>
          </div>         
        </div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/components/footer.php'; ?>

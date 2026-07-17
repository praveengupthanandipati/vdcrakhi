<?php
// TODO: replace with the live business inbox before going to production
const CONTACT_EMAIL_TO = 'praveennandipati@gmail.com';

$errors  = [];
$success = false;
$name = $phone = $email = $message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['full_name'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || strlen($name) < 2) {
        $errors['full_name'] = 'Please enter your full name.';
    }
    if ($phone === '' || !preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) {
        $errors['phone'] = 'Please enter a valid phone number.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if ($message === '' || strlen($message) < 10) {
        $errors['message'] = 'Please describe your message (at least 10 characters).';
    }

    if (!$errors) {
        $cleanEmail = str_replace(["\r", "\n"], '', $email);
        $subject = 'New Contact Form Message - Vdesiconnect';
        $body    = "You have a new message from the Vdesiconnect contact form.\n\n"
                 . "Name: {$name}\nPhone: {$phone}\nEmail: {$email}\n\nMessage:\n{$message}\n";
        $headers = "From: Vdesiconnect Website <no-reply@vdesiconnect.com>\r\n"
                 . "Reply-To: {$cleanEmail}\r\n";

        if (@mail(CONTACT_EMAIL_TO, $subject, $body, $headers)) {
            $success = true;
            $name = $phone = $email = $message = '';
        } else {
            $errors['general'] = 'Sorry, your message could not be sent right now. Please try again later or email us directly.';
        }
    }
}

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

    <div class="row g-5">
      <div class="col-lg-4">
        <div class="contact-info-card">
          <div class="contact-info-item">
            <span class="feature-icon"><i class="bi bi-telephone-fill"></i></span>
            <div>
              <h6>Phone</h6>
              <p>+91 98765 43210 (India)<br>+1 (415) 555-0198 (USA)</p>
            </div>
          </div>
          <div class="contact-info-item">
            <span class="feature-icon"><i class="bi bi-envelope-fill"></i></span>
            <div>
              <h6>Email</h6>
              <p>support@vdesiconnect.com</p>
            </div>
          </div>
          <div class="contact-info-item">
            <span class="feature-icon"><i class="bi bi-geo-alt-fill"></i></span>
            <div>
              <h6>Office Address</h6>
              <p>221 Festival Lane, Andheri West,<br>Mumbai, Maharashtra 400058, India</p>
            </div>
          </div>
          <div class="contact-info-item">
            <span class="feature-icon"><i class="bi bi-clock-fill"></i></span>
            <div>
              <h6>Working Hours</h6>
              <p>Mon - Sat: 9:00 AM - 7:00 PM IST</p>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-8">
        <div class="contact-form-card">
          <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
              <i class="bi bi-check-circle-fill"></i> Thank you! Your message has been sent — we'll get back to you soon.
            </div>
          <?php endif; ?>
          <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($errors['general']) ?></div>
          <?php endif; ?>

          <form action="contact.php" method="post" novalidate>
            <div class="row g-3">
              <div class="col-md-6">
                <label for="full_name" class="form-label">Full Name</label>
                <input type="text" class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>" id="full_name" name="full_name" value="<?= htmlspecialchars($name) ?>" placeholder="Your full name" required minlength="2">
                <?php if (isset($errors['full_name'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['full_name']) ?></div><?php endif; ?>
              </div>
              <div class="col-md-6">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="tel" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" id="phone" name="phone" value="<?= htmlspecialchars($phone) ?>" placeholder="+91 98765 43210" required pattern="^[0-9+\-\s()]{7,20}$">
                <?php if (isset($errors['phone'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['phone']) ?></div><?php endif; ?>
              </div>
              <div class="col-md-6">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="you@example.com" required>
                <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div><?php endif; ?>
              </div>
              <div class="col-12">
                <label for="message" class="form-label">Describe Your Message</label>
                <textarea class="form-control <?= isset($errors['message']) ? 'is-invalid' : '' ?>" id="message" name="message" rows="5" placeholder="How can we help you?" required minlength="10"><?= htmlspecialchars($message) ?></textarea>
                <?php if (isset($errors['message'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['message']) ?></div><?php endif; ?>
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-maroon btn-lg">Send Message</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/components/footer.php'; ?>

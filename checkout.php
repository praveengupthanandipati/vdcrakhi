<?php
require __DIR__ . '/data.php';

$id      = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$product = getProductById($products, $id);

if (!$product) {
    header('Location: product-list.php');
    exit;
}

$qty = max(1, min(10, (int) ($_POST['qty'] ?? $_GET['qty'] ?? 1)));

$errors = [];
$f = [
    'first_name' => '', 'last_name' => '', 'company' => '', 'country' => 'India',
    'street_address' => '', 'apartment' => '', 'city' => '', 'state' => '', 'pin_code' => '',
    'phone' => '', 'email' => '', 'ship_different' => false,
    'ship_address' => '', 'ship_city' => '', 'ship_state' => '', 'ship_pin' => '',
    'order_notes' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($f as $key => $default) {
        if ($key === 'ship_different') {
            $f[$key] = isset($_POST[$key]);
        } else {
            $f[$key] = trim($_POST[$key] ?? '');
        }
    }

    if ($f['first_name'] === '') $errors['first_name'] = 'First name is required.';
    if ($f['last_name'] === '') $errors['last_name'] = 'Last name is required.';
    if (!in_array($f['country'], ['India', 'USA'], true)) $errors['country'] = 'Please select a country.';
    if ($f['street_address'] === '') $errors['street_address'] = 'Street address is required.';
    if ($f['city'] === '') $errors['city'] = 'Town / City is required.';
    if ($f['state'] === '') $errors['state'] = 'State is required.';
    if (!preg_match('/^[A-Za-z0-9\s\-]{3,10}$/', $f['pin_code'])) $errors['pin_code'] = 'Enter a valid PIN / ZIP code.';
    if (!preg_match('/^[0-9+\-\s()]{7,20}$/', $f['phone'])) $errors['phone'] = 'Enter a valid phone number.';
    if (!filter_var($f['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email address.';

    if ($f['ship_different']) {
        if ($f['ship_address'] === '') $errors['ship_address'] = 'Shipping address is required.';
        if ($f['ship_city'] === '') $errors['ship_city'] = 'Shipping city is required.';
        if ($f['ship_state'] === '') $errors['ship_state'] = 'Shipping state is required.';
        if (!preg_match('/^[A-Za-z0-9\s\-]{3,10}$/', $f['ship_pin'])) $errors['ship_pin'] = 'Enter a valid shipping PIN / ZIP code.';
    }

    if (!$errors) {
        $subtotal = $product['offer_price'] * $qty;
        $total    = $subtotal + SHIPPING_FEE;
        header('Location: ' . RAZORPAY_LINK . '?amount=' . $total);
        exit;
    }
}

$subtotal   = $product['offer_price'] * $qty;
$total      = $subtotal + SHIPPING_FEE;
$pageTitle  = 'Checkout | Vdesiconnect';
$activePage = '';
require __DIR__ . '/components/header.php';
?>

<div class="breadcrumb-bar">
  <div class="wrap">
    <a href="index.php" class="text-decoration-none">Home</a> /
    <a href="product-detail.php?id=<?= $product['id'] ?>" class="text-decoration-none"><?= $product['name'] ?></a> /
    <span>Checkout</span>
  </div>
</div>

<section class="py-5">
  <div class="wrap">
    <?php if ($errors): ?>
      <div class="alert alert-danger" role="alert">Please fix the highlighted fields below and try again.</div>
    <?php endif; ?>

    <form id="checkoutForm" method="post" action="checkout.php?id=<?= $product['id'] ?>" novalidate>
      <input type="hidden" name="id" value="<?= $product['id'] ?>">
      <div class="row g-5">
        <div class="col-lg-7">
          <h3 class="checkout-heading">Billing details</h3>
          <div class="row g-3">
            <div class="col-md-6">
              <label for="first_name" class="form-label">First name <span class="text-danger">*</span></label>
              <input type="text" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" id="first_name" name="first_name" value="<?= htmlspecialchars($f['first_name']) ?>" required>
              <?php if (isset($errors['first_name'])): ?><div class="invalid-feedback"><?= $errors['first_name'] ?></div><?php endif; ?>
            </div>
            <div class="col-md-6">
              <label for="last_name" class="form-label">Last name <span class="text-danger">*</span></label>
              <input type="text" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" id="last_name" name="last_name" value="<?= htmlspecialchars($f['last_name']) ?>" required>
              <?php if (isset($errors['last_name'])): ?><div class="invalid-feedback"><?= $errors['last_name'] ?></div><?php endif; ?>
            </div>
            <div class="col-12">
              <label for="company" class="form-label">Company name (optional)</label>
              <input type="text" class="form-control" id="company" name="company" value="<?= htmlspecialchars($f['company']) ?>">
            </div>
            <div class="col-12">
              <label for="country" class="form-label">Country / Region <span class="text-danger">*</span></label>
              <select class="form-select <?= isset($errors['country']) ? 'is-invalid' : '' ?>" id="country" name="country" required>
                <?php foreach (['India', 'USA'] as $c): ?>
                  <option value="<?= $c ?>" <?= $f['country'] === $c ? 'selected' : '' ?>><?= $c ?></option>
                <?php endforeach; ?>
              </select>
              <?php if (isset($errors['country'])): ?><div class="invalid-feedback"><?= $errors['country'] ?></div><?php endif; ?>
            </div>
            <div class="col-12">
              <label for="street_address" class="form-label">Street address <span class="text-danger">*</span></label>
              <input type="text" class="form-control mb-2 <?= isset($errors['street_address']) ? 'is-invalid' : '' ?>" id="street_address" name="street_address" placeholder="House number and street name" value="<?= htmlspecialchars($f['street_address']) ?>" required>
              <?php if (isset($errors['street_address'])): ?><div class="invalid-feedback d-block mb-2"><?= $errors['street_address'] ?></div><?php endif; ?>
              <input type="text" class="form-control" id="apartment" name="apartment" placeholder="Apartment, suite, unit, etc. (optional)" value="<?= htmlspecialchars($f['apartment']) ?>">
            </div>
            <div class="col-md-6">
              <label for="city" class="form-label">Town / City <span class="text-danger">*</span></label>
              <input type="text" class="form-control <?= isset($errors['city']) ? 'is-invalid' : '' ?>" id="city" name="city" value="<?= htmlspecialchars($f['city']) ?>" required>
              <?php if (isset($errors['city'])): ?><div class="invalid-feedback"><?= $errors['city'] ?></div><?php endif; ?>
            </div>
            <div class="col-md-6">
              <label for="state" class="form-label">State <span class="text-danger">*</span></label>
              <input type="text" class="form-control <?= isset($errors['state']) ? 'is-invalid' : '' ?>" id="state" name="state" value="<?= htmlspecialchars($f['state']) ?>" required>
              <?php if (isset($errors['state'])): ?><div class="invalid-feedback"><?= $errors['state'] ?></div><?php endif; ?>
            </div>
            <div class="col-md-6">
              <label for="pin_code" class="form-label">PIN / ZIP Code <span class="text-danger">*</span></label>
              <input type="text" class="form-control <?= isset($errors['pin_code']) ? 'is-invalid' : '' ?>" id="pin_code" name="pin_code" value="<?= htmlspecialchars($f['pin_code']) ?>" required>
              <?php if (isset($errors['pin_code'])): ?><div class="invalid-feedback"><?= $errors['pin_code'] ?></div><?php endif; ?>
            </div>
            <div class="col-md-6">
              <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
              <input type="tel" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" id="phone" name="phone" value="<?= htmlspecialchars($f['phone']) ?>" required>
              <?php if (isset($errors['phone'])): ?><div class="invalid-feedback"><?= $errors['phone'] ?></div><?php endif; ?>
            </div>
            <div class="col-12">
              <label for="email" class="form-label">Email address <span class="text-danger">*</span></label>
              <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= htmlspecialchars($f['email']) ?>" required>
              <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= $errors['email'] ?></div><?php endif; ?>
            </div>

            <div class="col-12 mt-2">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="ship_different" name="ship_different" <?= $f['ship_different'] ? 'checked' : '' ?>>
                <label class="form-check-label fw-semibold" for="ship_different">Ship to a different address?</label>
              </div>
            </div>

            <div id="shippingFields" class="col-12 <?= $f['ship_different'] ? '' : 'd-none' ?>">
              <h5 class="checkout-subheading mt-3">Shipping details</h5>
              <div class="row g-3">
                <div class="col-12">
                  <label for="ship_address" class="form-label">Shipping address</label>
                  <input type="text" class="form-control <?= isset($errors['ship_address']) ? 'is-invalid' : '' ?>" id="ship_address" name="ship_address" value="<?= htmlspecialchars($f['ship_address']) ?>">
                  <?php if (isset($errors['ship_address'])): ?><div class="invalid-feedback"><?= $errors['ship_address'] ?></div><?php endif; ?>
                </div>
                <div class="col-md-4">
                  <label for="ship_city" class="form-label">Town / City</label>
                  <input type="text" class="form-control <?= isset($errors['ship_city']) ? 'is-invalid' : '' ?>" id="ship_city" name="ship_city" value="<?= htmlspecialchars($f['ship_city']) ?>">
                  <?php if (isset($errors['ship_city'])): ?><div class="invalid-feedback"><?= $errors['ship_city'] ?></div><?php endif; ?>
                </div>
                <div class="col-md-4">
                  <label for="ship_state" class="form-label">State</label>
                  <input type="text" class="form-control <?= isset($errors['ship_state']) ? 'is-invalid' : '' ?>" id="ship_state" name="ship_state" value="<?= htmlspecialchars($f['ship_state']) ?>">
                  <?php if (isset($errors['ship_state'])): ?><div class="invalid-feedback"><?= $errors['ship_state'] ?></div><?php endif; ?>
                </div>
                <div class="col-md-4">
                  <label for="ship_pin" class="form-label">PIN / ZIP Code</label>
                  <input type="text" class="form-control <?= isset($errors['ship_pin']) ? 'is-invalid' : '' ?>" id="ship_pin" name="ship_pin" value="<?= htmlspecialchars($f['ship_pin']) ?>">
                  <?php if (isset($errors['ship_pin'])): ?><div class="invalid-feedback"><?= $errors['ship_pin'] ?></div><?php endif; ?>
                </div>
              </div>
            </div>

            <div class="col-12 mt-2">
              <label for="order_notes" class="form-label">Order notes (optional)</label>
              <textarea class="form-control" id="order_notes" name="order_notes" rows="3" placeholder="Notes about your order, e.g. special notes for delivery."><?= htmlspecialchars($f['order_notes']) ?></textarea>
            </div>
          </div>
        </div>

        <div class="col-lg-5">
          <div class="order-summary-card">
            <h3 class="checkout-heading">Your order</h3>
            <div class="order-line order-line-item">
              <div class="d-flex align-items-center gap-3">
                <img src="<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="order-thumb">
                <span><?= htmlspecialchars($product['name']) ?> <strong>&times; <span id="qtyDisplay"><?= $qty ?></span></strong></span>
              </div>
              <span id="lineTotal"><?= formatPrice($subtotal) ?></span>
            </div>

            <div class="order-line">
              <label for="qty" class="mb-0">Quantity</label>
              <input type="number" class="form-control qty-input" id="qty" name="qty" min="1" max="10" value="<?= $qty ?>" data-unit-price="<?= $product['offer_price'] ?>" data-shipping="<?= SHIPPING_FEE ?>">
            </div>

            <div class="order-line">
              <strong>Subtotal</strong>
              <strong id="subtotalDisplay"><?= formatPrice($subtotal) ?></strong>
            </div>
            <div class="order-line">
              <strong>Shipment</strong>
              <span>Standard shipping: <?= formatPrice(SHIPPING_FEE) ?></span>
            </div>
            <div class="order-line order-total">
              <strong>Total</strong>
              <strong id="totalDisplay"><?= formatPrice($total) ?></strong>
            </div>

            <div class="payment-method-box">
              <p class="mb-1"><i class="bi bi-check-circle-fill text-success"></i> <strong>Credit Card / Debit Card / NetBanking</strong> — Pay by Razorpay</p>
              <p class="small text-muted mb-0">Pay securely by Credit or Debit card or Internet Banking through Razorpay.</p>
            </div>

            <p class="small text-muted mt-3">Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our <a href="#">privacy policy</a>.</p>

            <button type="submit" class="btn btn-maroon btn-lg w-100 mt-2">Place Order</button>
          </div>
        </div>
      </div>
    </form>
  </div>
</section>

<?php require __DIR__ . '/components/footer.php'; ?>

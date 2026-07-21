<?php
require __DIR__ . '/data.php';

$orderId = (int) ($_GET['order_id'] ?? 0);
$pageTitle = 'Order Confirmed | Vdesiconnect';
$activePage = '';

if ($orderId > 0) {
    $statusParam = strtolower((string) ($_GET['status'] ?? ''));
    $allowedStatuses = ['success', 'failed', 'pending'];
    $paymentStatus = in_array($statusParam, $allowedStatuses, true) ? $statusParam : null;
    $paymentId = trim((string) ($_GET['payment_id'] ?? ''));

    if ($paymentStatus !== null) {
        updateOrderPaymentStatus($orderId, $paymentStatus, $paymentId !== '' ? $paymentId : null);
    }

    $pdo = getDbConnection();
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = :id');
    $stmt->execute([':id' => $orderId]);
    $order = $stmt->fetch();

    $itemStmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = :order_id ORDER BY id ASC');
    $itemStmt->execute([':order_id' => $orderId]);
    $items = $itemStmt->fetchAll();
} else {
    $order = null;
    $items = [];
}

require __DIR__ . '/components/header.php';
?>

<div class="breadcrumb-bar">
  <div class="wrap">
    <a href="index.php" class="text-decoration-none">Home</a> / <span>Order Confirmed</span>
  </div>
</div>

<section class="py-5">
  <div class="wrap">
    <div class="row justify-content-center">
      <div class="col-lg-9">
        <div class="card border-0 shadow-sm" style="border-radius: 24px; overflow: hidden;">
          <div class="p-4 p-md-5" style="background: linear-gradient(135deg, #fff6ec 0%, #ffe6c4 100%);">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
              <div>
                <?php
                $orderStatus = strtolower((string) ($order['status'] ?? 'pending'));
                $statusBadgeClass = $orderStatus === 'success' ? 'badge-soft text-success' : ($orderStatus === 'failed' ? 'badge-soft text-danger' : 'badge-soft text-warning');
                $statusLabel = $orderStatus === 'success' ? 'Payment & Order Confirmed' : ($orderStatus === 'failed' ? 'Payment Failed' : 'Payment Pending');
                ?>
                <div class="<?= htmlspecialchars($statusBadgeClass) ?> mb-3"><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($statusLabel) ?></div>
                <h2 class="mb-2" style="font-size: clamp(1.6rem, 3vw, 2.3rem);">Thank you for your order!</h2>
                <p class="mb-0 text-muted">Your purchase has been received and is being prepared for dispatch.</p>
              </div>
              <div class="text-md-end">
                <p class="mb-1 fw-semibold">Order Number</p>
                <h4 class="mb-0 text-primary">#<?= htmlspecialchars($order['order_number'] ?? 'N/A') ?></h4>
              </div>
            </div>
          </div>

          <div class="p-4 p-md-5">
            <?php if ($order): ?>
              <div class="row g-4">
                <div class="col-lg-7">
                  <div class="card border-0 bg-light h-100">
                    <div class="card-body">
                      <h5 class="mb-3">Invoice Summary</h5>
                      <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Customer</span>
                        <strong><?= htmlspecialchars(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? '')) ?></strong>
                      </div>
                      <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Email</span>
                        <strong><?= htmlspecialchars($order['email'] ?? '') ?></strong>
                      </div>
                      <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Phone</span>
                        <strong><?= htmlspecialchars($order['phone'] ?? '') ?></strong>
                      </div>
                      <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Shipping</span>
                        <strong><?= htmlspecialchars($order['city'] ?? '') ?>, <?= htmlspecialchars($order['state'] ?? '') ?></strong>
                      </div>
                      <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Date</span>
                        <strong><?= htmlspecialchars(date('d M Y, h:i A', strtotime($order['created_at'] ?? 'now'))) ?></strong>
                      </div>
                      <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">Status</span>
                        <span class="badge <?= $orderStatus === 'success' ? 'text-bg-success' : ($orderStatus === 'failed' ? 'text-bg-danger' : 'text-bg-warning') ?>"><?= htmlspecialchars(ucfirst($orderStatus)) ?></span>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-lg-5">
                  <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                      <h5 class="mb-3">Support & Contact</h5>
                      <div class="contact-info-item mb-3">
                        <span class="feature-icon"><i class="bi bi-telephone-fill"></i></span>
                        <div>
                          <h6>Call Us</h6>
                          <p>+91 98765 43210<br>+1 (415) 555-0198</p>
                        </div>
                      </div>
                      <div class="contact-info-item mb-3">
                        <span class="feature-icon"><i class="bi bi-envelope-fill"></i></span>
                        <div>
                          <h6>Email</h6>
                          <p>support@vdesiconnect.com</p>
                        </div>
                      </div>
                      <div class="contact-info-item">
                        <span class="feature-icon"><i class="bi bi-clock-fill"></i></span>
                        <div>
                          <h6>Support Hours</h6>
                          <p>Mon - Sat • 9:00 AM - 7:00 PM IST</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mt-4">
                <h5 class="mb-3">Items Ordered</h5>
                <div class="table-responsive">
                  <table class="table align-middle">
                    <thead class="table-light">
                      <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Subtotal</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($items as $item): ?>
                        <tr>
                          <td><?= htmlspecialchars($item['product_name']) ?></td>
                          <td><?= (int) $item['quantity'] ?></td>
                          <td>₹<?= number_format((float) $item['unit_price'], 0) ?></td>
                          <td>₹<?= number_format((float) $item['subtotal'], 0) ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>

              <div class="row mt-4">
                <div class="col-md-7">
                  <div class="p-3 rounded-4" style="background: #f8f9fa;">
                    <h6 class="mb-2">Delivery Address</h6>
                    <p class="mb-1"><strong><?= htmlspecialchars($order['first_name'] ?? '') ?> <?= htmlspecialchars($order['last_name'] ?? '') ?></strong></p>
                    <p class="mb-1"><?= htmlspecialchars($order['street_address'] ?? '') ?>, <?= htmlspecialchars($order['apartment'] ?? '') ?></p>
                    <p class="mb-0"><?= htmlspecialchars($order['city'] ?? '') ?>, <?= htmlspecialchars($order['state'] ?? '') ?> - <?= htmlspecialchars($order['pin_code'] ?? '') ?></p>
                  </div>
                </div>
                <div class="col-md-5">
                  <div class="p-3 rounded-4" style="background: #fff6ec; border: 1px solid #f2d8b3;">
                    <div class="d-flex justify-content-between mb-2">
                      <span>Subtotal</span>
                      <strong>₹<?= number_format((float) ($order['total_amount'] ?? 0) - SHIPPING_FEE, 0) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                      <span>Shipping</span>
                      <strong>₹<?= number_format(SHIPPING_FEE, 0) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between pt-2 border-top">
                      <span>Total</span>
                      <strong class="text-primary">₹<?= number_format((float) ($order['total_amount'] ?? 0), 0) ?></strong>
                    </div>
                  </div>
                </div>
              </div>
            <?php else: ?>
              <div class="alert alert-warning" role="alert">Order details could not be found. Please contact support if you need help.</div>
            <?php endif; ?>

            <div class="mt-4 text-center">
              <a href="index.php" class="btn btn-maroon me-2">Continue Shopping</a>
              <a href="contact.php" class="btn btn-outline-secondary me-2">Need Help?</a>
              <button type="button" class="btn btn-outline-dark" onclick="window.print()">Print Invoice</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/components/footer.php'; ?>

<?php
require __DIR__ . '/data.php';

$orderNumber = strtoupper(trim((string) ($_POST['order_number'] ?? '')));
$order = null;
$error = '';

$statusLabels = [
    'pending' => 'Pending',
    'success' => 'Payment successful',
    'confirmed' => 'Confirmed',
    'processing' => 'Processing',
    'shipped' => 'Shipped',
    'delivered' => 'Delivered',
    'cancelled' => 'Cancelled',
    'failed' => 'Failed',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (strlen($orderNumber) > 50 || !preg_match('/^[A-Z0-9-]+$/', $orderNumber)) {
        $error = 'Please enter a valid order number.';
    } else {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare('SELECT * FROM orders WHERE order_number = :order_number LIMIT 1');
        $stmt->execute([':order_number' => $orderNumber]);
        $order = $stmt->fetch();

        if ($order) {
            $itemStmt = $pdo->prepare('SELECT product_name, quantity, unit_price, subtotal FROM order_items WHERE order_id = :order_id ORDER BY id');
            $itemStmt->execute([':order_id' => $order['id']]);
            $order['items'] = $itemStmt->fetchAll();

            $historyStmt = $pdo->prepare('SELECT new_status, note, created_at FROM order_status_history WHERE order_id = :order_id ORDER BY created_at DESC, id DESC');
            $historyStmt->execute([':order_id' => $order['id']]);
            $order['history'] = $historyStmt->fetchAll();
        } else {
            $error = 'No order was found with that order number.';
        }
    }
}

function publicStatusClass(string $status): string
{
    if (in_array($status, ['success', 'confirmed', 'delivered'], true)) {
        return 'bg-success';
    }
    if (in_array($status, ['failed', 'cancelled'], true)) {
        return 'bg-danger';
    }

    return 'bg-warning text-dark';
}

$pageTitle = 'Order Status | Vdesiconnect';
$activePage = 'order-status';
require __DIR__ . '/components/header.php';
?>

<section class="py-5 bg-light">
  <div class="wrap">
    <div class="mx-auto" style="max-width:850px">
      <div class="text-center mb-4">
        <h1 class="checkout-heading">Track Your Order</h1>
        <p class="text-muted">Enter the order number from your confirmation email.</p>
      </div>

      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
          <form method="post" class="row g-3 align-items-end">
            <div class="col-md">
              <label for="order_number" class="form-label fw-semibold">Order number</label>
              <input type="text" class="form-control form-control-lg" id="order_number" name="order_number" value="<?= htmlspecialchars($orderNumber) ?>" placeholder="ORD-20260731123456-1234" maxlength="50" required>
            </div>
            <div class="col-md-auto">
              <button type="submit" class="btn btn-maroon btn-lg w-100"><i class="bi bi-search"></i> Check status</button>
            </div>
          </form>
        </div>
      </div>

      <?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if ($order): ?>
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
              <div>
                <div class="text-muted small">ORDER NUMBER</div>
                <h2 class="h4 mb-1"><?= htmlspecialchars($order['order_number']) ?></h2>
                <div class="text-muted"><?= htmlspecialchars(date('d M Y, h:i A', strtotime($order['created_at']))) ?></div>
              </div>
              <span class="badge rounded-pill fs-6 px-3 py-2 <?= publicStatusClass((string) $order['order_status']) ?>">
                <?= htmlspecialchars($statusLabels[$order['order_status']] ?? ucfirst($order['order_status'])) ?>
              </span>
            </div>
          </div>
        </div>

        <div class="row g-4 mb-4">
          <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-body p-4">
                <h2 class="h5 mb-3">Customer and billing</h2>
                <p class="mb-1"><strong><?= htmlspecialchars(trim($order['first_name'] . ' ' . $order['last_name'])) ?></strong></p>
                <?php if (!empty($order['company'])): ?><p class="mb-1"><?= htmlspecialchars($order['company']) ?></p><?php endif; ?>
                <p class="mb-1"><?= htmlspecialchars($order['street_address']) ?></p>
                <?php if (!empty($order['apartment'])): ?><p class="mb-1"><?= htmlspecialchars($order['apartment']) ?></p><?php endif; ?>
                <p class="mb-1"><?= htmlspecialchars($order['city']) ?>, <?= htmlspecialchars($order['state']) ?> <?= htmlspecialchars($order['pin_code']) ?></p>
                <p class="mb-1"><?= htmlspecialchars($order['country']) ?></p>
                <p class="mb-1"><i class="bi bi-envelope"></i> <?= htmlspecialchars($order['email']) ?></p>
                <p class="mb-0"><i class="bi bi-telephone"></i> <?= htmlspecialchars($order['phone']) ?></p>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-body p-4">
                <h2 class="h5 mb-3">Shipping address</h2>
                <?php if (!empty($order['ship_different'])): ?>
                  <p class="mb-1"><?= htmlspecialchars($order['ship_address']) ?></p>
                  <p class="mb-0"><?= htmlspecialchars($order['ship_city']) ?>, <?= htmlspecialchars($order['ship_state']) ?> <?= htmlspecialchars($order['ship_pin']) ?></p>
                <?php else: ?>
                  <p class="mb-1"><?= htmlspecialchars($order['street_address']) ?></p>
                  <?php if (!empty($order['apartment'])): ?><p class="mb-1"><?= htmlspecialchars($order['apartment']) ?></p><?php endif; ?>
                  <p class="mb-1"><?= htmlspecialchars($order['city']) ?>, <?= htmlspecialchars($order['state']) ?> <?= htmlspecialchars($order['pin_code']) ?></p>
                  <p class="mb-0"><?= htmlspecialchars($order['country']) ?></p>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body p-4">
            <h2 class="h5 mb-3">Order details</h2>
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead><tr><th>Item</th><th>Quantity</th><th class="text-end">Amount</th></tr></thead>
                <tbody>
                  <?php foreach ($order['items'] as $item): ?>
                    <tr>
                      <td><?= htmlspecialchars($item['product_name']) ?></td>
                      <td><?= (int) $item['quantity'] ?></td>
                      <td class="text-end"><?= formatPrice((float) $item['subtotal']) ?></td>
                    </tr>
                  <?php endforeach; ?>
                  <tr>
                    <td colspan="2" class="text-end fw-bold">Order total</td>
                    <td class="text-end fw-bold"><?= formatPrice((float) $order['total_amount']) ?></td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="row g-3 mt-3">
              <div class="col-md-6"><strong>Payment status:</strong> <?= htmlspecialchars(ucfirst((string) $order['status'])) ?></div>
              <div class="col-md-6"><strong>Payment ID:</strong> <?= htmlspecialchars($order['payment_id'] ?: 'Not available') ?></div>
              <div class="col-md-6"><strong>Referral code:</strong> <?= htmlspecialchars($order['referral_code'] ?: 'None') ?></div>
            </div>
            <?php if (!empty($order['order_notes'])): ?>
              <div class="mt-3"><strong>Order notes:</strong><p class="mb-0 mt-1"><?= nl2br(htmlspecialchars($order['order_notes'])) ?></p></div>
            <?php endif; ?>
          </div>
        </div>

        <div class="card border-0 shadow-sm">
          <div class="card-body p-4">
            <h2 class="h5 mb-4">Status updates</h2>
            <?php if (empty($order['history'])): ?>
              <div class="d-flex gap-3">
                <i class="bi bi-clock-history fs-4 text-warning"></i>
                <div><strong>Order received</strong><div class="text-muted">Your order is currently pending.</div></div>
              </div>
            <?php else: ?>
              <?php foreach ($order['history'] as $index => $change): ?>
                <div class="d-flex gap-3 <?= $index < count($order['history']) - 1 ? 'border-bottom pb-3 mb-3' : '' ?>">
                  <i class="bi bi-check-circle-fill fs-4 text-success"></i>
                  <div>
                    <strong><?= htmlspecialchars($statusLabels[$change['new_status']] ?? ucfirst($change['new_status'])) ?></strong>
                    <div class="text-muted small"><?= htmlspecialchars(date('d M Y, h:i A', strtotime($change['created_at']))) ?></div>
                    <?php if (!empty($change['note'])): ?><p class="mb-0 mt-2"><?= nl2br(htmlspecialchars($change['note'])) ?></p><?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/components/footer.php'; ?>

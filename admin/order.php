<?php
require __DIR__ . '/bootstrap.php';
adminRequireLogin();

$orderId = (int) ($_GET['id'] ?? 0);
$order = adminGetOrder($orderId);

if (!$order) {
    http_response_code(404);
    adminRenderHeader('Order not found');
    echo '<div class="card"><h1>Order not found</h1><p>The requested order does not exist.</p><a class="btn btn-primary" href="index.php">Back to orders</a></div>';
    adminRenderFooter();
    exit;
}

$updateError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = (string) ($_POST['csrf_token'] ?? '');
    $newStatus = (string) ($_POST['order_status'] ?? '');
    $adminNote = trim((string) ($_POST['admin_note'] ?? ''));

    if (!adminVerifyCsrf($csrf)) {
        $updateError = 'Your session expired. Please try again.';
    } elseif (strlen($adminNote) > 2000) {
        $updateError = 'The status note must be 2,000 characters or fewer.';
    } else {
        try {
            adminUpdateOrderStatus(
                $orderId,
                $newStatus,
                $adminNote,
                (string) ($_SESSION['admin_username'] ?? 'admin')
            );
            header('Location: order.php?id=' . $orderId . '&updated=1');
            exit;
        } catch (Throwable $error) {
            $updateError = $error->getMessage();
        }
    }
}

$itemsSubtotal = 0.0;
foreach ($order['items'] as $item) {
    $itemsSubtotal += (float) $item['subtotal'];
}
$shippingFee = max(0, (float) $order['total_amount'] - $itemsSubtotal);

adminRenderHeader('Order ' . $order['order_number']);
?>
<div style="display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap;margin-bottom:20px">
  <div>
    <a href="index.php">&larr; Back to orders</a>
    <h1 style="margin-top:10px"><?= adminEscape($order['order_number']) ?></h1>
  </div>
  <div class="actions">
    <a class="btn btn-light" href="receipt.php?id=<?= (int) $order['id'] ?>" target="_blank">Print receipt</a>
  </div>
</div>

<?php if (isset($_GET['updated'])): ?><div class="alert alert-success">Order status updated successfully.</div><?php endif; ?>
<?php if ($updateError !== ''): ?><div class="alert alert-error"><?= adminEscape($updateError) ?></div><?php endif; ?>

<section class="card">
  <h2>Update order status</h2>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= adminEscape(adminCsrfToken()) ?>">
    <div class="grid grid-2">
      <div>
        <label for="order_status">New status</label>
        <select id="order_status" name="order_status" required>
          <?php foreach (adminOrderStatuses() as $value => $label): ?>
            <option value="<?= adminEscape($value) ?>" <?= $order['order_status'] === $value ? 'selected' : '' ?>><?= adminEscape($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label for="admin_note">Status note shown to customer (optional)</label>
        <textarea id="admin_note" name="admin_note" rows="3" maxlength="2000" placeholder="Example: Package handed to courier"></textarea>
      </div>
    </div>
    <button type="submit" class="btn btn-primary" style="margin-top:15px">Save status</button>
  </form>
</section>

<div class="grid grid-2">
  <section class="card">
    <h2>Order information</h2>
    <dl class="detail-list">
      <dt>Date</dt><dd><?= adminEscape(date('d M Y, h:i A', strtotime($order['created_at']))) ?></dd>
      <dt>Order status</dt><dd><span class="status <?= adminStatusClass((string) $order['order_status']) ?>"><?= adminEscape($order['order_status']) ?></span></dd>
      <dt>Payment status</dt><dd><?= adminEscape($order['status']) ?></dd>
      <dt>Payment ID</dt><dd><?= adminEscape($order['payment_id'] ?: 'Not available') ?></dd>
      <dt>Referral code</dt><dd><?= adminEscape($order['referral_code'] ?: 'None') ?></dd>
    </dl>
  </section>

  <section class="card">
    <h2>Customer</h2>
    <dl class="detail-list">
      <dt>Name</dt><dd><?= adminEscape(trim($order['first_name'] . ' ' . $order['last_name'])) ?></dd>
      <?php if (!empty($order['company'])): ?><dt>Company</dt><dd><?= adminEscape($order['company']) ?></dd><?php endif; ?>
      <dt>Email</dt><dd><a href="mailto:<?= adminEscape($order['email']) ?>"><?= adminEscape($order['email']) ?></a></dd>
      <dt>Phone</dt><dd><?= adminEscape($order['phone']) ?></dd>
    </dl>
  </section>

  <section class="card">
    <h2>Billing address</h2>
    <p style="line-height:1.65;margin:0">
      <?= adminEscape($order['street_address']) ?><br>
      <?php if (!empty($order['apartment'])): ?><?= adminEscape($order['apartment']) ?><br><?php endif; ?>
      <?= adminEscape($order['city']) ?>, <?= adminEscape($order['state']) ?> <?= adminEscape($order['pin_code']) ?><br>
      <?= adminEscape($order['country']) ?>
    </p>
  </section>

  <section class="card">
    <h2>Shipping address</h2>
    <p style="line-height:1.65;margin:0">
      <?php if (!empty($order['ship_different'])): ?>
        <?= adminEscape($order['ship_address']) ?><br>
        <?= adminEscape($order['ship_city']) ?>, <?= adminEscape($order['ship_state']) ?> <?= adminEscape($order['ship_pin']) ?>
      <?php else: ?>
        Same as billing address
      <?php endif; ?>
    </p>
  </section>
</div>

<section class="card table-wrap">
  <h2>Items</h2>
  <table>
    <thead><tr><th>Product</th><th>Quantity</th><th>Unit price</th><th>Subtotal</th></tr></thead>
    <tbody>
      <?php foreach ($order['items'] as $item): ?>
        <tr>
          <td><?= adminEscape($item['product_name']) ?></td>
          <td><?= (int) $item['quantity'] ?></td>
          <td><?= formatPrice((float) $item['unit_price']) ?></td>
          <td><?= formatPrice((float) $item['subtotal']) ?></td>
        </tr>
      <?php endforeach; ?>
      <tr><td colspan="3" style="text-align:right"><strong>Shipping</strong></td><td><?= formatPrice($shippingFee) ?></td></tr>
      <tr><td colspan="3" style="text-align:right"><strong>Total</strong></td><td><strong><?= formatPrice((float) $order['total_amount']) ?></strong></td></tr>
    </tbody>
  </table>
</section>

<?php if (!empty($order['order_notes'])): ?>
  <section class="card">
    <h2>Order notes</h2>
    <p style="white-space:pre-wrap;margin-bottom:0"><?= adminEscape($order['order_notes']) ?></p>
  </section>
<?php endif; ?>

<section class="card">
  <h2>Status history</h2>
  <?php if (empty($order['status_history'])): ?>
    <p class="muted" style="margin-bottom:0">No status changes have been recorded yet.</p>
  <?php else: ?>
    <ol class="history">
      <?php foreach ($order['status_history'] as $change): ?>
        <li>
          <strong><?= adminEscape(ucfirst($change['old_status'])) ?> &rarr; <?= adminEscape(ucfirst($change['new_status'])) ?></strong><br>
          <span class="muted"><?= adminEscape(date('d M Y, h:i A', strtotime($change['created_at']))) ?> by <?= adminEscape($change['changed_by']) ?></span>
          <?php if (!empty($change['note'])): ?><p class="history-note"><?= adminEscape($change['note']) ?></p><?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ol>
  <?php endif; ?>
</section>

<?php adminRenderFooter(); ?>

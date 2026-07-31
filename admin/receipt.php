<?php
require __DIR__ . '/bootstrap.php';
adminRequireLogin();

$orderId = (int) ($_GET['id'] ?? 0);
$order = adminGetOrder($orderId);

if (!$order) {
    http_response_code(404);
    exit('Order not found.');
}

$itemsSubtotal = 0.0;
foreach ($order['items'] as $item) {
    $itemsSubtotal += (float) $item['subtotal'];
}
$shippingFee = max(0, (float) $order['total_amount'] - $itemsSubtotal);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="robots" content="noindex,nofollow">
  <title>Receipt <?= adminEscape($order['order_number']) ?></title>
  <style>
    *{box-sizing:border-box}body{font-family:Arial,sans-serif;color:#2b2424;margin:0;background:#eee;padding:25px}
    .toolbar{max-width:820px;margin:0 auto 15px;display:flex;justify-content:space-between}
    .btn{border:0;border-radius:5px;padding:10px 15px;text-decoration:none;cursor:pointer;background:#7b1831;color:#fff}
    .receipt{max-width:820px;margin:auto;background:#fff;padding:38px}
    .top{display:flex;justify-content:space-between;gap:20px;border-bottom:3px solid #7b1831;padding-bottom:18px}
    h1{color:#7b1831;margin:0}.right{text-align:right}.muted{color:#71676a}.addresses{display:grid;grid-template-columns:1fr 1fr;gap:35px;margin:28px 0;line-height:1.6}
    table{border-collapse:collapse;width:100%}th{background:#7b1831;color:#fff;text-align:left}th,td{padding:11px;border:1px solid #ddd}
    .amount{text-align:right}.total td{font-size:18px;font-weight:bold}.meta{margin-top:25px;line-height:1.7}
    @media(max-width:600px){body{padding:0}.receipt{padding:22px}.top,.addresses{grid-template-columns:1fr;display:grid}.right{text-align:left}}
    @media print{body{background:#fff;padding:0}.toolbar{display:none}.receipt{max-width:none;padding:0}}
  </style>
</head>
<body>
  <div class="toolbar">
    <a class="btn" href="order.php?id=<?= (int) $order['id'] ?>">&larr; Back</a>
    <button class="btn" type="button" onclick="window.print()">Print receipt</button>
  </div>

  <main class="receipt">
    <div class="top">
      <div><h1>Vdesiconnect</h1><div class="muted">Raksha Bandhan Gifts</div></div>
      <div class="right">
        <strong>ORDER RECEIPT</strong><br>
        <?= adminEscape($order['order_number']) ?><br>
        <span class="muted"><?= adminEscape(date('d M Y, h:i A', strtotime($order['created_at']))) ?></span>
      </div>
    </div>

    <div class="addresses">
      <div>
        <strong>Bill to</strong><br>
        <?= adminEscape(trim($order['first_name'] . ' ' . $order['last_name'])) ?><br>
        <?php if (!empty($order['company'])): ?><?= adminEscape($order['company']) ?><br><?php endif; ?>
        <?= adminEscape($order['street_address']) ?><br>
        <?php if (!empty($order['apartment'])): ?><?= adminEscape($order['apartment']) ?><br><?php endif; ?>
        <?= adminEscape($order['city']) ?>, <?= adminEscape($order['state']) ?> <?= adminEscape($order['pin_code']) ?><br>
        <?= adminEscape($order['country']) ?><br>
        <?= adminEscape($order['phone']) ?><br><?= adminEscape($order['email']) ?>
      </div>
      <div>
        <strong>Ship to</strong><br>
        <?php if (!empty($order['ship_different'])): ?>
          <?= adminEscape($order['ship_address']) ?><br>
          <?= adminEscape($order['ship_city']) ?>, <?= adminEscape($order['ship_state']) ?> <?= adminEscape($order['ship_pin']) ?>
        <?php else: ?>
          <?= adminEscape($order['street_address']) ?><br>
          <?php if (!empty($order['apartment'])): ?><?= adminEscape($order['apartment']) ?><br><?php endif; ?>
          <?= adminEscape($order['city']) ?>, <?= adminEscape($order['state']) ?> <?= adminEscape($order['pin_code']) ?><br>
          <?= adminEscape($order['country']) ?>
        <?php endif; ?>
      </div>
    </div>

    <table>
      <thead><tr><th>Item</th><th>Qty</th><th class="amount">Unit price</th><th class="amount">Amount</th></tr></thead>
      <tbody>
        <?php foreach ($order['items'] as $item): ?>
          <tr>
            <td><?= adminEscape($item['product_name']) ?></td>
            <td><?= (int) $item['quantity'] ?></td>
            <td class="amount"><?= formatPrice((float) $item['unit_price']) ?></td>
            <td class="amount"><?= formatPrice((float) $item['subtotal']) ?></td>
          </tr>
        <?php endforeach; ?>
        <tr><td colspan="3" class="amount">Shipping</td><td class="amount"><?= formatPrice($shippingFee) ?></td></tr>
        <tr class="total"><td colspan="3" class="amount">Total</td><td class="amount"><?= formatPrice((float) $order['total_amount']) ?></td></tr>
      </tbody>
    </table>

    <div class="meta">
      <strong>Order status:</strong> <?= adminEscape(ucfirst((string) $order['order_status'])) ?><br>
      <strong>Payment status:</strong> <?= adminEscape(ucfirst((string) $order['status'])) ?><br>
      <?php if (!empty($order['payment_id'])): ?><strong>Payment ID:</strong> <?= adminEscape($order['payment_id']) ?><br><?php endif; ?>
      <?php if (!empty($order['referral_code'])): ?><strong>Referral code:</strong> <?= adminEscape($order['referral_code']) ?><?php endif; ?>
    </div>
  </main>
</body>
</html>

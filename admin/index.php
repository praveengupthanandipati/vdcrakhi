<?php
require __DIR__ . '/bootstrap.php';
adminRequireLogin();

function adminValidDate(string $value): bool
{
    if ($value === '') {
        return true;
    }

    $date = DateTime::createFromFormat('!Y-m-d', $value);
    return $date instanceof DateTime && $date->format('Y-m-d') === $value;
}

$orderNumber = trim((string) ($_GET['order_number'] ?? ''));
$fromDate = trim((string) ($_GET['from_date'] ?? ''));
$toDate = trim((string) ($_GET['to_date'] ?? ''));
$filterError = '';

if (!adminValidDate($fromDate) || !adminValidDate($toDate)) {
    $filterError = 'Please enter valid filter dates.';
}
if ($filterError === '' && $fromDate !== '' && $toDate !== '' && $fromDate > $toDate) {
    $filterError = 'From date cannot be later than to date.';
}

$where = [];
$params = [];

if ($orderNumber !== '') {
    $where[] = 'order_number LIKE :order_number';
    $params[':order_number'] = '%' . $orderNumber . '%';
}
if ($filterError === '' && $fromDate !== '') {
    $where[] = 'created_at >= :from_date';
    $params[':from_date'] = $fromDate . ' 00:00:00';
}
if ($filterError === '' && $toDate !== '') {
    $toExclusive = (new DateTime($toDate))->modify('+1 day')->format('Y-m-d 00:00:00');
    $where[] = 'created_at < :to_date';
    $params[':to_date'] = $toExclusive;
}

$sql = 'SELECT id, order_number, first_name, last_name, email, phone, total_amount, order_status, created_at FROM orders';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY created_at DESC LIMIT 500';

$stmt = getDbConnection()->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

adminRenderHeader('Orders');
?>
<div style="display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap">
  <h1>Orders</h1>
  <span class="muted"><?= count($orders) ?> order<?= count($orders) === 1 ? '' : 's' ?></span>
</div>

<section class="card">
  <form method="get" class="filters">
    <div>
      <label for="order_number">Order number</label>
      <input type="search" id="order_number" name="order_number" value="<?= adminEscape($orderNumber) ?>" placeholder="Example: ORD-2026">
    </div>
    <div>
      <label for="from_date">From date</label>
      <input type="date" id="from_date" name="from_date" value="<?= adminEscape($fromDate) ?>">
    </div>
    <div>
      <label for="to_date">To date</label>
      <input type="date" id="to_date" name="to_date" value="<?= adminEscape($toDate) ?>">
    </div>
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="index.php" class="btn btn-light">Clear</a>
  </form>
</section>

<?php if ($filterError !== ''): ?><div class="alert alert-error"><?= adminEscape($filterError) ?></div><?php endif; ?>

<section class="card table-wrap">
  <?php if (!$orders): ?>
    <div class="empty">No orders match the selected filters.</div>
  <?php else: ?>
    <table>
      <thead>
        <tr><th>Order</th><th>Date</th><th>Customer</th><th>Contact</th><th>Total</th><th>Status</th><th></th></tr>
      </thead>
      <tbody>
      <?php foreach ($orders as $order): ?>
        <tr>
          <td><strong><?= adminEscape($order['order_number']) ?></strong></td>
          <td><?= adminEscape(date('d M Y, h:i A', strtotime($order['created_at']))) ?></td>
          <td><?= adminEscape(trim($order['first_name'] . ' ' . $order['last_name'])) ?></td>
          <td><?= adminEscape($order['email']) ?><br><span class="muted"><?= adminEscape($order['phone']) ?></span></td>
          <td><?= formatPrice((float) $order['total_amount']) ?></td>
          <td><span class="status <?= adminStatusClass((string) $order['order_status']) ?>"><?= adminEscape($order['order_status']) ?></span></td>
          <td><a class="btn btn-light" href="order.php?id=<?= (int) $order['id'] ?>">View</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</section>
<?php adminRenderFooter(); ?>

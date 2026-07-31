<?php
require_once dirname(__DIR__) . '/data.php';

$adminConfig = require dirname(__DIR__) . '/config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('vdc_admin_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function adminEscape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function adminIsLoggedIn(): bool
{
    return !empty($_SESSION['admin_logged_in']);
}

function adminRequireLogin(): void
{
    if (!adminIsLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function adminCsrfToken(): string
{
    if (empty($_SESSION['admin_csrf'])) {
        $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['admin_csrf'];
}

function adminVerifyCsrf(string $token): bool
{
    return !empty($_SESSION['admin_csrf']) && hash_equals($_SESSION['admin_csrf'], $token);
}

function adminGetOrder(int $id): ?array
{
    $pdo = getDbConnection();
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $order = $stmt->fetch();

    if (!$order) {
        return null;
    }

    $itemStmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = :order_id ORDER BY id');
    $itemStmt->execute([':order_id' => $id]);
    $order['items'] = $itemStmt->fetchAll();

    $historyStmt = $pdo->prepare('SELECT * FROM order_status_history WHERE order_id = :order_id ORDER BY created_at DESC, id DESC');
    $historyStmt->execute([':order_id' => $id]);
    $order['status_history'] = $historyStmt->fetchAll();

    return $order;
}

function adminOrderStatuses(): array
{
    return [
        'pending' => 'Pending',
        'success' => 'Payment successful',
        'confirmed' => 'Confirmed',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
        'failed' => 'Failed',
    ];
}

function adminUpdateOrderStatus(int $orderId, string $newStatus, string $note, string $changedBy): void
{
    $statuses = adminOrderStatuses();
    if (!isset($statuses[$newStatus])) {
        throw new InvalidArgumentException('Invalid order status.');
    }

    $pdo = getDbConnection();
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare('SELECT order_status FROM orders WHERE id = :id FOR UPDATE');
        $stmt->execute([':id' => $orderId]);
        $oldStatus = $stmt->fetchColumn();

        if ($oldStatus === false) {
            throw new RuntimeException('Order not found.');
        }

        $update = $pdo->prepare('UPDATE orders SET order_status = :status WHERE id = :id');
        $update->execute([':status' => $newStatus, ':id' => $orderId]);

        $history = $pdo->prepare('INSERT INTO order_status_history (order_id, old_status, new_status, note, changed_by) VALUES (:order_id, :old_status, :new_status, :note, :changed_by)');
        $history->execute([
            ':order_id' => $orderId,
            ':old_status' => $oldStatus,
            ':new_status' => $newStatus,
            ':note' => $note !== '' ? $note : null,
            ':changed_by' => $changedBy,
        ]);

        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}

function adminStatusClass(string $status): string
{
    if (in_array($status, ['success', 'confirmed', 'delivered'], true)) {
        return 'status-success';
    }
    if (in_array($status, ['failed', 'cancelled'], true)) {
        return 'status-failed';
    }

    return 'status-pending';
}

function adminRenderHeader(string $title): void
{
    $safeTitle = adminEscape($title);
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<meta name="robots" content="noindex,nofollow">'
        . '<title>' . $safeTitle . ' | Vdesiconnect Admin</title>'
        . '<style>'
        . '*{box-sizing:border-box}body{margin:0;background:#f5f2f3;color:#2d2527;font-family:Arial,sans-serif}'
        . 'a{color:#7b1831}.admin-top{background:#66152b;color:#fff;padding:15px 24px}'
        . '.admin-nav{max-width:1180px;margin:auto;display:flex;align-items:center;justify-content:space-between;gap:20px}'
        . '.admin-brand{display:flex;align-items:center;gap:12px;color:#fff;text-decoration:none;font-size:21px;font-weight:bold}.admin-brand img{height:44px;width:auto;background:#fff;border-radius:4px;padding:2px}.admin-links{display:flex;gap:18px}.admin-links a{color:#fff;text-decoration:none}'
        . '.admin-main{max-width:1180px;margin:28px auto;padding:0 18px}.card{background:#fff;border:1px solid #ddd;border-radius:10px;padding:22px;margin-bottom:20px}'
        . 'h1{margin:0 0 20px;font-size:28px}h2{font-size:20px;margin:0 0 16px}.grid{display:grid;gap:18px}.grid-2{grid-template-columns:repeat(2,minmax(0,1fr))}'
        . '.filters{display:grid;grid-template-columns:2fr 1fr 1fr auto auto;gap:12px;align-items:end}'
        . 'label{display:block;font-size:13px;font-weight:bold;margin-bottom:6px}input,select,textarea{width:100%;padding:10px;border:1px solid #bbb;border-radius:5px;font:inherit}textarea{resize:vertical}'
        . '.btn{display:inline-block;border:0;border-radius:5px;padding:10px 15px;text-decoration:none;cursor:pointer;font-size:14px}'
        . '.btn-primary{background:#7b1831;color:#fff}.btn-light{background:#eee;color:#333}.actions{display:flex;gap:8px;flex-wrap:wrap}'
        . '.table-wrap{overflow:auto}table{width:100%;border-collapse:collapse}th,td{text-align:left;padding:12px;border-bottom:1px solid #e3e3e3;white-space:nowrap}'
        . 'th{background:#faf7f8;font-size:13px}.status{display:inline-block;padding:5px 9px;border-radius:20px;font-size:12px;font-weight:bold;text-transform:capitalize}'
        . '.status-success{background:#dff4e5;color:#176b31}.status-failed{background:#fde2e2;color:#a21f1f}.status-pending{background:#fff0c9;color:#805d00}'
        . '.muted{color:#73696c}.detail-list{margin:0}.detail-list dt{font-weight:bold;margin-top:10px}.detail-list dd{margin:3px 0 0;line-height:1.5}'
        . '.alert{padding:12px 14px;border-radius:6px;margin-bottom:18px}.alert-error{background:#fde5e5;color:#8d1515}.alert-success{background:#dff4e5;color:#176b31}.empty{text-align:center;padding:35px;color:#756d70}'
        . '.history{list-style:none;padding:0;margin:0}.history li{border-left:3px solid #d7c4ca;padding:0 0 20px 18px;margin-left:5px}.history li:last-child{padding-bottom:0}.history-note{white-space:pre-wrap;margin:8px 0 0}'
        . '@media(max-width:800px){.filters{grid-template-columns:1fr}.grid-2{grid-template-columns:1fr}.admin-nav{align-items:flex-start}.admin-links{flex-direction:column;gap:8px}}'
        . '</style></head><body>'
        . '<header class="admin-top"><nav class="admin-nav"><a class="admin-brand" href="index.php"><img src="../img/logo.png" alt="Vdesiconnect logo"><span>Admin Dashboard</span></a>'
        . '<div class="admin-links"><a href="index.php">Orders</a><a href="../index.php" target="_blank">View store</a><a href="logout.php">Logout</a></div>'
        . '</nav></header><main class="admin-main">';
}

function adminRenderFooter(): void
{
    echo '</main></body></html>';
}

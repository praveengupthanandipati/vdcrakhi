<?php
require __DIR__ . '/order-mailer.php';

header('Cache-Control: no-store, no-cache, must-revalidate');
header('X-Robots-Tag: noindex, nofollow', true);

$config = require __DIR__ . '/config.php';
$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $testNumber = 'MAIL-TEST-' . date('Ymd-His');
    $testOrder = [
        'number' => $testNumber,
        'date' => date('d M Y, h:i A'),
        'customer_name' => 'Mail Test',
        'company' => '',
        'street_address' => 'SMTP server test',
        'apartment' => '',
        'city' => 'Test',
        'state' => 'Test',
        'pin_code' => '000000',
        'country' => 'India',
        'phone' => '0000000000',
        'email' => $config['admin_email'],
        'ship_different' => false,
        'ship_address' => '',
        'ship_city' => '',
        'ship_state' => '',
        'ship_pin' => '',
        'product_name' => 'SMTP test message',
        'qty' => 1,
        'unit_price' => 0.0,
        'subtotal' => 0.0,
        'shipping_fee' => 0.0,
        'total' => 0.0,
    ];

    try {
        sendSmtpHtml(
            $config,
            $config['admin_email'],
            'Vdesiconnect Admin',
            'Vdesiconnect SMTP test - ' . date(DATE_RFC2822),
            '<h2>SMTP test successful</h2><p>This email confirms that the server can connect, authenticate, and send through the configured SMTP account.</p>',
            buildInvoicePdf($testOrder),
            $testNumber
        );
        $success = true;
        $message = 'Test email sent successfully to the configured admin address.';
    } catch (Throwable $error) {
        logOrderEmailError('test', $testOrder, $config, $error);
        $message = $error->getMessage();
    }
}

function testPageEscape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SMTP Mail Test</title>
  <style>
    body{font-family:Arial,sans-serif;background:#f5f5f5;color:#222;margin:0;padding:40px 16px}
    main{max-width:620px;margin:auto;background:#fff;border:1px solid #ddd;border-radius:10px;padding:28px}
    h1{margin-top:0}
    button{background:#7b1831;color:#fff;border:0;border-radius:5px;padding:12px 18px;cursor:pointer}
    .result{padding:13px;border-radius:5px;margin-bottom:18px}.ok{background:#e4f6e8;color:#176b2c}.error{background:#fde7e7;color:#9b1c1c}
  </style>
</head>
<body>
<main>
  <h1>SMTP Mail Test</h1>
  <p>This sends one test email to the configured admin address.</p>

  <?php if ($message !== ''): ?>
    <div class="result <?= $success ? 'ok' : 'error' ?>"><?= testPageEscape($message) ?></div>
  <?php endif; ?>

  <form method="post">
    <button type="submit">Send test email</button>
  </form>
</main>
</body>
</html>

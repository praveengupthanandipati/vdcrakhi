<?php

function mailEscape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function formatMailPrice(float $amount): string
{
    return '&#8377;' . number_format($amount, 0);
}

function buildInvoiceHtml(array $order): string
{
    $shippingAddress = $order['ship_different']
        ? "{$order['ship_address']}, {$order['ship_city']}, {$order['ship_state']} {$order['ship_pin']}"
        : "{$order['street_address']}"
            . ($order['apartment'] !== '' ? ", {$order['apartment']}" : '')
            . ", {$order['city']}, {$order['state']} {$order['pin_code']}";

    $company = $order['company'] !== ''
        ? '<br>' . mailEscape($order['company'])
        : '';

    return '<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Invoice ' . mailEscape($order['number']) . '</title>
  <style>
    body{font-family:Arial,sans-serif;color:#2b2424;margin:0;padding:32px}
    .invoice{max-width:760px;margin:auto}
    .top{display:flex;justify-content:space-between;border-bottom:3px solid #7b1831;padding-bottom:18px}
    h1{color:#7b1831;margin:0}.muted{color:#706767}.right{text-align:right}
    .addresses{display:flex;gap:40px;margin:28px 0}.addresses>div{flex:1;line-height:1.6}
    table{border-collapse:collapse;width:100%}th{background:#7b1831;color:#fff;text-align:left}
    th,td{padding:12px;border:1px solid #ddd}.amount{text-align:right}
    .total td{font-size:18px;font-weight:bold}.status{color:#a26300;font-weight:bold}
    @media print{body{padding:0}}
  </style>
</head>
<body>
<div class="invoice">
  <div class="top">
    <div><h1>Vdesiconnect</h1><div class="muted">Raksha Bandhan Gifts</div></div>
    <div class="right"><strong>INVOICE</strong><br>' . mailEscape($order['number']) . '<br><span class="muted">' . mailEscape($order['date']) . '</span></div>
  </div>
  <div class="addresses">
    <div><strong>Bill to</strong><br>' . mailEscape($order['customer_name']) . $company . '<br>'
        . mailEscape($order['street_address']) . '<br>'
        . ($order['apartment'] !== '' ? mailEscape($order['apartment']) . '<br>' : '')
        . mailEscape("{$order['city']}, {$order['state']} {$order['pin_code']}") . '<br>'
        . mailEscape($order['country']) . '<br>' . mailEscape($order['phone']) . '<br>' . mailEscape($order['email']) . '</div>
    <div><strong>Ship to</strong><br>' . mailEscape($shippingAddress) . '</div>
  </div>
  <table>
    <thead><tr><th>Item</th><th>Qty</th><th class="amount">Unit price</th><th class="amount">Amount</th></tr></thead>
    <tbody>
      <tr><td>' . mailEscape($order['product_name']) . '</td><td>' . (int) $order['qty'] . '</td><td class="amount">' . formatMailPrice($order['unit_price']) . '</td><td class="amount">' . formatMailPrice($order['subtotal']) . '</td></tr>
      <tr><td colspan="3" class="amount">Standard shipping</td><td class="amount">' . formatMailPrice($order['shipping_fee']) . '</td></tr>
      <tr class="total"><td colspan="3" class="amount">Total</td><td class="amount">' . formatMailPrice($order['total']) . '</td></tr>
    </tbody>
  </table>
  <p class="status">Payment status: Pending</p>
  <p class="muted">This invoice was created when the order was placed. Payment is completed separately through Razorpay.</p>
</div>
</body>
</html>';
}

function pdfText(string $value): string
{
    $converted = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $value);
    $value = $converted === false ? preg_replace('/[^\x20-\x7E]/', '', $value) : $converted;

    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $value);
}

function buildInvoicePdf(array $order): string
{
    $shippingAddress = $order['ship_different']
        ? "{$order['ship_address']}, {$order['ship_city']}, {$order['ship_state']} {$order['ship_pin']}"
        : "{$order['street_address']}"
            . ($order['apartment'] !== '' ? ", {$order['apartment']}" : '')
            . ", {$order['city']}, {$order['state']} {$order['pin_code']}";

    $lines = [
        'VDESICONNECT - INVOICE',
        'Invoice: ' . $order['number'],
        'Date: ' . $order['date'],
        '',
        'Bill to: ' . $order['customer_name'],
        'Email: ' . $order['email'],
        'Phone: ' . $order['phone'],
        'Address: ' . $order['street_address'],
        $order['city'] . ', ' . $order['state'] . ' ' . $order['pin_code'],
        $order['country'],
        '',
        'Ship to: ' . $shippingAddress,
        '',
        'Item: ' . $order['product_name'],
        'Quantity: ' . (int) $order['qty'],
        'Unit price: INR ' . number_format((float) $order['unit_price'], 0),
        'Subtotal: INR ' . number_format((float) $order['subtotal'], 0),
        'Standard shipping: INR ' . number_format((float) $order['shipping_fee'], 0),
        'TOTAL: INR ' . number_format((float) $order['total'], 0),
        '',
        'Payment status: Pending',
    ];

    $content = "BT\n/F1 12 Tf\n50 790 Td\n17 TL\n";
    foreach ($lines as $line) {
        $content .= '(' . pdfText($line) . ") Tj\nT*\n";
    }
    $content .= "ET\n";

    $objects = [
        '<< /Type /Catalog /Pages 2 0 R >>',
        '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
        '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
        '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        '<< /Length ' . strlen($content) . " >>\nstream\n" . $content . 'endstream',
    ];

    $pdf = "%PDF-1.4\n";
    $offsets = [0];
    foreach ($objects as $number => $object) {
        $offsets[] = strlen($pdf);
        $pdf .= ($number + 1) . " 0 obj\n" . $object . "\nendobj\n";
    }

    $xrefOffset = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";
    foreach (array_slice($offsets, 1) as $offset) {
        $pdf .= sprintf("%010d 00000 n \n", $offset);
    }
    $pdf .= 'trailer << /Size ' . (count($objects) + 1) . " /Root 1 0 R >>\n";
    $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

    return $pdf;
}

function buildCustomerOrderEmail(array $order): string
{
    return '<!doctype html><html lang="en"><body style="margin:0;background:#f7f2ed;font-family:Arial,sans-serif;color:#2b2424">'
        . '<div style="max-width:680px;margin:auto;padding:28px 16px">'
        . '<div style="background:#7b1831;color:#fff;text-align:center;padding:28px;border-radius:14px 14px 0 0">'
        . '<div style="font-size:28px;font-weight:bold">Vdesiconnect</div><div style="margin-top:8px">Your order has been received</div></div>'
        . '<div style="background:#fff;padding:30px;border-radius:0 0 14px 14px">'
        . '<h2 style="color:#7b1831;margin-top:0">Thank you, ' . mailEscape($order['first_name']) . '!</h2>'
        . '<p>We received order <strong>' . mailEscape($order['number']) . '</strong>. Please complete the Razorpay payment in your browser so we can begin processing it.</p>'
        . '<div style="background:#faf6f2;border-left:4px solid #d6a24a;padding:16px;margin:22px 0">'
        . '<strong>' . mailEscape($order['product_name']) . '</strong> &times; ' . (int) $order['qty']
        . '<span style="float:right;font-weight:bold">' . formatMailPrice($order['total']) . '</span><br>'
        . '<span style="color:#706767;font-size:13px">Includes standard shipping</span></div>'
        . '<p>Your invoice is attached as a PDF file.</p>'
        . '<p style="margin-bottom:0">Questions? Reply to this email and include your order number.</p>'
        . '</div><p style="text-align:center;color:#817676;font-size:12px">Vdesiconnect &bull; Raksha Bandhan gifts delivered with care</p>'
        . '</div></body></html>';
}

function buildAdminOrderEmail(array $order): string
{
    $notes = $order['order_notes'] !== ''
        ? '<p><strong>Order notes:</strong><br>' . nl2br(mailEscape($order['order_notes'])) . '</p>'
        : '';

    $referral = !empty($order['referral_code'])
        ? '<p><strong>Referral code:</strong> ' . mailEscape($order['referral_code']) . '</p>'
        : '';

    return '<!doctype html><html lang="en"><body style="font-family:Arial,sans-serif;color:#2b2424">'
        . '<div style="max-width:700px;margin:auto;border:1px solid #ddd;border-radius:12px;overflow:hidden">'
        . '<div style="background:#7b1831;color:#fff;padding:22px"><h2 style="margin:0">New order ' . mailEscape($order['number']) . '</h2></div>'
        . '<div style="padding:24px"><p><strong>Customer:</strong> ' . mailEscape($order['customer_name']) . '<br>'
        . '<strong>Email:</strong> ' . mailEscape($order['email']) . '<br><strong>Phone:</strong> ' . mailEscape($order['phone']) . '</p>'
        . '<p><strong>Item:</strong> ' . mailEscape($order['product_name']) . ' &times; ' . (int) $order['qty'] . '<br>'
        . '<strong>Order total:</strong> ' . formatMailPrice($order['total']) . '<br><strong>Payment:</strong> Pending Razorpay payment</p>'
        . $referral . $notes . '<p>The full invoice with billing and shipping addresses is attached.</p></div></div>'
        . '</body></html>';
}

function smtpReadResponse($socket): array
{
    $response = '';
    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;
        if (strlen($line) < 4 || $line[3] === ' ') {
            break;
        }
    }

    return [(int) substr($response, 0, 3), trim($response)];
}

function smtpCommand($socket, string $command, array $expectedCodes): void
{
    fwrite($socket, $command . "\r\n");
    [$code, $response] = smtpReadResponse($socket);
    if (!in_array($code, $expectedCodes, true)) {
        throw new RuntimeException("SMTP command failed ({$code}): {$response}");
    }
}

function sendSmtpHtml(
    array $config,
    string $to,
    string $toName,
    string $subject,
    string $html,
    string $invoicePdf,
    string $invoiceNumber
): void {
    if ($config['smtp_password'] === '') {
        throw new RuntimeException('SMTP_PASSWORD is not configured.');
    }

    $transport = $config['smtp_encryption'] === 'ssl' ? 'ssl://' : '';
    $socket = @stream_socket_client(
        $transport . $config['smtp_host'] . ':' . $config['smtp_port'],
        $errorNumber,
        $errorMessage,
        20,
        STREAM_CLIENT_CONNECT
    );

    if (!$socket) {
        throw new RuntimeException("Unable to connect to SMTP server: {$errorMessage} ({$errorNumber})");
    }

    stream_set_timeout($socket, 20);
    try {
        [$code, $response] = smtpReadResponse($socket);
        if ($code !== 220) {
            throw new RuntimeException("SMTP connection rejected ({$code}): {$response}");
        }

        $hostname = gethostname() ?: 'localhost';
        smtpCommand($socket, 'EHLO ' . $hostname, [250]);

        if ($config['smtp_encryption'] === 'tls') {
            smtpCommand($socket, 'STARTTLS', [220]);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('Unable to start SMTP TLS encryption.');
            }
            smtpCommand($socket, 'EHLO ' . $hostname, [250]);
        }

        smtpCommand($socket, 'AUTH LOGIN', [334]);
        smtpCommand($socket, base64_encode($config['smtp_username']), [334]);
        smtpCommand($socket, base64_encode($config['smtp_password']), [235]);
        smtpCommand($socket, 'MAIL FROM:<' . $config['mail_from'] . '>', [250]);
        smtpCommand($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
        smtpCommand($socket, 'DATA', [354]);

        $boundary = '=_VDC_' . bin2hex(random_bytes(12));
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $encodedFromName = '=?UTF-8?B?' . base64_encode($config['mail_from_name']) . '?=';
        $encodedToName = '=?UTF-8?B?' . base64_encode($toName) . '?=';
        $safeInvoiceNumber = preg_replace('/[^A-Za-z0-9_-]/', '-', $invoiceNumber);

        $message = 'Date: ' . date(DATE_RFC2822) . "\r\n"
            . 'From: ' . $encodedFromName . ' <' . $config['mail_from'] . ">\r\n"
            . 'To: ' . $encodedToName . ' <' . $to . ">\r\n"
            . 'Reply-To: ' . $config['mail_from'] . "\r\n"
            . 'Subject: ' . $encodedSubject . "\r\n"
            . "MIME-Version: 1.0\r\n"
            . 'Content-Type: multipart/mixed; boundary="' . $boundary . "\"\r\n\r\n"
            . '--' . $boundary . "\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($html))
            . '--' . $boundary . "\r\n"
            . 'Content-Type: application/pdf; name="Invoice-' . $safeInvoiceNumber . ".pdf\"\r\n"
            . "Content-Transfer-Encoding: base64\r\n"
            . 'Content-Disposition: attachment; filename="Invoice-' . $safeInvoiceNumber . ".pdf\"\r\n\r\n"
            . chunk_split(base64_encode($invoicePdf))
            . '--' . $boundary . "--\r\n";

        $message = preg_replace('/(?m)^\./', '..', $message);
        fwrite($socket, $message . ".\r\n");
        [$dataCode, $dataResponse] = smtpReadResponse($socket);
        if ($dataCode !== 250) {
            throw new RuntimeException("SMTP message rejected ({$dataCode}): {$dataResponse}");
        }

        smtpCommand($socket, 'QUIT', [221]);
    } finally {
        fclose($socket);
    }
}

function sendOrderEmails(array $order): array
{
    $config = require __DIR__ . '/config.php';
    $invoice = buildInvoicePdf($order);
    $results = ['customer' => false, 'admin' => false];

    try {
        sendSmtpHtml(
            $config,
            $order['email'],
            $order['customer_name'],
            'Order received - ' . $order['number'],
            buildCustomerOrderEmail($order),
            $invoice,
            $order['number']
        );
        $results['customer'] = true;
    } catch (Throwable $error) {
        error_log('Customer order email failed for ' . $order['number'] . ': ' . $error->getMessage());
    }

    try {
        sendSmtpHtml(
            $config,
            $config['admin_email'],
            'Vdesiconnect Admin',
            'New order - ' . $order['number'],
            buildAdminOrderEmail($order),
            $invoice,
            $order['number']
        );
        $results['admin'] = true;
    } catch (Throwable $error) {
        error_log('Admin order email failed for ' . $order['number'] . ': ' . $error->getMessage());
    }

    return $results;
}

<?php
/**
 * Gmail SMTP test - CORE PHP ONLY (no PHPMailer, no composer).
 * Uses raw socket + AUTH LOGIN.
 *
 * Setup:
 *   1. Enable 2-Step Verification on the Google account.
 *   2. https://myaccount.google.com/apppasswords -> generate 16-char App Password.
 *   3. Fill the CONFIG block below.
 *
 * Run:  php gmail_smtp_test.php
 * Requires: openssl extension enabled in php.ini (extension=openssl)
 */

// ---------------- CONFIG ----------------
$GMAIL_USER   = 'rakhis@elitemart.co.in';
$APP_PASSWORD = 'jvafdetqaegttnei';   // 16 chars, spaces removed
$MAIL_TO      = 'rakhis@elitemart.co.in';
$FROM_NAME    = 'SMTP Tester';

$USE_SSL = false;   // false = STARTTLS on 587 | true = implicit SSL on 465
$DEBUG   = true;    // print full SMTP conversation
// ----------------------------------------

$host = $USE_SSL ? 'ssl://smtp.gmail.com' : 'smtp.gmail.com';
$port = $USE_SSL ? 465 : 587;

/** Read a full multiline SMTP reply */
function smtp_read($sock, $debug) {
    $data = '';
    while ($line = fgets($sock, 515)) {
        $data .= $line;
        if ($debug) echo "S: " . $line;
        // last line has a space (not '-') at position 3
        if (isset($line[3]) && $line[3] === ' ') break;
    }
    return $data;
}

/** Send a command and check the expected reply code */
function smtp_cmd($sock, $cmd, $expect, $debug, $hide = false) {
    if ($cmd !== null) {
        if ($debug) echo "C: " . ($hide ? '***hidden***' : $cmd) . "\n";
        fwrite($sock, $cmd . "\r\n");
    }
    $res  = smtp_read($sock, $debug);
    $code = (int)substr($res, 0, 3);
    if (!in_array($code, (array)$expect, true)) {
        fclose($sock);
        exit("\n❌ SMTP error. Expected " . implode('/', (array)$expect) .
             ", got:\n" . $res . "\n");
    }
    return $res;
}

echo "Connecting to {$host}:{$port} ...\n";
$errno = 0; $errstr = '';
$socket = @stream_socket_client(
    "{$host}:{$port}", $errno, $errstr, 20,
    STREAM_CLIENT_CONNECT,
    stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true]])
);

if (!$socket) {
    exit("❌ Connection failed [{$errno}]: {$errstr}\n" .
         "   Check firewall/ISP blocking of port {$port}, and that openssl is enabled.\n");
}
stream_set_timeout($socket, 20);

// Greeting
smtp_cmd($socket, null, 220, $DEBUG);

// EHLO
smtp_cmd($socket, 'EHLO localhost', 250, $DEBUG);

// STARTTLS upgrade (port 587 only)
if (!$USE_SSL) {
    smtp_cmd($socket, 'STARTTLS', 220, $DEBUG);
    if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        fclose($socket);
        exit("❌ TLS handshake failed.\n");
    }
    smtp_cmd($socket, 'EHLO localhost', 250, $DEBUG);  // must re-EHLO after TLS
}

// AUTH LOGIN
smtp_cmd($socket, 'AUTH LOGIN', 334, $DEBUG);
smtp_cmd($socket, base64_encode($GMAIL_USER), 334, $DEBUG);
$authRes = smtp_cmd($socket, base64_encode(str_replace(' ', '', $APP_PASSWORD)), 235, $DEBUG, true);

// Envelope
smtp_cmd($socket, "MAIL FROM:<{$GMAIL_USER}>", 250, $DEBUG);
smtp_cmd($socket, "RCPT TO:<{$MAIL_TO}>", [250, 251], $DEBUG);
smtp_cmd($socket, 'DATA', 354, $DEBUG);

// Message body (MIME multipart: plain + html)
$boundary = 'bnd_' . md5(uniqid((string)mt_rand(), true));
$subject  = 'SMTP Test - ' . date('Y-m-d H:i:s');

$headers = [
    "From: " . mb_encode_mimeheader($FROM_NAME) . " <{$GMAIL_USER}>",
    "To: <{$MAIL_TO}>",
    "Subject: " . mb_encode_mimeheader($subject),
    "Date: " . date('r'),
    "Message-ID: <" . uniqid('', true) . "@gmail.com>",
    "MIME-Version: 1.0",
    "Content-Type: multipart/alternative; boundary=\"{$boundary}\"",
];

$body  = implode("\r\n", $headers) . "\r\n\r\n";
$body .= "--{$boundary}\r\n";
$body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
$body .= "Plain-text body.\r\n\r\nIf you are reading this, Gmail SMTP works.\r\n\r\n";
$body .= "--{$boundary}\r\n";
$body .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
$body .= "<html><body><h3 style='color:#1a73e8'>SMTP Test Successful</h3>"
       . "<p>If you are reading this, Gmail SMTP works.</p></body></html>\r\n\r\n";
$body .= "--{$boundary}--\r\n";

// Dot-stuffing: a line starting with '.' must be escaped
$body = preg_replace('/^\./m', '..', $body);

fwrite($socket, $body . "\r\n.\r\n");
smtp_cmd($socket, null, 250, $DEBUG);

smtp_cmd($socket, 'QUIT', [221, 250], $DEBUG);
fclose($socket);

echo "\n✅ Sent to {$MAIL_TO}\n";

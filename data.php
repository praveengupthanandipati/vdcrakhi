<?php
/**
 * Single source of truth for the Rakhi catalog.
 * Product data (category, name, mrp, offer_price, description) lives in
 * products.json — edit that file to add/update/remove products.
 */

$categories = [
    'single-rakhi-india'    => 'Single Rakhi to India',
    'multiple-rakhis-india' => 'Multiple Rakhis to India',
    'rakhi-chocolate-india' => 'Rakhi with Chocolate to India',
    'rakhi-sweets-india'    => 'Rakhi with Sweets to India',
    'rakhi-nuts-india'      => 'Rakhi with Nuts to India',
    'single-rakhi-usa'      => 'Single Rakhi to Usa',
    'multiple-rakhi-usa'    => 'Multiple Rakhi to Usa',
    'rakhi-sweets-usa'      => 'Rakhi with Sweets to Usa',
];
$categorySlugsByLabel = array_flip($categories);

// Loop over every product photo found in /img and cycle through them as a
// fallback for any product that doesn't set its own "image" in products.json.
$imageFiles = glob(__DIR__ . '/img/*.{jpg,jpeg,png}', GLOB_BRACE);
$imageFiles = array_values(array_filter($imageFiles, fn($f) => !in_array(basename($f), ['logo.png', 'fav.png', 'placeholder.jpg'], true)));
natsort($imageFiles);
$productImages = array_map(fn($f) => 'img/' . basename($f), array_values($imageFiles));
if (!$productImages) {
    $productImages = ['img/placeholder.jpg'];
}

function slugifyCategory(string $label): string {
    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $label), '-'));
    return $slug !== '' ? $slug : 'general';
}

$productsRaw = json_decode(file_get_contents(__DIR__ . '/products.json'), true) ?: [];

$products = [];
$seenCategorySlugs = [];
$id = 1;
foreach ($productsRaw as $item) {
    $label = $item['category'];
    // Reuse the known slug for existing categories; auto-generate one for any
    // new category name added to products.json so it works without code changes.
    $slug = $categorySlugsByLabel[$label] ?? slugifyCategory($label);
    if (!isset($categories[$slug])) {
        $categories[$slug] = $label;
    }

    $isFirstInCategory = !isset($seenCategorySlugs[$slug]);
    $seenCategorySlugs[$slug] = true;

    $products[] = [
        'id'             => $id,
        'name'           => $item['name'],
        'category'       => $slug,
        'category_label' => $label,
        'price'          => $item['mrp'],
        'offer_price'    => $item['offer_price'],
        // 'rating'         => round((38 + (($id * 7) % 13)) / 10, 1),
        'image'          => !empty($item['image']) ? $item['image'] : $productImages[($id - 1) % count($productImages)],
        'description'    => $item['description'],
        'badge'          => $isFirstInCategory ? 'Bestseller' : (($id % 7 === 0) ? 'New' : null),
    ];
    $id++;
}

function getProductById(array $products, $id) {
    foreach ($products as $p) {
        if ((int) $p['id'] === (int) $id) return $p;
    }
    return null;
}

function formatPrice($amount) {
    return '₹' . number_format((float) $amount, 0);
}

// define('RAZORPAY_LINK', '');
// define('RAZORPAY_KEY_ID', '');
// define('RAZORPAY_KEY_SECRET', '');

define('RAZORPAY_LINK', 'https://razorpay.me/@elitemart4120');
define('RAZORPAY_KEY_ID', 'rzp_test_TFqDGMRwEytZqP');
define('RAZORPAY_KEY_SECRET', 'AJAn4LJKAQ7fHC0KVfKg3n63');

define('SHIPPING_FEE', 45);
define('DB_HOST', 'localhost');
define('DB_NAME', 'vdcrakhi');
define('DB_USER', 'vdcrakhi');
define('DB_PASS', 'vdcrakhi@2026');
define('ADMIN_EMAIL', 'info@kpits.in');
define('MAIL_FROM_EMAIL', 'rakhis@vdesiconnect.com');
define('MAIL_FROM_NAME', 'Vdesiconnect');

function createRazorpayOrder(int $orderId, string $orderNumber, float $amount): array
{
    $payload = [
        'amount' => (int) round($amount * 100),
        'currency' => 'INR',
        'receipt' => $orderNumber,
        'notes' => ['order_id' => $orderId],
    ];

    $jsonPayload = json_encode($payload);
    $ch = curl_init('https://api.razorpay.com/v1/orders');
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $error !== '') {
        throw new RuntimeException('Unable to initialize Razorpay order: ' . $error);
    }

    $decoded = json_decode($response, true);
    if (!is_array($decoded) || empty($decoded['id']) || $httpCode >= 400) {
        throw new RuntimeException('Invalid Razorpay response: ' . ($decoded['error']['description'] ?? $response));
    }

    return $decoded;
}

function verifyRazorpaySignature(string $orderId, string $paymentId, string $signature): bool
{
    $payload = $orderId . '|' . $paymentId;
    $expected = hash_hmac('sha256', $payload, RAZORPAY_KEY_SECRET);
    return hash_equals($expected, $signature);
}

function updateOrderPaymentStatus(int $orderId, string $status, ?string $paymentId = null): void
{
    $pdo = getDbConnection();
    $stmt = $pdo->prepare('UPDATE orders SET status = :status, payment_id = :payment_id WHERE id = :id');
    $stmt->execute([
        ':status' => $status,
        ':payment_id' => $paymentId,
        ':id' => $orderId,
    ]);
}

function getDbConnection(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';charset=utf8mb4';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '`');
    $pdo->exec('USE `' . DB_NAME . '`');

    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        order_number VARCHAR(50) NOT NULL UNIQUE,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        company VARCHAR(150) DEFAULT NULL,
        country VARCHAR(50) NOT NULL,
        street_address VARCHAR(255) NOT NULL,
        apartment VARCHAR(255) DEFAULT NULL,
        city VARCHAR(100) NOT NULL,
        state VARCHAR(100) NOT NULL,
        pin_code VARCHAR(20) NOT NULL,
        phone VARCHAR(30) NOT NULL,
        email VARCHAR(150) NOT NULL,
        ship_different TINYINT(1) NOT NULL DEFAULT 0,
        ship_address VARCHAR(255) DEFAULT NULL,
        ship_city VARCHAR(100) DEFAULT NULL,
        ship_state VARCHAR(100) DEFAULT NULL,
        ship_pin VARCHAR(20) DEFAULT NULL,
        order_notes TEXT DEFAULT NULL,
        referral_code VARCHAR(20) DEFAULT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'pending',
        payment_id VARCHAR(100) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    try {
        $pdo->exec("ALTER TABLE orders ADD COLUMN payment_id VARCHAR(100) DEFAULT NULL");
    } catch (Throwable $e) {
        // Ignore if the column already exists.
    }

    try {
        $pdo->exec("ALTER TABLE orders ADD COLUMN referral_code VARCHAR(20) DEFAULT NULL");
    } catch (Throwable $e) {
        // Ignore if the column already exists.
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        order_id INT UNSIGNED NOT NULL,
        product_id INT UNSIGNED NOT NULL,
        product_name VARCHAR(255) NOT NULL,
        quantity INT UNSIGNED NOT NULL,
        unit_price DECIMAL(10,2) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    return $pdo;
}

function sendOrderNotificationEmail(array $billingData, array $product, int $qty, float $subtotal, float $total, int $orderId): bool
{
    $customerEmail = trim($billingData['email'] ?? '');
    $adminEmail = defined('ADMIN_EMAIL') ? constant('ADMIN_EMAIL') : 'admin@vdcrakhi.com';
    $fromEmail = defined('MAIL_FROM_EMAIL') ? constant('MAIL_FROM_EMAIL') : 'no-reply@vdcrakhi.com';
    $fromName = defined('MAIL_FROM_NAME') ? constant('MAIL_FROM_NAME') : 'Vdesiconnect';

    if ($customerEmail === '') {
        return false;
    }

    $customerSubject = 'Your order has been received - Order #' . $orderId;
    $customerMessage = "Hello {$billingData['first_name']} {$billingData['last_name']},\n\n";
    $customerMessage .= "Thank you for your order with Vdesiconnect.\n";
    $customerMessage .= "Order ID: {$orderId}\n";
    $customerMessage .= "Product: {$product['name']}\n";
    $customerMessage .= "Quantity: {$qty}\n";
    $customerMessage .= "Subtotal: ₹" . number_format($subtotal, 0) . "\n";
    $customerMessage .= "Shipping: ₹" . number_format(SHIPPING_FEE, 0) . "\n";
    $customerMessage .= "Total: ₹" . number_format($total, 0) . "\n\n";
    $customerMessage .= "We will contact you shortly with the delivery details.\n";

    $adminSubject = 'New order received - Order #' . $orderId;
    $adminMessage = "New order received on Vdesiconnect.\n\n";
    $adminMessage .= "Customer: {$billingData['first_name']} {$billingData['last_name']}\n";
    $adminMessage .= "Email: {$customerEmail}\n";
    $adminMessage .= "Phone: {$billingData['phone']}\n";
    $adminMessage .= "Product: {$product['name']}\n";
    $adminMessage .= "Quantity: {$qty}\n";
    $adminMessage .= "Total: ₹" . number_format($total, 0) . "\n";

    $headers = "From: {$fromName} <{$fromEmail}>\r\n";
    $headers .= "Reply-To: {$fromEmail}\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    $customerSent = @mail($customerEmail, $customerSubject, $customerMessage, $headers);
    $adminSent = @mail($adminEmail, $adminSubject, $adminMessage, $headers);

    return $customerSent || $adminSent;
}

function saveOrderToDatabase(array $billingData, array $product, int $qty, float $subtotal, float $total): array
{
    $pdo = getDbConnection();
    $orderNumber = 'ORD-' . date('YmdHis') . '-' . random_int(1000, 9999);

    $stmt = $pdo->prepare("INSERT INTO orders (
        order_number, first_name, last_name, company, country, street_address, apartment, city, state, pin_code, phone, email,
        ship_different, ship_address, ship_city, ship_state, ship_pin, order_notes, referral_code, total_amount
    ) VALUES (
        :order_number, :first_name, :last_name, :company, :country, :street_address, :apartment, :city, :state, :pin_code, :phone, :email,
        :ship_different, :ship_address, :ship_city, :ship_state, :ship_pin, :order_notes, :referral_code, :total_amount
    )");

    $stmt->execute([
        ':order_number' => $orderNumber,
        ':first_name' => $billingData['first_name'],
        ':last_name' => $billingData['last_name'],
        ':company' => $billingData['company'],
        ':country' => $billingData['country'],
        ':street_address' => $billingData['street_address'],
        ':apartment' => $billingData['apartment'],
        ':city' => $billingData['city'],
        ':state' => $billingData['state'],
        ':pin_code' => $billingData['pin_code'],
        ':phone' => $billingData['phone'],
        ':email' => $billingData['email'],
        ':ship_different' => $billingData['ship_different'] ? 1 : 0,
        ':ship_address' => $billingData['ship_different'] ? $billingData['ship_address'] : null,
        ':ship_city' => $billingData['ship_different'] ? $billingData['ship_city'] : null,
        ':ship_state' => $billingData['ship_different'] ? $billingData['ship_state'] : null,
        ':ship_pin' => $billingData['ship_different'] ? $billingData['ship_pin'] : null,
        ':order_notes' => $billingData['order_notes'],
        ':referral_code' => $billingData['referral_code'] !== '' ? $billingData['referral_code'] : null,
        ':total_amount' => $total,
    ]);

    $orderId = (int) $pdo->lastInsertId();

    $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, subtotal) VALUES (:order_id, :product_id, :product_name, :quantity, :unit_price, :subtotal)");
    $itemStmt->execute([
        ':order_id' => $orderId,
        ':product_id' => (int) $product['id'],
        ':product_name' => $product['name'],
        ':quantity' => $qty,
        ':unit_price' => (float) $product['offer_price'],
        ':subtotal' => $subtotal,
    ]);

    return [
        'id' => $orderId,
        'order_number' => $orderNumber,
    ];
}

<?php
/**
 * Single source of truth for the Rakhi catalog.
 * Generates 30 sample products across 6 categories with test images.
 */

$categories = [
    'traditional'   => 'Traditional Rakhis',
    'kids'          => 'Kids Rakhis',
    'premium'       => 'Premium & Designer Rakhis',
    'eco'           => 'Eco-Friendly Rakhis',
    'return-gifts'  => 'Return Gift Sets',
    'combo'         => 'Combo Packs (Rakhi + Sweets)',
];

// Loop over every rakhi*.jpg found in /img and cycle through them for the catalog
$imageFiles = glob(__DIR__ . '/img/rakhi*.jpg');
natsort($imageFiles);
$productImages = array_map(fn($f) => 'img/' . basename($f), array_values($imageFiles));

$names = [
    'traditional'  => ['Royal Silk Rakhi', 'Classic Mauli Thread Rakhi', 'Traditional Kalash Rakhi', 'Zardosi Handcrafted Rakhi', 'Banarasi Silk Rakhi'],
    'kids'         => ['Cartoon Character Rakhi', 'Chota Hero Kids Rakhi', 'Superhero Rakhi for Kids', 'Robo Toon Fun Rakhi', 'Cute Teddy Rakhi'],
    'premium'      => ['Gold Plated Designer Rakhi', 'Kundan Stone Rakhi', 'Pearl Studded Premium Rakhi', 'American Diamond Rakhi', 'Meenakari Designer Rakhi'],
    'eco'          => ['Rudraksha Eco Rakhi', 'Tulsi Bead Eco Rakhi', 'Sandalwood Eco Rakhi', 'Seed Plantable Rakhi', 'Jute Handmade Rakhi'],
    'return-gifts' => ['Chocolate Return Gift Set', 'Scented Candle Gift Set', 'Photo Frame Return Gift', 'Keychain Combo Gift Set', 'Mug & Card Gift Set'],
    'combo'        => ['Rakhi with Kaju Katli', 'Rakhi with Soan Papdi', 'Rakhi with Dry Fruits Box', 'Rakhi with Chocolate Box', 'Rakhi with Motichoor Ladoo'],
];

$products = [];
$id = 1;
foreach ($names as $catKey => $items) {
    foreach ($items as $i => $name) {
        $price  = 499 + (($id * 37) % 1500);
        $offer  = (int) round($price * (0.7 + (($id * 3) % 16) / 100));

        $products[] = [
            'id'             => $id,
            'name'           => $name,
            'category'       => $catKey,
            'category_label' => $categories[$catKey],
            'price'          => $price,
            'offer_price'    => $offer,
            'rating'         => round((38 + (($id * 7) % 13)) / 10, 1),
            'image'          => $productImages[($id - 1) % count($productImages)],
            'description'    => "A beautiful {$name}, handpicked for Raksha Bandhan celebrations. Crafted with love to express the special bond between brother and sister.",
            'badge'          => $i === 0 ? 'Bestseller' : (($id % 7 === 0) ? 'New' : null),
        ];
        $id++;
    }
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

define('RAZORPAY_LINK', 'https://razorpay.me/@elitemart4120');
define('RAZORPAY_KEY_ID', 'rzp_test_TFqDGMRwEytZqP');
define('RAZORPAY_KEY_SECRET', 'AJAn4LJKAQ7fHC0KVfKg3n63');
define('SHIPPING_FEE', 45);
define('DB_HOST', 'localhost');
define('DB_NAME', 'vdcrakhi');
define('DB_USER', 'root');
define('DB_PASS', '');
define('ADMIN_EMAIL', 'admin@vdcrakhi.com');
define('MAIL_FROM_EMAIL', 'no-reply@vdcrakhi.com');
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
        ship_different, ship_address, ship_city, ship_state, ship_pin, order_notes, total_amount
    ) VALUES (
        :order_number, :first_name, :last_name, :company, :country, :street_address, :apartment, :city, :state, :pin_code, :phone, :email,
        :ship_different, :ship_address, :ship_city, :ship_state, :ship_pin, :order_notes, :total_amount
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

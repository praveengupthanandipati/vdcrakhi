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
define('SHIPPING_FEE', 45);

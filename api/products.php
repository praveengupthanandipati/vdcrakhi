<?php
header('Content-Type: application/json');
require __DIR__ . '/../data.php';
echo json_encode(array_values($products));

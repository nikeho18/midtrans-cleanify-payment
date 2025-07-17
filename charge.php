<?php
require 'vendor/autoload.php';

use Midtrans\Config;
use Midtrans\Snap;

// Load .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Ambil server key dari environment
Config::$serverKey = $_ENV['MIDTRANS_SERVER_KEY'];
Config::$isProduction = true;
Config::$isSanitized = true;
Config::$is3ds = true;

// Ambil data dari Android
$raw_data = file_get_contents("php://input");
$data = json_decode($raw_data, true);

// Validasi
if (!$data || !isset($data['order_id']) || !isset($data['gross_amount']) || !isset($data['name']) || !isset($data['email']) || !isset($data['phone'])) {
    echo json_encode(['status' => false, 'message' => 'Invalid input']);
    exit;
}

// Buat parameter Snap
$params = [
    'transaction_details' => [
        'order_id' => $data['order_id'],
        'gross_amount' => (int)$data['gross_amount'],
    ],
    'customer_details' => [
        'first_name' => $data['name'],
        'email' => $data['email'],
        'phone' => $data['phone'],
    ],
    'enabled_payments' => ['gopay', 'shopeepay', 'bank_transfer', 'credit_card'],
];

// Buat Snap Token
try {
    $snapToken = Snap::getSnapToken($params);
    echo json_encode(['status' => true, 'snapToken' => $snapToken]);
} catch (Exception $e) {
    echo json_encode(['status' => false, 'message' => $e->getMessage()]);
}

<?php
// api/orders.php
// Müşterinin son siparişlerini n8n webhook üzerinden sorgular
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_utils.php';

$userId = verifyTokenAndGetUser();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$phone = $_GET['phone'] ?? '';

if (empty($phone)) {
    http_response_code(400);
    echo json_encode(['error' => 'phone parametresi zorunludur']);
    exit;
}

$phone = preg_replace('/[^0-9]/', '', $phone);

// n8n webhook URL — environment variable'dan al
$n8nBase = getenv('N8N_BASE_URL') ?: 'https://n8n.motomotomasyon.com';
$url = rtrim($n8nBase, '/') . '/webhook/get-orders';

$payload = json_encode(['phone' => $phone]);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error    = curl_error($ch);
curl_close($ch);

if ($error) {
    echo json_encode([]);
    exit;
}

$data = json_decode($response, true);

if ($httpCode >= 200 && $httpCode < 300 && is_array($data)) {
    // Sipariş verilerini formatla
    $formatted = [];
    foreach ($data as $o) {
        $formatted[] = [
            'order_id' => $o['shopify_order_id'] ?? null,
            'customer' => $o['customer_name'] ?? null,
            'total'    => $o['total_price'] ?? null,
            'payment'  => $o['payment_status'] ?? null,
            'status'   => $o['status'] ?? null,
            'address'  => $o['shipping_address'] ?? null,
            'date'     => $o['created_at'] ?? null
        ];
    }
    echo json_encode($formatted);
} else {
    echo json_encode([]);
}

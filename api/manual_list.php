<?php
// api/manual_list.php
// Manuel yanıt listesine numara ekleme/çıkarma (n8n webhook proxy)
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_utils.php';

// Yetkilendirme Kontrolü
$userId = verifyTokenAndGetUser();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$phone = $input['phone'] ?? '';
$action = $input['action'] ?? 'add'; // 'add' veya 'remove'

if (empty($phone)) {
    http_response_code(400);
    echo json_encode(['error' => 'phone parametresi zorunludur']);
    exit;
}

$phone = preg_replace('/[^0-9]/', '', $phone);

// n8n webhook URL — environment variable'dan al
$n8nBase = getenv('N8N_BASE_URL') ?: 'https://n8n.motomotomasyon.com';

if ($action === 'add') {
    $n8nUrl = rtrim($n8nBase, '/') . '/webhook/add-manual-list';
} elseif ($action === 'remove') {
    $n8nUrl = rtrim($n8nBase, '/') . '/webhook/remove-manual-list';
} else {
    http_response_code(400);
    echo json_encode(['error' => 'action "add" veya "remove" olmalıdır']);
    exit;
}

$payload = json_encode(['phone' => $phone]);

$ch = curl_init($n8nUrl);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
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
    http_response_code(502);
    echo json_encode(['error' => "n8n bağlantı hatası: {$error}"]);
    exit;
}

$data = json_decode($response, true);

if ($httpCode >= 200 && $httpCode < 300) {
    echo json_encode([
        'success' => true,
        'message' => $action === 'add' ? 'Numara manuel listeye eklendi' : 'Numara manuel listeden çıkarıldı',
        'phone'   => $phone,
        'action'  => $action
    ]);
} else {
    http_response_code($httpCode ?: 500);
    echo json_encode([
        'error'   => true,
        'message' => $data['message'] ?? 'n8n hatası'
    ]);
}

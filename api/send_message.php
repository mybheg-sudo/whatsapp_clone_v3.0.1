<?php
// api/send_message.php
// Web panelden yazılan mesajı n8n webhook üzerinden WhatsApp ile gönderir
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_utils.php';

// Yetkilendirme Kontrolü
verifyTokenAndGetUser();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$phone = $input['phone'] ?? '';
$message = $input['message'] ?? '';
$contactId = $input['contact_id'] ?? '';

// Validasyon
if (empty($phone) || empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'phone ve message parametreleri zorunludur']);
    exit;
}

// Telefon numarasını temizle
$phone = preg_replace('/[^0-9]/', '', $phone);

// n8n webhook URL — environment variable'dan al
$n8nBase = getenv('N8N_BASE_URL') ?: 'https://n8n.motomotomasyon.com';
$n8nUrl = rtrim($n8nBase, '/') . '/webhook/send-whatsapp';

$payload = json_encode([
    'phone'      => $phone,
    'message'    => $message,
    'contact_id' => $contactId
]);

$ch = curl_init($n8nUrl);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 30,
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

// Giden mesajı PostgreSQL'e logla
if ($pdo && !empty($contactId)) {
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO messages (contact_id, direction, type, content, timestamp) 
             VALUES (?, 'outgoing', 'text', ?, NOW())"
        );
        $stmt->execute([$contactId, $message]);
    } catch (\PDOException $e) {
        error_log("Giden mesaj log hatası: " . $e->getMessage());
    }
}

$data = json_decode($response, true);

if ($httpCode >= 200 && $httpCode < 300) {
    echo json_encode([
        'success' => true,
        'message' => 'Mesaj gönderildi',
        'phone'   => $phone
    ]);
} else {
    http_response_code($httpCode ?: 500);
    echo json_encode([
        'error'   => true,
        'message' => $data['message'] ?? 'n8n hatası',
        'details' => $data
    ]);
}

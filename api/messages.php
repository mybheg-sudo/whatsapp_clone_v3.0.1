<?php
// api/messages.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_utils.php';

// Yetkilendirme Kontrolü
$userId = verifyTokenAndGetUser();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// JS Frontend'den contact_id parametresi alıyoruz
$contactId = $_GET['contact_id'] ?? '';

if (empty($contactId)) {
    http_response_code(400);
    echo json_encode(['error' => 'contact_id parameter required']);
    exit;
}

try {
    // API Dokümanına uygun olarak belirtilen ID'li kişinin mesajlarını getir
    // (Aynı zamanda $userId ile yetki/izolasyon kısıtlaması eklenmiştir)
    $stmt = $pdo->prepare("SELECT id, direction, type, content, timestamp FROM messages WHERE user_id = ? AND contact_id = ? ORDER BY timestamp ASC");
    $stmt->execute([$userId, $contactId]);
    $messages = $stmt->fetchAll();

    $formattedMessages = [];
    foreach ($messages as $m) {
        $formattedMessages[] = [
            'id' => $m['id'],
            'direction' => $m['direction'], // incoming veya outgoing
            'type' => $m['type'] ?? 'text',
            'content' => $m['content'],
            'timestamp' => $m['timestamp'] // 2023-10-25T14:20:00.000Z formatında
        ];
    }

    echo json_encode($formattedMessages);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => 'DB Hatası: ' . $e->getMessage()]);
}

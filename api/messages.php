<?php
// api/messages.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_utils.php';

// Yetkilendirme Kontrolü
verifyTokenAndGetUser();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$contactId = $_GET['contact_id'] ?? '';

if (empty($contactId)) {
    http_response_code(400);
    echo json_encode(['error' => 'contact_id parameter required']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, direction, type, content, timestamp FROM messages WHERE contact_id = ? ORDER BY timestamp ASC");
    $stmt->execute([$contactId]);
    $messages = $stmt->fetchAll();

    $formattedMessages = [];
    foreach ($messages as $m) {
        $formattedMessages[] = [
            'id' => $m['id'],
            'direction' => $m['direction'],
            'type' => $m['type'] ?? 'text',
            'content' => $m['content'],
            'timestamp' => $m['timestamp']
        ];
    }

    echo json_encode($formattedMessages);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => 'DB Hatası: ' . $e->getMessage()]);
}

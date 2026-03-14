<?php
// api/sse.php — Server-Sent Events endpoint for real-time messages
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_utils.php';

// Yetkilendirme — token query param olarak da kabul edilir (EventSource header göndermez)
if (isset($_GET['token'])) {
    $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $_GET['token'];
}
$userId = verifyTokenAndGetUser();

$contactId = $_GET['contact_id'] ?? '';
$lastId = intval($_GET['last_id'] ?? 0);

if (empty($contactId)) {
    http_response_code(400);
    echo "data: " . json_encode(['error' => 'contact_id required']) . "\n\n";
    exit;
}

// SSE headers
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');

// Flush function
function sseFlush() {
    if (ob_get_level() > 0) ob_flush();
    flush();
}

// Keep connection alive for max 30 seconds, check every 3 seconds
$maxTime = 30;
$startTime = time();
$interval = 3;

while ((time() - $startTime) < $maxTime) {
    try {
        $stmt = $pdo->prepare(
            "SELECT id, direction, type, content, timestamp 
             FROM messages 
             WHERE user_id = ? AND contact_id = ? AND id > ?
             ORDER BY timestamp ASC"
        );
        $stmt->execute([$userId, $contactId, $lastId]);
        $newMessages = $stmt->fetchAll();

        if (!empty($newMessages)) {
            foreach ($newMessages as $msg) {
                echo "data: " . json_encode($msg) . "\n\n";
                $lastId = max($lastId, intval($msg['id']));
            }
            sseFlush();
        }

        // Heartbeat to keep connection alive
        echo ": heartbeat\n\n";
        sseFlush();

    } catch (\PDOException $e) {
        echo "data: " . json_encode(['error' => 'DB error']) . "\n\n";
        sseFlush();
        break;
    }

    // Check if client disconnected
    if (connection_aborted()) break;

    sleep($interval);
}

echo "event: timeout\ndata: reconnect\n\n";
sseFlush();

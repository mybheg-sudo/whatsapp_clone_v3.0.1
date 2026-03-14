<?php
// api/stats.php
// İstatistik verileri — PostgreSQL messages tablosundan
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_utils.php';

verifyTokenAndGetUser();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$stats = [
    'today'        => ['sent' => 0, 'received' => 0, 'total' => 0],
    'week'         => ['sent' => 0, 'received' => 0, 'total' => 0],
    'month'        => ['sent' => 0, 'received' => 0, 'total' => 0],
    'total'        => ['contacts' => 0, 'messages' => 0],
    'top_contacts' => [],
    'recent_activity' => []
];

if (!$pdo) {
    echo json_encode($stats);
    exit;
}

try {
    // Bugünkü mesajlar
    $stmt = $pdo->query("
        SELECT 
            SUM(CASE WHEN direction = 'outgoing' THEN 1 ELSE 0 END) as sent,
            SUM(CASE WHEN direction = 'incoming' THEN 1 ELSE 0 END) as received,
            COUNT(*) as total
        FROM messages 
        WHERE DATE(timestamp) = CURRENT_DATE
    ");
    $today = $stmt->fetch();
    $stats['today'] = [
        'sent'     => (int)($today['sent'] ?? 0),
        'received' => (int)($today['received'] ?? 0),
        'total'    => (int)($today['total'] ?? 0)
    ];

    // Bu hafta
    $stmt = $pdo->query("
        SELECT 
            SUM(CASE WHEN direction = 'outgoing' THEN 1 ELSE 0 END) as sent,
            SUM(CASE WHEN direction = 'incoming' THEN 1 ELSE 0 END) as received,
            COUNT(*) as total
        FROM messages 
        WHERE timestamp >= CURRENT_DATE - INTERVAL '7 days'
    ");
    $week = $stmt->fetch();
    $stats['week'] = [
        'sent'     => (int)($week['sent'] ?? 0),
        'received' => (int)($week['received'] ?? 0),
        'total'    => (int)($week['total'] ?? 0)
    ];

    // Bu ay
    $stmt = $pdo->query("
        SELECT 
            SUM(CASE WHEN direction = 'outgoing' THEN 1 ELSE 0 END) as sent,
            SUM(CASE WHEN direction = 'incoming' THEN 1 ELSE 0 END) as received,
            COUNT(*) as total
        FROM messages 
        WHERE timestamp >= CURRENT_DATE - INTERVAL '30 days'
    ");
    $month = $stmt->fetch();
    $stats['month'] = [
        'sent'     => (int)($month['sent'] ?? 0),
        'received' => (int)($month['received'] ?? 0),
        'total'    => (int)($month['total'] ?? 0)
    ];

    // Toplam
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM contacts");
    $stats['total']['contacts'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM messages");
    $stats['total']['messages'] = (int)$stmt->fetchColumn();

    // En aktif 5 kişi
    $stmt = $pdo->query("
        SELECT c.name, c.phone, COUNT(m.id) as msg_count
        FROM contacts c
        JOIN messages m ON c.id = m.contact_id
        GROUP BY c.id, c.name, c.phone
        ORDER BY msg_count DESC
        LIMIT 5
    ");
    $stats['top_contacts'] = $stmt->fetchAll();

    // Son 7 günlük günlük mesaj sayısı (grafik için)
    $stmt = $pdo->query("
        SELECT 
            DATE(timestamp) as date,
            SUM(CASE WHEN direction = 'outgoing' THEN 1 ELSE 0 END) as sent,
            SUM(CASE WHEN direction = 'incoming' THEN 1 ELSE 0 END) as received
        FROM messages 
        WHERE timestamp >= CURRENT_DATE - INTERVAL '7 days'
        GROUP BY DATE(timestamp)
        ORDER BY date ASC
    ");
    $stats['recent_activity'] = $stmt->fetchAll();

} catch (\PDOException $e) {
    error_log("Stats error: " . $e->getMessage());
}

echo json_encode($stats);

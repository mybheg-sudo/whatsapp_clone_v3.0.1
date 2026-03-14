<?php
// api/contacts.php
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

try {
    $searchTerm = $_GET['search'] ?? '';
    
    // Kontakları ve her kontak için en son mesajı getiren JOIN sorgusu
    $query = "
        SELECT 
            c.id, 
            c.phone, 
            c.name as customer_name,
            m.content as last_message,
            m.type as last_message_type,
            m.timestamp as last_message_time
        FROM contacts c
        LEFT JOIN (
            SELECT contact_id, MAX(timestamp) as max_time
            FROM messages
            WHERE user_id = ?
            GROUP BY contact_id
        ) latest_msg ON c.id = latest_msg.contact_id
        LEFT JOIN messages m ON latest_msg.contact_id = m.contact_id AND latest_msg.max_time = m.timestamp
        WHERE c.user_id = ?
    ";
    
    $params = [$userId, $userId];
    
    if (!empty($searchTerm)) {
        $query .= " AND (c.name LIKE ? OR c.phone LIKE ?)";
        $searchWild = '%' . $searchTerm . '%';
        $params[] = $searchWild;
        $params[] = $searchWild;
    }
    
    $query .= " ORDER BY m.timestamp DESC, c.id DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $contacts = $stmt->fetchAll();
    
    // API dökümanıyla birebir eşleşen format oluştur
    $formattedOptions = [];
    foreach ($contacts as $c) {
        $formattedOptions[] = [
            'id' => $c['id'],
            'phone' => $c['phone'],
            'name' => $c['customer_name'],
            'last_message' => $c['last_message'],
            'last_message_type' => $c['last_message_type'] ?? 'text',
            'last_message_time' => $c['last_message_time']
        ];
    }
    
    echo json_encode($formattedOptions);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
}

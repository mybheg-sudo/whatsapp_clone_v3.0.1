<?php
require_once 'config.php';

try {
    $stmt = $pdo->prepare("SELECT id, direction, type, content, timestamp FROM messages WHERE user_id = ? AND contact_id = ? ORDER BY timestamp ASC LIMIT 5");
    $stmt->execute([1, 5]);
    $messages = $stmt->fetchAll();
    print_r($messages);
} catch (\PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}

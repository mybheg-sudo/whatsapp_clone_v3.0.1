<?php
// Geçici debug — messages tablo yapısını göster
require_once __DIR__ . '/../config.php';
header('Content-Type: text/plain; charset=utf-8');

if (!$pdo) { die("DB bağlantısı yok\n"); }

echo "=== MESSAGES TABLO YAPISI ===\n";
$cols = $pdo->query("DESCRIBE messages")->fetchAll();
foreach ($cols as $c) {
    echo $c['Field'] . " | " . $c['Type'] . " | " . $c['Null'] . " | " . $c['Key'] . " | " . $c['Default'] . "\n";
}

echo "\n=== SON 5 MESAJ ===\n";
$msgs = $pdo->query("SELECT * FROM messages ORDER BY id DESC LIMIT 5")->fetchAll();
foreach ($msgs as $m) {
    echo json_encode($m) . "\n";
}

echo "\n=== CONTACTS TABLO YAPISI ===\n";
$cols = $pdo->query("DESCRIBE contacts")->fetchAll();
foreach ($cols as $c) {
    echo $c['Field'] . " | " . $c['Type'] . " | " . $c['Null'] . " | " . $c['Key'] . " | " . $c['Default'] . "\n";
}

echo "\n=== TOPLAM KAYIT SAYILARI ===\n";
$mc = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
$cc = $pdo->query("SELECT COUNT(*) FROM contacts")->fetchColumn();
echo "Messages: $mc\n";
echo "Contacts: $cc\n";

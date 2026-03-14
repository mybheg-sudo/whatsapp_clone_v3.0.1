<?php
/**
 * MYBHEG Kurumsal İletişim Paneli - Yerel Veritabanı Kurulum Dosyası (SQLite)
 * Bu dosya MySQL gerektirmeden çalışan taşınabilir veritabanını oluşturur.
 */

$sqlite_path = __DIR__ . '/database.sqlite';

try {
    $pdo = new PDO("sqlite:$sqlite_path");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // users tablosu
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL
    )");

    // conversations tablosu
    $pdo->exec("CREATE TABLE IF NOT EXISTS conversations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        phone VARCHAR(20) NOT NULL UNIQUE,
        customer_name VARCHAR(100),
        tags VARCHAR(255)
    )");

    // messages tablosu
    $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        conv_id INTEGER,
        wa_id VARCHAR(100),
        sender_phone VARCHAR(20),
        message_text TEXT,
        status VARCHAR(20),
        manual_override INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Örnek verileri ekle
    $stmt = $pdo->query("SELECT COUNT(*) FROM conversations");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO conversations (phone, customer_name, tags) VALUES 
            ('+905551234567', 'Ahmet Yılmaz', 'Şikayet, VIP'),
            ('+905449876543', 'Ayşe Demir', 'Sipariş'),
            ('+905321112233', '+90 532 111 22 33', 'Yeni'),
            ('+905059998877', 'Mehmet Çelik', 'Destek')");

        $pdo->exec("INSERT INTO messages (conv_id, sender_phone, message_text, status) VALUES 
            (1, '+905551234567', 'Merhaba, siparişim hala kargoya verilmedi.', 'received'),
            (1, 'SYSTEM', 'Merhaba Ahmet Bey, hemen kontrol ediyorum.', 'sent'),
            (1, '+905551234567', 'Kargom nerede kaldı?', 'received')");
    }

    echo "SQLite veritabanı başarıyla kuruldu ve örnek veriler eklendi!";

} catch (PDOException $e) {
    die("Kurulum Hatası: " . $e->getMessage());
}
?>
<?php
/**
 * Tek Kullanıcı Dönüşümü — Veritabanı Migration
 * 
 * Bu script:
 * 1. users tablosunda sadece 1 kullanıcı bırakır (id=1)
 * 2. contacts tablosundan user_id sütununu kaldırır
 * 3. messages tablosundan user_id sütununu kaldırır
 * 
 * Kullanım: Browser'da /database/migrate_single_user.php açın
 * GÜVENLİK: Migration sonrası bu dosyayı silin!
 */

require_once __DIR__ . '/../config.php';

// Sadece CLI veya doğrudan erişimle çalıştırılabilir
header('Content-Type: text/plain; charset=utf-8');

if (!$pdo) {
    die("HATA: Veritabanı bağlantısı kurulamadı.\n");
}

$results = [];

try {
    $pdo->beginTransaction();

    // 1. Users tablosundaki fazla kullanıcıları temizle (id=1 hariç)
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $userCount = $stmt->fetchColumn();
    $results[] = "Mevcut kullanıcı sayısı: $userCount";

    if ($userCount > 1) {
        $pdo->exec("DELETE FROM users WHERE id != 1");
        $results[] = "✅ Fazla kullanıcılar silindi (id=1 korundu)";
    } else {
        $results[] = "ℹ️ Zaten tek kullanıcı var, silme gerekmedi";
    }

    // 2. contacts tablosundan user_id sütununu kaldır
    $stmt = $pdo->query("SHOW COLUMNS FROM contacts LIKE 'user_id'");
    if ($stmt->rowCount() > 0) {
        // Önce index varsa kaldır
        try {
            $pdo->exec("ALTER TABLE contacts DROP INDEX idx_contacts_user_id");
        } catch (\PDOException $e) {
            // Index yoksa sorun değil
        }
        $pdo->exec("ALTER TABLE contacts DROP COLUMN user_id");
        $results[] = "✅ contacts.user_id sütunu kaldırıldı";
    } else {
        $results[] = "ℹ️ contacts tablosunda user_id zaten yok";
    }

    // 3. messages tablosundan user_id sütununu kaldır
    $stmt = $pdo->query("SHOW COLUMNS FROM messages LIKE 'user_id'");
    if ($stmt->rowCount() > 0) {
        try {
            $pdo->exec("ALTER TABLE messages DROP INDEX idx_messages_user_id");
        } catch (\PDOException $e) {
            // Index yoksa sorun değil
        }
        $pdo->exec("ALTER TABLE messages DROP COLUMN user_id");
        $results[] = "✅ messages.user_id sütunu kaldırıldı";
    } else {
        $results[] = "ℹ️ messages tablosunda user_id zaten yok";
    }

    $pdo->commit();
    $results[] = "\n=== Migration başarıyla tamamlandı! ===";
    $results[] = "⚠️ GÜVENLİK: Bu dosyayı production'dan silin!";

} catch (\PDOException $e) {
    $pdo->rollBack();
    $results[] = "❌ HATA: " . $e->getMessage();
    $results[] = "Transaction geri alındı, hiçbir değişiklik yapılmadı.";
}

echo implode("\n", $results) . "\n";

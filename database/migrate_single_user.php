<?php
/**
 * Tek Kullanıcı Dönüşümü — Veritabanı Migration
 * 
 * Bu script:
 * 1. users tablosunda sadece 1 kullanıcı bırakır (id=1)
 * 2. Foreign key constraint'leri kaldırır
 * 3. contacts tablosundan user_id sütununu kaldırır
 * 4. messages tablosundan user_id sütununu kaldırır
 * 
 * Kullanım: Browser'da /database/migrate_single_user.php açın
 * GÜVENLİK: Migration sonrası bu dosyayı silin!
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: text/plain; charset=utf-8');

if (!$pdo) {
    die("HATA: Veritabanı bağlantısı kurulamadı.\n");
}

$results = [];

try {
    // FK check'leri geçici olarak kapat
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // 1. Users tablosundaki fazla kullanıcıları temizle (id=1 hariç)
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $userCount = $stmt->fetchColumn();
    $results[] = "Mevcut kullanıcı sayısı: $userCount";

    if ($userCount > 1) {
        $pdo->exec("DELETE FROM users WHERE id != 1");
        $results[] = "✅ Fazla kullanıcılar silindi (id=1 korundu)";
    } else {
        $results[] = "ℹ️ Zaten tek kullanıcı var";
    }

    // 2. contacts tablosundan FK + user_id kaldır
    $stmt = $pdo->query("SHOW COLUMNS FROM contacts LIKE 'user_id'");
    if ($stmt->rowCount() > 0) {
        // Tüm FK constraint'leri bul ve kaldır
        $fks = $pdo->query("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = 'contacts' 
              AND COLUMN_NAME = 'user_id' 
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ")->fetchAll();
        
        foreach ($fks as $fk) {
            $fkName = $fk['CONSTRAINT_NAME'];
            $pdo->exec("ALTER TABLE contacts DROP FOREIGN KEY `$fkName`");
            $results[] = "✅ contacts FK kaldırıldı: $fkName";
        }

        // Index'leri kaldır
        $indexes = $pdo->query("SHOW INDEX FROM contacts WHERE Column_name = 'user_id'")->fetchAll();
        foreach ($indexes as $idx) {
            $idxName = $idx['Key_name'];
            try {
                $pdo->exec("ALTER TABLE contacts DROP INDEX `$idxName`");
                $results[] = "✅ contacts index kaldırıldı: $idxName";
            } catch (\PDOException $e) {
                // Index zaten silinmiş olabilir
            }
        }

        $pdo->exec("ALTER TABLE contacts DROP COLUMN user_id");
        $results[] = "✅ contacts.user_id sütunu kaldırıldı";
    } else {
        $results[] = "ℹ️ contacts tablosunda user_id zaten yok";
    }

    // 3. messages tablosundan FK + user_id kaldır
    $stmt = $pdo->query("SHOW COLUMNS FROM messages LIKE 'user_id'");
    if ($stmt->rowCount() > 0) {
        $fks = $pdo->query("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = 'messages' 
              AND COLUMN_NAME = 'user_id' 
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ")->fetchAll();
        
        foreach ($fks as $fk) {
            $fkName = $fk['CONSTRAINT_NAME'];
            $pdo->exec("ALTER TABLE messages DROP FOREIGN KEY `$fkName`");
            $results[] = "✅ messages FK kaldırıldı: $fkName";
        }

        $indexes = $pdo->query("SHOW INDEX FROM messages WHERE Column_name = 'user_id'")->fetchAll();
        foreach ($indexes as $idx) {
            $idxName = $idx['Key_name'];
            try {
                $pdo->exec("ALTER TABLE messages DROP INDEX `$idxName`");
                $results[] = "✅ messages index kaldırıldı: $idxName";
            } catch (\PDOException $e) {
                // Index zaten silinmiş olabilir
            }
        }

        $pdo->exec("ALTER TABLE messages DROP COLUMN user_id");
        $results[] = "✅ messages.user_id sütunu kaldırıldı";
    } else {
        $results[] = "ℹ️ messages tablosunda user_id zaten yok";
    }

    // FK check'leri tekrar aç
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    $results[] = "\n=== Migration başarıyla tamamlandı! ===";
    $results[] = "⚠️ GÜVENLİK: Bu dosyayı production'dan silin!";

} catch (\PDOException $e) {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    $results[] = "❌ HATA: " . $e->getMessage();
}

echo implode("\n", $results) . "\n";

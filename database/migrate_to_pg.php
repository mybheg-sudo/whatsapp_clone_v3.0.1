<?php
/**
 * MySQL → PostgreSQL Migration Script
 * 
 * Bu script production'da çalıştırılacak:
 * 1. MySQL'den tüm verileri okur
 * 2. PostgreSQL'de tabloları oluşturur
 * 3. Verileri PostgreSQL'e aktarır
 * 
 * Kullanım: curl https://SITE/database/migrate_to_pg.php
 * ⚠️ Çalıştırdıktan sonra bu dosyayı silin!
 */

header('Content-Type: text/plain; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== MySQL → PostgreSQL Migration ===\n\n";

// ---- MySQL Bağlantısı (MYSQL_* env vars'tan) ----
$mysqlHost = getenv('MYSQL_HOST') ?: 'mcgscc440skos040gwwsww08';
$mysqlPort = getenv('MYSQL_PORT') ?: '3306';
$mysqlDb   = getenv('MYSQL_DB')   ?: 'default';
$mysqlUser = getenv('MYSQL_USER') ?: 'mysql';
$mysqlPass = getenv('MYSQL_PASS') ?: '';

try {
    $mysql = new PDO(
        "mysql:host=$mysqlHost;port=$mysqlPort;dbname=$mysqlDb;charset=utf8mb4",
        $mysqlUser, $mysqlPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    echo "✅ MySQL bağlantısı OK\n";
} catch (PDOException $e) {
    die("❌ MySQL bağlantı hatası: " . $e->getMessage() . "\n");
}

// ---- PostgreSQL Bağlantısı (DB_* env vars — config.php ile aynı) ----
$pgHost = getenv('PG_HOST') ?: (getenv('DB_HOST') ?: 'u0owk4owog0go48gc8o8s4ss');
$pgPort = getenv('PG_PORT') ?: (getenv('DB_PORT') ?: '5432');
$pgDb   = getenv('PG_DB')   ?: (getenv('DB_NAME') ?: 'mybheg_crm');
$pgUser = getenv('PG_USER') ?: (getenv('DB_USER') ?: 'mybheg');
$pgPass = getenv('PG_PASS') ?: (getenv('DB_PASS') ?: 'MBhg2026PgSecure!x7k9z');

try {
    $pg = new PDO(
        "pgsql:host=$pgHost;port=$pgPort;dbname=$pgDb",
        $pgUser, $pgPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ PostgreSQL bağlantısı OK\n\n";
} catch (PDOException $e) {
    die("❌ PostgreSQL bağlantı hatası: " . $e->getMessage() . "\n");
}

// ---- Schema Oluştur ----
echo "--- Schema oluşturuluyor ---\n";
$schema = file_get_contents(__DIR__ . '/pg_schema.sql');
$pg->exec($schema);
echo "✅ Tablolar oluşturuldu\n\n";

// ---- 1. Users Tablosu ----
echo "--- Users tablosu aktarılıyor ---\n";
$users = $mysql->query("SELECT * FROM users")->fetchAll();
$pgStmt = $pg->prepare("INSERT INTO users (id, username, password_hash, created_at, updated_at) 
                         VALUES (?, ?, ?, ?, ?) ON CONFLICT (id) DO NOTHING");
foreach ($users as $u) {
    $pgStmt->execute([
        $u['id'],
        $u['username'],
        $u['password_hash'],
        $u['created_at'] ?? date('Y-m-d H:i:s'),
        $u['updated_at'] ?? date('Y-m-d H:i:s')
    ]);
}
echo "✅ " . count($users) . " kullanıcı aktarıldı\n\n";

// ---- 2. Contacts Tablosu ----
echo "--- Contacts tablosu aktarılıyor ---\n";
$contacts = $mysql->query("SELECT * FROM contacts")->fetchAll();
$pgStmt = $pg->prepare("INSERT INTO contacts (id, phone, name, created_at, updated_at) 
                         VALUES (?, ?, ?, ?, ?) ON CONFLICT (id) DO NOTHING");
$pg->beginTransaction();
foreach ($contacts as $c) {
    $pgStmt->execute([
        $c['id'],
        $c['phone'],
        $c['name'],
        $c['created_at'] ?? date('Y-m-d H:i:s'),
        $c['updated_at'] ?? date('Y-m-d H:i:s')
    ]);
}
$pg->commit();
echo "✅ " . count($contacts) . " kişi aktarıldı\n\n";

// ---- 3. Messages Tablosu ----
echo "--- Messages tablosu aktarılıyor ---\n";
$msgCount = $mysql->query("SELECT COUNT(*) FROM messages")->fetchColumn();
echo "Toplam $msgCount mesaj aktarılacak...\n";

$offset = 0;
$batchSize = 100;
$totalInserted = 0;

$pgStmt = $pg->prepare("INSERT INTO messages (id, message_id, contact_id, direction, type, content, timestamp, raw_data) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON CONFLICT (id) DO NOTHING");

while ($offset < $msgCount) {
    $messages = $mysql->query("SELECT * FROM messages ORDER BY id LIMIT $batchSize OFFSET $offset")->fetchAll();
    
    $pg->beginTransaction();
    foreach ($messages as $m) {
        $pgStmt->execute([
            $m['id'],
            $m['message_id'],
            $m['contact_id'],
            $m['direction'],
            $m['type'],
            $m['content'],
            $m['timestamp'],
            $m['raw_data']
        ]);
        $totalInserted++;
    }
    $pg->commit();
    
    $offset += $batchSize;
    echo "  ... $totalInserted / $msgCount\n";
}
echo "✅ $totalInserted mesaj aktarıldı\n\n";

// ---- 4. Orders Tablosu (varsa) ----
echo "--- Orders tablosu kontrol ediliyor ---\n";
try {
    $orderCols = $mysql->query("DESCRIBE orders")->fetchAll(PDO::FETCH_COLUMN, 0);
    $orders = $mysql->query("SELECT * FROM orders")->fetchAll();
    
    if (count($orders) > 0) {
        // Dinamik INSERT — mevcut sütunlara göre
        $pg->beginTransaction();
        foreach ($orders as $o) {
            // orders tablosundaki ana sütunları aktar
            $pgStmt = $pg->prepare("INSERT INTO orders (id, order_id, shopify_order_id, customer_name, customer_phone, email, total_price, currency, status, address, note, line_items, created_at, updated_at, first_message_sent)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON CONFLICT (id) DO NOTHING");
            $pgStmt->execute([
                $o['id'] ?? null,
                $o['order_id'] ?? null,
                $o['shopify_order_id'] ?? null,
                $o['customer_name'] ?? null,
                $o['customer_phone'] ?? null,
                $o['email'] ?? null,
                $o['total_price'] ?? null,
                $o['currency'] ?? 'TRY',
                $o['status'] ?? 'pending',
                $o['address'] ?? null,
                $o['note'] ?? null,
                $o['line_items'] ?? null,
                $o['created_at'] ?? date('Y-m-d H:i:s'),
                $o['updated_at'] ?? date('Y-m-d H:i:s'),
                $o['first_message_sent'] ?? false
            ]);
        }
        $pg->commit();
        echo "✅ " . count($orders) . " sipariş aktarıldı\n\n";
    } else {
        echo "ℹ️ Orders tablosu boş — atlanıyor\n\n";
    }
} catch (PDOException $e) {
    echo "ℹ️ Orders tablosu bulunamadı veya hata: " . $e->getMessage() . "\n\n";
}

// ---- Sequence'ları güncelle (auto-increment devam etsin) ----
echo "--- Sequence'lar güncelleniyor ---\n";
$tables = ['users', 'contacts', 'messages', 'orders'];
foreach ($tables as $t) {
    try {
        $maxId = $pg->query("SELECT COALESCE(MAX(id), 0) FROM $t")->fetchColumn();
        $seqName = "{$t}_id_seq";
        $pg->exec("SELECT setval('$seqName', $maxId, true)");
        echo "✅ $t sequence → $maxId\n";
    } catch (PDOException $e) {
        echo "⚠️ $t sequence hatası: " . $e->getMessage() . "\n";
    }
}

// ---- Doğrulama ----
echo "\n--- Doğrulama ---\n";
$pgUsers = $pg->query("SELECT COUNT(*) FROM users")->fetchColumn();
$pgContacts = $pg->query("SELECT COUNT(*) FROM contacts")->fetchColumn();
$pgMessages = $pg->query("SELECT COUNT(*) FROM messages")->fetchColumn();

echo "PostgreSQL:\n";
echo "  Users:    $pgUsers\n";
echo "  Contacts: $pgContacts\n";
echo "  Messages: $pgMessages\n";
try {
    $pgOrders = $pg->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    echo "  Orders:   $pgOrders\n";
} catch (PDOException $e) {
    echo "  Orders:   (tablo yok)\n";
}

echo "\n=== Migration başarıyla tamamlandı! ===\n";
echo "⚠️ GÜVENLİK: Bu dosyayı production'dan silin!\n";

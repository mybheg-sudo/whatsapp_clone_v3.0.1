<?php
// config.php
// Üretim ortamı (Hostinger) ayarları
date_default_timezone_set('Europe/Istanbul');
ini_set('display_errors', 0);
error_reporting(E_ALL);

$host = getenv('DB_HOST') ?: '92.113.22.4';
$db   = getenv('DB_NAME') ?: 'u183773716_whatsapp';
$user = getenv('DB_USER') ?: 'u183773716_whatsapp';
$pass = getenv('DB_PASS') ?: 'c4*M0X>wlNoF';
$port = getenv('DB_PORT') ?: '3306';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'",
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // MySQL bağlantı hatası — $pdo null olarak bırak, gereken yerlerde kontrol edilecek
    $pdo = null;
    error_log("MySQL bağlantı hatası: " . $e->getMessage());
}
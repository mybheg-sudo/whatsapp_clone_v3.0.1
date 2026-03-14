<?php
// config.php
// Üretim ve geliştirme ortamları ayarları
date_default_timezone_set('Europe/Istanbul');

// Ortam değişkenlerini .env dosyasından yükleme (lokal geliştirme için)
if (file_exists(__DIR__ . '/.env')) {
    $env_array = parse_ini_file(__DIR__ . '/.env');
    foreach ($env_array as $key => $value) {
        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

// Application environment
$appEnv = getenv('APP_ENV') ?: 'development';
$isProduction = ($appEnv === 'production');

if ($isProduction) {
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

// Production Security — HTTPS zorlama (kullanıcı onayıyla aktif)
if ($isProduction && getenv('FORCE_HTTPS') === 'true') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
        $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header("Location: $redirect", true, 301);
        exit;
    }
    // HSTS Header
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

// Production Security — CSP & security headers
if ($isProduction && getenv('CSP_ENABLED') === 'true') {
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: DENY");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: strict-origin-when-cross-origin");
}

// JWT Secret (environment variable'dan alınır)
define('JWT_SECRET', getenv('JWT_SECRET') ?: 'mybheg_dev_secret_key_change_in_production');

// Veritabanı bağlantı bilgileri (Environment Variables üzerinden alınır)
// PostgreSQL bağlantısı
$host = getenv('DB_HOST') ?: 'localhost';
$db   = getenv('DB_NAME') ?: 'mybheg_crm';
$user = getenv('DB_USER') ?: 'mybheg';
$pass = getenv('DB_PASS') ?: '';
$port = getenv('DB_PORT') ?: '5432';

$dsn = "pgsql:host=$host;port=$port;dbname=$db";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // PostgreSQL client encoding
    $pdo->exec("SET client_encoding TO 'UTF8'");
} catch (\PDOException $e) {
    $pdo = null;
    error_log("PostgreSQL bağlantı hatası: " . $e->getMessage());
}
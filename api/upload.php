<?php
// api/upload.php — Dosya yükleme endpoint'i
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_utils.php';

$userId = verifyTokenAndGetUser();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// Dosya kontrolü
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Dosya yükleme hatası']);
    exit;
}

$file = $_FILES['file'];
$maxSize = 10 * 1024 * 1024; // 10MB

if ($file['size'] > $maxSize) {
    http_response_code(413);
    echo json_encode(['error' => 'Dosya boyutu 10MB\'dan büyük olamaz']);
    exit;
}

// İzin verilen MIME türleri
$allowedTypes = [
    'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp',
    'application/pdf' => 'pdf',
    'application/msword' => 'doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    'application/vnd.ms-excel' => 'xls',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
    'text/plain' => 'txt'
];

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);

if (!isset($allowedTypes[$mime])) {
    http_response_code(415);
    echo json_encode(['error' => 'Bu dosya türü desteklenmiyor', 'mime' => $mime]);
    exit;
}

$ext = $allowedTypes[$mime];
$isImage = strpos($mime, 'image/') === 0;

// Upload klasörünü oluştur
$uploadsDir = __DIR__ . '/../uploads/' . $userId;
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

// Benzersiz dosya adı
$filename = uniqid('file_') . '.' . $ext;
$filepath = $uploadsDir . '/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Dosya kaydedilemedi']);
    exit;
}

// URL oluştur
$fileUrl = 'uploads/' . $userId . '/' . $filename;

echo json_encode([
    'success' => true,
    'url' => $fileUrl,
    'filename' => $file['name'],
    'size' => $file['size'],
    'mime' => $mime,
    'is_image' => $isImage
]);

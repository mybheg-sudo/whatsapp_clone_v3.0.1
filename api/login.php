<?php
// api/login.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_utils.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

try {
    // Veritabanında users tablosundan kontrol et.
    $stmt = $pdo->prepare("SELECT id, username, password, system_phone FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    $isValid = false;
    $userData = [];

    if ($user && password_verify($password, $user['password'])) {
        $isValid = true;
        $userData = [
            'id' => $user['id'],
            'username' => $user['username'],
            'systemPhone' => $user['system_phone'] ?? '905419682572'
        ];
    }

    if ($isValid) {
        $token = generateToken($userData['id']);
        echo json_encode([
            'token' => $token,
            'user' => $userData
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Kullanıcı adı veya şifre hatalı.']);
    }

} catch (\PDOException $e) {
    error_log('DB Hatası: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Sistem hatası.']);
}

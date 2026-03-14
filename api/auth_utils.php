<?php
// api/auth_utils.php
// Basit JWT veya Bearer Token tarzı kontrol mekanizması

function generateToken($userId) {
    // Gerçek bir sistemde güçlü bir Secret Key ile (firebase/php-jwt) imzalanmalıdır.
    // Şimdilik demo için base64 encode ile basit bir "BHEG-TOKEN-ID" yapısı kuruyoruz.
    $payload = [
        'user_id' => $userId,
        'exp' => time() + (86400 * 7) // 7 Gün
    ];
    return base64_encode(json_encode($payload)) . '.mocksignature';
}

function verifyTokenAndGetUser() {
    $authHeader = '';
    
    // 1. getallheaders() — PHP built-in server ve Apache'de çalışır
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        // Case-insensitive arama
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'authorization') {
                $authHeader = $value;
                break;
            }
        }
    }
    
    // 2. Fallback: $_SERVER
    if (empty($authHeader) && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    }
    if (empty($authHeader) && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }

    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $token = $matches[1];
        $parts = explode('.', $token);
        
        if (count($parts) >= 1) {
            $payload = json_decode(base64_decode($parts[0]), true);
            if ($payload && isset($payload['user_id']) && $payload['exp'] > time()) {
                return $payload['user_id'];
            }
        }
    }
    
    // Token geçersiz veya yoksa
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['error' => '401 Access Denied']);
    exit;
}

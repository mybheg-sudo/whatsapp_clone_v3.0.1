<?php
// api/auth_utils.php
// Basit JWT veya Bearer Token tarzı kontrol mekanizması

function generateToken($userId) {
    $secretKey = getenv('JWT_SECRET') ?: 'default-secret-key-12345';
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload = json_encode([
        'user_id' => $userId,
        'exp' => time() + (86400 * 7) // 7 Gün
    ]);
    
    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secretKey, true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
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
        
        if (count($parts) === 3) {
            $secretKey = getenv('JWT_SECRET') ?: 'default-secret-key-12345';
            $signature = hash_hmac('sha256', $parts[0] . "." . $parts[1], $secretKey, true);
            $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
            
            if (hash_equals($base64UrlSignature, $parts[2])) {
                $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);
                if ($payload && isset($payload['user_id']) && $payload['exp'] > time()) {
                    return $payload['user_id'];
                }
            }
        }
    }
    
    // Token geçersiz veya yoksa
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['error' => '401 Access Denied']);
    exit;
}

<?php
// api/health.php — Coolify healthcheck endpoint
// Basit bir 200 OK döner + DB bağlantı kontrolü
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$dbOk = ($pdo !== null);

http_response_code(200);
echo json_encode([
    'status' => 'ok',
    'db'     => $dbOk ? 'connected' : 'disconnected',
    'time'   => date('c')
]);

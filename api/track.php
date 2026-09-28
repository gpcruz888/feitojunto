<?php
/**
 * feito. — Analytics Tracker Endpoint
 * Registra visitas de página no banco SQLite.
 * Chamado via fetch() no frontend — não bloqueia o carregamento da página.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Aceita apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit;
}

// Ignora bots conhecidos
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$botPattern = '/(bot|crawl|slurp|spider|mediapartners|google|bing|yahoo|semrush|ahref|mj12|dataminr|rogerbot|linkedinbot|twitterbot|facebookexternalhit)/i';
if (preg_match($botPattern, $ua)) {
    echo json_encode(['ok' => false, 'reason' => 'bot']);
    exit;
}

require_once __DIR__ . '/../core/Env.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Schema.php';

// Garante que a tabela existe
try {
    Schema::up();
} catch (Exception $e) {}

$body = json_decode(file_get_contents('php://input'), true);
$url  = trim($body['url'] ?? $_SERVER['HTTP_REFERER'] ?? '');
$ref  = trim($body['referrer'] ?? '');
$sid  = trim($body['session_id'] ?? '');

if (empty($url)) {
    echo json_encode(['ok' => false, 'reason' => 'no url']);
    exit;
}

// Hash do IP para privacidade (LGPD)
$ip = $_SERVER['HTTP_CF_CONNECTING_IP']
    ?? $_SERVER['HTTP_X_FORWARDED_FOR']
    ?? $_SERVER['REMOTE_ADDR']
    ?? '';
$ipHash = hash('sha256', $ip . date('Y-m-d')); // muda a cada dia → não rastreia cross-day

// Sanitiza dados
$url    = substr($url, 0, 500);
$ref    = substr($ref, 0, 500);
$sid    = substr(preg_replace('/[^a-zA-Z0-9_\-]/', '', $sid), 0, 64);
$ua     = substr($ua, 0, 300);

try {
    $db = Database::getInstance();
    $stmt = $db->prepare("
        INSERT INTO page_views (url, referrer, ip_hash, user_agent, session_id)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$url, $ref ?: null, $ipHash, $ua, $sid ?: null]);
    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}

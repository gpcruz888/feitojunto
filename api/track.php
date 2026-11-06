<?php
/**
 * feito. — Analytics Tracker Endpoint
 * Registra visitas de página no banco SQLite.
 * Chamado via fetch() no frontend — não bloqueia o carregamento da página.
 */
require_once __DIR__ . '/../core/Env.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Schema.php';

Security::sendHeaders(false);
header('Content-Type: application/json; charset=utf-8');
header('Vary: Origin, Sec-Fetch-Site');

// Aceita apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit;
}

// Rejeita requisições cross-site via Fetch Metadata (Resource Isolation)
$secFetchSite = $_SERVER['HTTP_SEC_FETCH_SITE'] ?? '';
if (!empty($secFetchSite) && !in_array($secFetchSite, ['same-origin', 'same-site', 'none'], true)) {
    http_response_code(403);
    echo json_encode(['ok' => false]);
    exit;
}

// Valida Origin se presente (CORS restrito ao domínio da aplicação)
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (!empty($origin)) {
    $appUrl = rtrim((string)Env::get('APP_URL', 'https://feitojunto.com'), '/');
    $currentHost = $_SERVER['HTTP_HOST'] ?? '';
    $originHost = parse_url($origin, PHP_URL_HOST) ?: '';
    $appHost = parse_url($appUrl, PHP_URL_HOST) ?: '';

    if ($origin === $appUrl || ($originHost !== '' && ($originHost === $appHost || $originHost === explode(':', $currentHost)[0]))) {
        header("Access-Control-Allow-Origin: {$origin}");
    } else {
        http_response_code(403);
        echo json_encode(['ok' => false]);
        exit;
    }
}

// Ignora bots conhecidos
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$botPattern = '/(bot|crawl|slurp|spider|mediapartners|google|bing|yahoo|semrush|ahref|mj12|dataminr|rogerbot|linkedinbot|twitterbot|facebookexternalhit)/i';
if (preg_match($botPattern, $ua)) {
    echo json_encode(['ok' => false, 'reason' => 'bot']);
    exit;
}

// Garante que a tabela existe
try {
    Schema::up();
} catch (Exception $e) {
    error_log("Track schema error: " . $e->getMessage());
}

$rawBody = file_get_contents('php://input');
$body = is_string($rawBody) && $rawBody !== '' ? json_decode($rawBody, true) : [];
if (!is_array($body)) {
    $body = [];
}

$url  = trim((string)($body['url'] ?? $_SERVER['HTTP_REFERER'] ?? ''));
$ref  = trim((string)($body['referrer'] ?? ''));
$sid  = trim((string)($body['session_id'] ?? ''));

if (empty($url) || (!str_starts_with($url, '/') && !preg_match('#^https?://#i', $url))) {
    echo json_encode(['ok' => false, 'reason' => 'no url']);
    exit;
}

// Hash HMAC do IP para privacidade (LGPD) — resistente a rainbow tables
$ip = Security::getClientIp();
$secret = (string)Env::get('APP_SECRET', 'feito-lgpd-daily-salt-2026');
$ipHash = hash_hmac('sha256', $ip . '|' . date('Y-m-d'), $secret);

// Sanitiza dados
$url    = substr(strip_tags($url), 0, 500);
$ref    = substr(strip_tags($ref), 0, 500);
$sid    = substr(preg_replace('/[^a-zA-Z0-9_\-]/', '', $sid) ?? '', 0, 64);
$ua     = substr(preg_replace('/[\x00-\x1F\x7F]+/', ' ', $ua) ?? '', 0, 300);

try {
    $db = Database::getInstance();

    // Rate limit por IP para evitar flood no banco SQLite (máx 60 pageviews/minuto por IP)
    if (Security::isRateLimited($db, 'track_pv', $ip, 60, 1)) {
        http_response_code(429);
        echo json_encode(['ok' => false]);
        exit;
    }
    Security::recordRateLimitHit($db, 'track_pv', $ip);

    $stmt = $db->prepare("
        INSERT INTO page_views (url, referrer, ip_hash, user_agent, session_id)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$url, $ref ?: null, $ipHash, $ua, $sid ?: null]);
    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    error_log("Analytics track error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false]);
}

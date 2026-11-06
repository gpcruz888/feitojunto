<?php
require_once __DIR__ . '/../../core/Security.php';
Security::sendHeaders(true);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Csrf.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/Logger.php';

Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
    exit;
}

$rawInput = file_get_contents('php://input');
$input = is_string($rawInput) && $rawInput !== '' ? json_decode($rawInput, true) : null;
if (!is_array($input) || empty($input)) {
    $input = $_POST;
}

$csrfToken = $input['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
if (!Csrf::validate($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token de segurança inválido ou expirado. Recarregue a página.']);
    exit;
}

$leadId = (int)($input['lead_id'] ?? 0);
$note = mb_substr(trim((string)($input['note'] ?? '')), 0, 2000);

if ($leadId <= 0 || empty($note)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Por favor, escreva uma observação válida.']);
    exit;
}

try {
    $db = Database::getInstance();

    $stmt = $db->prepare("SELECT id, nome FROM leads WHERE id = ?");
    $stmt->execute([$leadId]);
    $lead = $stmt->fetch();

    if (!$lead) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Lead não encontrado']);
        exit;
    }

    $adminUser = Auth::user();
    $adminName = $adminUser['name'] ?? 'Administrador';

    $hist = $db->prepare("
        INSERT INTO lead_history (lead_id, admin_id, action, description)
        VALUES (?, ?, 'NOTE_ADDED', ?)
    ");
    $description = "Nota adicionada por {$adminName}: " . strip_tags($note);
    $hist->execute([$leadId, Auth::id(), $description]);

    // Atualiza last_contact_at
    $up = $db->prepare("UPDATE leads SET last_contact_at = CURRENT_TIMESTAMP WHERE id = ?");
    $up->execute([$leadId]);

    Logger::log('LEAD_NOTE_ADDED', 'lead', $leadId, "Nota adicionada ao lead #{$leadId}");

    echo json_encode(['success' => true, 'message' => 'Observação registrada com sucesso!']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno ao salvar nota']);
}

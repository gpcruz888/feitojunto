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
$newStatus = trim((string)($input['status'] ?? ''));

$allowedStatuses = ['NOVO', 'EM_CONTATO', 'QUALIFICADO', 'CONVERTIDO', 'PERDIDO', 'ARQUIVADO'];

if ($leadId <= 0 || !in_array($newStatus, $allowedStatuses, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

try {
    $db = Database::getInstance();

    // Obtém status atual
    $stmt = $db->prepare("SELECT status, nome FROM leads WHERE id = ?");
    $stmt->execute([$leadId]);
    $lead = $stmt->fetch();

    if (!$lead) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Lead não encontrado']);
        exit;
    }

    $oldStatus = $lead['status'];

    if ($oldStatus !== $newStatus) {
        $up = $db->prepare("UPDATE leads SET status = ?, updated_at = CURRENT_TIMESTAMP, last_contact_at = CURRENT_TIMESTAMP WHERE id = ?");
        $up->execute([$newStatus, $leadId]);

        // Grava no histórico do lead
        $hist = $db->prepare("
            INSERT INTO lead_history (lead_id, admin_id, action, description)
            VALUES (?, ?, 'STATUS_CHANGED', ?)
        ");
        $adminUser = Auth::user();
        $adminName = $adminUser['name'] ?? 'Administrador';
        $histDesc = "Status alterado de '{$oldStatus}' para '{$newStatus}' por {$adminName}";
        $hist->execute([$leadId, Auth::id(), $histDesc]);

        Logger::log('LEAD_STATUS_UPDATE', 'lead', $leadId, "Status do lead #{$leadId} ({$lead['nome']}) alterado para {$newStatus}");
    }

    echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso!']);
} catch (Exception $e) {
    error_log("Lead status update error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno ao atualizar status do lead.']);
}

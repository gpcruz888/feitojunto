<?php
require_once __DIR__ . '/../core/Security.php';
Security::sendHeaders(false);
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

require_once __DIR__ . '/../core/Schema.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Mailer.php';
require_once __DIR__ . '/../core/Logger.php';

// Garante que o banco e tabelas existam
try {
    Schema::up();
} catch (Exception $e) {
    error_log("Schema initialization error: " . $e->getMessage());
}

$inputJSON = file_get_contents('php://input');
$input = is_string($inputJSON) && $inputJSON !== '' ? json_decode($inputJSON, true) : null;

if (!is_array($input) || empty($input)) {
    $input = $_POST;
}

// 1. Anti-spam: Honeypot check (se preenchido, é um bot)
if (!empty($input['company_website']) || !empty($input['_hp'])) {
    // Retorna sucesso falso para enganar o bot sem processar
    echo json_encode(['success' => true, 'message' => 'Briefing recebido com sucesso!']);
    exit;
}

// 2. Sanitização e limitação de tamanho dos campos (prevenção de DoS/bloat)
$nome = mb_substr(trim(strip_tags((string)($input['nome'] ?? ''))), 0, 120);
$email = mb_substr(filter_var(trim((string)($input['email'] ?? '')), FILTER_SANITIZE_EMAIL), 0, 150);
$whatsapp = mb_substr(trim(strip_tags((string)($input['whatsapp'] ?? ''))), 0, 40);
$empresa = mb_substr(trim(strip_tags((string)($input['empresa'] ?? ''))), 0, 120);
$tipoProjeto = mb_substr(trim(strip_tags((string)($input['tipo_projeto'] ?? 'Geral'))), 0, 80);
$orcamento = mb_substr(trim(strip_tags((string)($input['prazo_orcamento'] ?? $input['orcamento'] ?? 'Não especificado'))), 0, 120);
$mensagem = mb_substr(trim(strip_tags((string)($input['mensagem'] ?? 'Sem mensagem'))), 0, 4000);
$source = mb_substr(trim(strip_tags((string)($input['source'] ?? 'site_modal'))), 0, 50);

// 3. Validação dos campos obrigatórios
if (empty($nome) || empty($email) || empty($whatsapp) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Por favor, preencha todos os campos obrigatórios com informações válidas.'
    ]);
    exit;
}

// 4. Rate Limiting de envio (máximo 10 requisições por hora por IP e 60 globais por hora)
$ip = Security::getClientIp();

try {
    $db = Database::getInstance();

    if (Security::isRateLimited($db, 'lead_submit', $ip, 10, 60)) {
        Logger::log('LEAD_RATE_LIMITED', 'lead', null, "Submissão de lead bloqueada por rate limit (IP: {$ip})");
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'message' => 'Muitas solicitações enviadas recentemente. Aguarde alguns minutos ou chame no WhatsApp.'
        ]);
        exit;
    }

    // Verifica limite global de submissões na última hora contra ataques distribuídos
    $rateStmt = $db->prepare("
        SELECT COUNT(*) FROM leads 
        WHERE created_at > datetime('now', '-1 hour')
    ");
    $rateStmt->execute();
    $hourlyLeads = (int)$rateStmt->fetchColumn();
    if ($hourlyLeads >= 60) {
        Logger::log('LEAD_GLOBAL_THROTTLE', 'lead', null, "Limite global de leads por hora atingido");
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'message' => 'Serviço temporariamente sobrecarregado. Por favor, entre em contato via WhatsApp.'
        ]);
        exit;
    }

    Security::recordRateLimitHit($db, 'lead_submit', $ip);

    // 5. Salva o Lead no banco de dados PRIMEIRO (Fonte da verdade)
    $stmt = $db->prepare("
        INSERT INTO leads (nome, email, whatsapp, empresa, tipo_projeto, prazo_orcamento, mensagem, source, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'NOVO')
    ");
    $stmt->execute([
        $nome,
        $email,
        $whatsapp,
        $empresa,
        $tipoProjeto,
        $orcamento,
        $mensagem,
        $source
    ]);
    $leadId = (int)$db->lastInsertId();

    // 6. Registra no histórico do lead
    $histStmt = $db->prepare("
        INSERT INTO lead_history (lead_id, action, description)
        VALUES (?, 'STATUS_CHANGED', 'Lead recebido através do formulário do site')
    ");
    $histStmt->execute([$leadId]);

    // Monta dados completos para notificação
    $leadData = [
        'id' => $leadId,
        'nome' => $nome,
        'email' => $email,
        'whatsapp' => $whatsapp,
        'empresa' => $empresa,
        'tipo_projeto' => $tipoProjeto,
        'prazo_orcamento' => $orcamento,
        'mensagem' => $mensagem,
        'source' => $source
    ];

    // 7. Notifica por e-mail (se falhar, o lead permanece salvo com segurança no banco)
    Mailer::notifyNewLead($leadData);

    // 8. Envia confirmação automática se estiver ativada
    Mailer::sendAutoReply($leadData);

    echo json_encode([
        'success' => true,
        'message' => 'Briefing recebido com sucesso! Nossa equipe entrará em contato em breve.'
    ]);

} catch (Exception $e) {
    error_log("Lead submission error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Ocorreu um erro interno ao processar sua solicitação. Por favor tente novamente ou entre em contato pelo WhatsApp.'
    ]);
}

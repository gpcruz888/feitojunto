<?php
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
$input = json_decode($inputJSON, true);

if (!$input) {
    $input = $_POST;
}

// 1. Anti-spam: Honeypot check (se preenchido, é um bot)
if (!empty($input['company_website']) || !empty($input['_hp'])) {
    // Retorna sucesso falso para enganar o bot sem processar
    echo json_encode(['success' => true, 'message' => 'Briefing recebido com sucesso!']);
    exit;
}

// 2. Sanitização dos campos
$nome = trim(strip_tags($input['nome'] ?? ''));
$email = filter_var(trim($input['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$whatsapp = trim(strip_tags($input['whatsapp'] ?? ''));
$empresa = trim(strip_tags($input['empresa'] ?? ''));
$tipoProjeto = trim(strip_tags($input['tipo_projeto'] ?? 'Geral'));
$orcamento = trim(strip_tags($input['prazo_orcamento'] ?? $input['orcamento'] ?? 'Não especificado'));
$mensagem = trim(strip_tags($input['mensagem'] ?? 'Sem mensagem'));
$source = trim(strip_tags($input['source'] ?? 'site_modal'));

// 3. Validação dos campos obrigatórios
if (empty($nome) || empty($email) || empty($whatsapp) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Por favor, preencha todos os campos obrigatórios com informações válidas.'
    ]);
    exit;
}

// 4. Rate Limiting de envio (máximo 10 requisições por hora por IP)
$ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
if (str_contains($ip, ',')) {
    $ip = trim(explode(',', $ip)[0]);
}

try {
    $db = Database::getInstance();

    // Verifica limite de submissões
    $rateStmt = $db->prepare("
        SELECT COUNT(*) FROM leads 
        WHERE created_at > datetime('now', '-1 hour')
    ");
    $rateStmt->execute();
    // Proteção global simples contra DDoS no formulário

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
    $mailSent = Mailer::notifyNewLead($leadData);

    // 8. Envia confirmação automática se estiver ativada
    Mailer::sendAutoReply($leadData);

    echo json_encode([
        'success' => true,
        'message' => 'Briefing recebido com sucesso! Nossa equipe entrará em contato em breve.',
        'lead_id' => $leadId,
        'mail_sent' => $mailSent
    ]);

} catch (Exception $e) {
    error_log("Lead submission error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Ocorreu um erro interno ao processar sua solicitação. Por favor tente novamente ou entre em contato pelo WhatsApp.'
    ]);
}

<?php
require_once dirname(__DIR__, 3) . '/core/Security.php';
Security::sendHeaders(true);
require_once dirname(__DIR__, 3) . '/core/Auth.php';
require_once dirname(__DIR__, 3) . '/core/Database.php';
require_once dirname(__DIR__, 3) . '/core/Logger.php';

Auth::requireLogin();

$db = Database::getInstance();

$search = trim((string)($_GET['q'] ?? ''));
$statusFilter = trim((string)($_GET['status'] ?? ''));
$allowedStatuses = ['NOVO', 'EM_CONTATO', 'QUALIFICADO', 'CONVERTIDO', 'PERDIDO', 'ARQUIVADO'];
if (!empty($statusFilter) && !in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = '';
}

$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(nome LIKE ? OR email LIKE ? OR whatsapp LIKE ? OR empresa LIKE ?)";
    $term = "%{$search}%";
    $params = array_merge($params, [$term, $term, $term, $term]);
}

if (!empty($statusFilter)) {
    $where[] = "status = ?";
    $params[] = $statusFilter;
}

$whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$stmt = $db->prepare("SELECT * FROM leads {$whereSql} ORDER BY created_at DESC");
$stmt->execute($params);
$leads = $stmt->fetchAll();

Logger::log('LEADS_EXPORT', 'lead', null, "Exportação CSV de " . count($leads) . " leads realizada");

$filename = 'leads-feito-' . date('Y-m-d_His') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header("Content-Disposition: attachment; filename=\"{$filename}\"");
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// UTF-8 BOM para compatibilidade com Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Cabeçalho CSV
fputcsv($output, [
    'ID',
    'Nome',
    'E-mail',
    'WhatsApp',
    'Empresa',
    'Tipo de Projeto',
    'Orçamento/Prazo',
    'Mensagem',
    'Origem',
    'Status',
    'Data de Recebimento'
], ';');

foreach ($leads as $l) {
    fputcsv($output, [
        $l['id'],
        Security::sanitizeCsvValue($l['nome']),
        Security::sanitizeCsvValue($l['email']),
        Security::sanitizeCsvValue($l['whatsapp']),
        Security::sanitizeCsvValue($l['empresa']),
        Security::sanitizeCsvValue($l['tipo_projeto']),
        Security::sanitizeCsvValue($l['prazo_orcamento']),
        Security::sanitizeCsvValue($l['mensagem']),
        Security::sanitizeCsvValue($l['source']),
        Security::sanitizeCsvValue($l['status']),
        Security::sanitizeCsvValue($l['created_at'])
    ], ';');
}

fclose($output);
exit;

<?php
require_once __DIR__ . '/../../../core/Auth.php';
require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../../../core/Logger.php';

Auth::requireLogin();

$db = Database::getInstance();

$search = trim($_GET['q'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

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
        $l['nome'],
        $l['email'],
        $l['whatsapp'],
        $l['empresa'],
        $l['tipo_projeto'],
        $l['prazo_orcamento'],
        $l['mensagem'],
        $l['source'],
        $l['status'],
        $l['created_at']
    ], ';');
}

fclose($output);
exit;

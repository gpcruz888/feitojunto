<?php
$adminTitle = 'Gerenciamento de Leads';

require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../../../core/Helpers.php';
require_once __DIR__ . '/../../../core/Auth.php';

$db = Database::getInstance();

// Filtros
$search = trim($_GET['q'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$page = max(1, (int)($_GET['p'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Montagem da query
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

// Contagem total para paginação
$countStmt = $db->prepare("SELECT COUNT(*) FROM leads {$whereSql}");
$countStmt->execute($params);
$totalLeads = (int)$countStmt->fetchColumn();
$totalPages = ceil($totalLeads / $perPage);

// Busca registros paginados
$query = "
    SELECT * FROM leads 
    {$whereSql} 
    ORDER BY created_at DESC 
    LIMIT {$perPage} OFFSET {$offset}
";
$stmt = $db->prepare($query);
$stmt->execute($params);
$leads = $stmt->fetchAll();

// Ações no topo
$topbarActions = '
  <a href="/admin/leads/export?' . http_build_query($_GET) . '" class="btn btn--glass" style="padding: 8px 16px; font-size: 13px;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
    Exportar CSV
  </a>
';

require_once __DIR__ . '/../layout/header.php';
?>

<div class="admin-card">
  <!-- Barra de Filtros & Busca -->
  <div class="admin-card__header">
    <form method="GET" action="/admin/leads" class="admin-filter-bar">
      <input 
        type="text" 
        name="q" 
        class="admin-search-input" 
        placeholder="Buscar nome, e-mail ou whats..." 
        value="<?php echo Helpers::e($search); ?>"
      >

      <select name="status" class="admin-filter-select" onchange="this.form.submit()">
        <option value="">Todos os Status</option>
        <option value="NOVO" <?php echo $statusFilter === 'NOVO' ? 'selected' : ''; ?>>Novo</option>
        <option value="EM_CONTATO" <?php echo $statusFilter === 'EM_CONTATO' ? 'selected' : ''; ?>>Em Contato</option>
        <option value="QUALIFICADO" <?php echo $statusFilter === 'QUALIFICADO' ? 'selected' : ''; ?>>Qualificado</option>
        <option value="CONVERTIDO" <?php echo $statusFilter === 'CONVERTIDO' ? 'selected' : ''; ?>>Convertido</option>
        <option value="PERDIDO" <?php echo $statusFilter === 'PERDIDO' ? 'selected' : ''; ?>>Perdido</option>
        <option value="ARQUIVADO" <?php echo $statusFilter === 'ARQUIVADO' ? 'selected' : ''; ?>>Arquivado</option>
      </select>

      <button type="submit" class="btn btn-primary" style="padding: 8px 16px; font-size: 13px;">Filtrar</button>
      
      <?php if (!empty($search) || !empty($statusFilter)): ?>
        <a href="/admin/leads" style="font-size: 13px; color: var(--text-white-40); text-decoration: underline; margin-left: 6px;">Limpar</a>
      <?php endif; ?>
    </form>

    <div style="font-size: 13px; color: var(--text-white-40);">
      Mostrando <strong><?php echo count($leads); ?></strong> de <strong><?php echo $totalLeads; ?></strong> leads
    </div>
  </div>

  <!-- Tabela Principal de Leads -->
  <div class="admin-table-container">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Lead / Contato</th>
          <th>WhatsApp</th>
          <th>Projeto / Investimento</th>
          <th>Status</th>
          <th>Origem</th>
          <th>Recebido em</th>
          <th style="text-align: right;">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($leads)): ?>
          <tr>
            <td colspan="7" style="text-align: center; color: var(--text-white-40); padding: 48px;">
              Nenhum lead encontrado com os filtros selecionados.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($leads as $lead): 
            $badge = Helpers::leadStatusBadge($lead['status']);
          ?>
            <tr>
              <td>
                <a href="/admin/leads/view?id=<?php echo $lead['id']; ?>" style="font-weight: 600; color: var(--text-white); font-size: 14px;">
                  <?php echo Helpers::e($lead['nome']); ?>
                </a>
                <div style="font-size: 12px; color: var(--text-white-40); margin-top: 2px;">
                  <?php echo Helpers::e($lead['email']); ?>
                  <?php if (!empty($lead['empresa'])): ?>
                    • <span style="color: var(--text-white-65);"><?php echo Helpers::e($lead['empresa']); ?></span>
                  <?php endif; ?>
                </div>
              </td>
              <td>
                <a href="https://wa.me/<?php echo preg_replace('/\D/', '', $lead['whatsapp']); ?>" target="_blank" rel="noopener" style="color: #25d366; font-weight: 500; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                  <?php echo Helpers::e($lead['whatsapp']); ?>
                </a>
              </td>
              <td>
                <div style="font-size: 13px; font-weight: 500; color: var(--text-white);"><?php echo Helpers::e($lead['tipo_projeto']); ?></div>
                <div style="font-size: 12px; color: var(--text-white-40);"><?php echo Helpers::e($lead['prazo_orcamento']); ?></div>
              </td>
              <td>
                <select 
                  class="admin-filter-select js-lead-status-select" 
                  data-lead-id="<?php echo $lead['id']; ?>"
                  style="padding: 4px 10px; font-size: 12px; border-radius: var(--radius-xs);"
                >
                  <option value="NOVO" <?php echo $lead['status'] === 'NOVO' ? 'selected' : ''; ?>>Novo</option>
                  <option value="EM_CONTATO" <?php echo $lead['status'] === 'EM_CONTATO' ? 'selected' : ''; ?>>Em Contato</option>
                  <option value="QUALIFICADO" <?php echo $lead['status'] === 'QUALIFICADO' ? 'selected' : ''; ?>>Qualificado</option>
                  <option value="CONVERTIDO" <?php echo $lead['status'] === 'CONVERTIDO' ? 'selected' : ''; ?>>Convertido</option>
                  <option value="PERDIDO" <?php echo $lead['status'] === 'PERDIDO' ? 'selected' : ''; ?>>Perdido</option>
                  <option value="ARQUIVADO" <?php echo $lead['status'] === 'ARQUIVADO' ? 'selected' : ''; ?>>Arquivado</option>
                </select>
              </td>
              <td style="font-size: 12px; color: var(--text-white-40);">
                <?php echo Helpers::e($lead['source']); ?>
              </td>
              <td style="font-size: 12px; color: var(--text-white-40); white-space: nowrap;">
                <?php echo Helpers::formatDate($lead['created_at']); ?>
              </td>
              <td style="text-align: right;">
                <div class="admin-actions" style="justify-content: flex-end;">
                  <a href="/admin/leads/view?id=<?php echo $lead['id']; ?>" class="admin-action-btn" title="Ver detalhes e histórico">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Paginação -->
  <?php if ($totalPages > 1): ?>
    <div style="padding: 16px 24px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--hairline);">
      <div style="font-size: 13px; color: var(--text-white-40);">
        Página <?php echo $page; ?> de <?php echo $totalPages; ?>
      </div>
      <div style="display: flex; gap: 8px;">
        <?php if ($page > 1): ?>
          <a href="/admin/leads?<?php echo http_build_query(array_merge($_GET, ['p' => $page - 1])); ?>" class="btn--glass btn" style="padding: 6px 14px; font-size: 12px;">← Anterior</a>
        <?php endif; ?>
        <?php if ($page < $totalPages): ?>
          <a href="/admin/leads?<?php echo http_build_query(array_merge($_GET, ['p' => $page + 1])); ?>" class="btn--glass btn" style="padding: 6px 14px; font-size: 12px;">Próxima →</a>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

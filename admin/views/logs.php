<?php
$adminTitle = 'Logs de Auditoria';

require_once dirname(__DIR__, 2) . '/core/Database.php';
require_once dirname(__DIR__, 2) . '/core/Helpers.php';
require_once dirname(__DIR__, 2) . '/core/Auth.php';

Auth::requireRole('SUPER_ADMIN');

$db = Database::getInstance();

$page = max(1, (int)($_GET['p'] ?? 1));
$perPage = 30;
$offset = ($page - 1) * $perPage;

$countStmt = $db->query("SELECT COUNT(*) FROM audit_logs");
$totalLogs = (int)$countStmt->fetchColumn();
$totalPages = ceil($totalLogs / $perPage);

$stmt = $db->prepare("
    SELECT l.*, a.name as admin_name, a.email as admin_email 
    FROM audit_logs l 
    LEFT JOIN admins a ON l.admin_id = a.id 
    ORDER BY l.created_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->execute([$perPage, $offset]);
$logs = $stmt->fetchAll();

require_once __DIR__ . '/layout/header.php';
?>

<div class="admin-card">
  <div class="admin-card__header">
    <h3 class="admin-card__title">Registro Central de Atividades</h3>
    <span style="font-size: 13px; color: var(--text-white-40);">Total de <?php echo $totalLogs; ?> eventos registrados</span>
  </div>

  <div class="admin-table-container">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Data & Hora</th>
          <th>Ação</th>
          <th>Usuário / Responsável</th>
          <th>Descrição do Evento</th>
          <th>IP de Origem</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($logs)): ?>
          <tr>
            <td colspan="5" style="text-align: center; color: var(--text-white-40); padding: 48px;">
              Nenhum registro de log encontrado.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($logs as $log): ?>
            <tr>
              <td style="font-size: 12px; color: var(--text-white-65); white-space: nowrap;">
                <?php echo Helpers::formatDate($log['created_at']); ?>
              </td>
              <td>
                <span class="admin-badge" style="background: rgba(255, 255, 255, 0.07); color: var(--text-white); font-family: monospace; font-size: 11px;">
                  <?php echo Helpers::e($log['action']); ?>
                </span>
              </td>
              <td>
                <?php if (!empty($log['admin_name'])): ?>
                  <strong style="color: var(--text-white); font-size: 13px;"><?php echo Helpers::e($log['admin_name']); ?></strong>
                  <div style="font-size: 11px; color: var(--text-white-40);"><?php echo Helpers::e($log['admin_email']); ?></div>
                <?php else: ?>
                  <span style="color: var(--text-white-40); font-size: 12px;">Sistema / Anônimo</span>
                <?php endif; ?>
              </td>
              <td style="font-size: 13px; color: var(--text-white-90);">
                <?php echo Helpers::e($log['description']); ?>
              </td>
              <td style="font-size: 12px; color: var(--text-white-40); font-family: monospace;">
                <?php echo Helpers::e($log['ip_address']); ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if ($totalPages > 1): ?>
    <div style="padding: 16px 24px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--hairline);">
      <div style="font-size: 13px; color: var(--text-white-40);">
        Página <?php echo $page; ?> de <?php echo $totalPages; ?>
      </div>
      <div style="display: flex; gap: 8px;">
        <?php if ($page > 1): ?>
          <a href="/admin/logs?p=<?php echo $page - 1; ?>" class="btn--glass btn" style="padding: 6px 14px; font-size: 12px;">← Anterior</a>
        <?php endif; ?>
        <?php if ($page < $totalPages): ?>
          <a href="/admin/logs?p=<?php echo $page + 1; ?>" class="btn--glass btn" style="padding: 6px 14px; font-size: 12px;">Próxima →</a>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>

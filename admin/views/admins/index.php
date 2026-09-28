<?php
$adminTitle = 'Gerenciamento de Administradores';

require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../../../core/Helpers.php';
require_once __DIR__ . '/../../../core/Auth.php';
require_once __DIR__ . '/../../../core/Csrf.php';
require_once __DIR__ . '/../../../core/Logger.php';

Auth::requireRole('SUPER_ADMIN');

$db = Database::getInstance();
$message = '';
$messageType = '';

// Exclusão / Alteração de Status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (Csrf::validate()) {
        $action = $_POST['action'] ?? '';
        $targetId = (int)($_POST['admin_id'] ?? 0);
        $currentId = Auth::id();

        if ($action === 'delete' && $targetId > 0) {
            if ($targetId === $currentId) {
                $message = 'Você não pode excluir a sua própria conta.';
                $messageType = 'error';
            } else {
                // Checa se é o último SUPER_ADMIN
                $superCount = (int)$db->query("SELECT COUNT(*) FROM admins WHERE role = 'SUPER_ADMIN' AND status = 'ATIVO'")->fetchColumn();
                $targetRole = $db->query("SELECT role FROM admins WHERE id = {$targetId}")->fetchColumn();

                if ($targetRole === 'SUPER_ADMIN' && $superCount <= 1) {
                    $message = 'Não é possível remover o único Super Administrador ativo do sistema.';
                    $messageType = 'error';
                } else {
                    $del = $db->prepare("DELETE FROM admins WHERE id = ?");
                    $del->execute([$targetId]);
                    Logger::log('ADMIN_DELETED', 'admin', $targetId, "Administrador #{$targetId} excluído");
                    $message = 'Administrador removido com sucesso.';
                    $messageType = 'success';
                }
            }
        }

        if ($action === 'toggle_status' && $targetId > 0) {
            if ($targetId === $currentId) {
                $message = 'Você não pode desativar seu próprio acesso.';
                $messageType = 'error';
            } else {
                $newStatus = ($_POST['current_status'] === 'ATIVO') ? 'INATIVO' : 'ATIVO';
                $up = $db->prepare("UPDATE admins SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $up->execute([$newStatus, $targetId]);
                Logger::log('ADMIN_STATUS_TOGGLED', 'admin', $targetId, "Status do admin #{$targetId} alterado para {$newStatus}");
                $message = "Status alterado para {$newStatus}.";
                $messageType = 'success';
            }
        }
    }
}

// Lista administradores
$admins = $db->query("SELECT * FROM admins ORDER BY created_at ASC")->fetchAll();

$topbarActions = '
  <a href="/admin/admins/novo" class="btn btn-primary" style="padding: 8px 18px; font-size: 13px;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Novo Administrador
  </a>
';

require_once __DIR__ . '/../layout/header.php';
?>

<?php if (!empty($message)): ?>
  <div class="admin-alert admin-alert--<?php echo $messageType; ?>" style="margin-bottom: 24px;">
    <span><?php echo Helpers::e($message); ?></span>
  </div>
<?php endif; ?>

<div class="admin-card">
  <div class="admin-card__header">
    <h3 class="admin-card__title">Usuários com Acesso ao Painel</h3>
    <span style="font-size: 13px; color: var(--text-white-40);"><?php echo count($admins); ?> cadastrados</span>
  </div>

  <div class="admin-table-container">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Nome</th>
          <th>E-mail</th>
          <th>Função / Nível</th>
          <th>Status</th>
          <th>Último Acesso</th>
          <th>Criado em</th>
          <th style="text-align: right;">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($admins as $adm): 
          $isMe = ($adm['id'] === Auth::id());
        ?>
          <tr>
            <td>
              <strong style="color: var(--text-white);"><?php echo Helpers::e($adm['name']); ?></strong>
              <?php if ($isMe): ?>
                <span class="admin-badge" style="background: rgba(255, 90, 0, 0.15); color: var(--accent); margin-left: 6px;">você</span>
              <?php endif; ?>
            </td>
            <td style="font-size: 13px; color: var(--text-white-90);">
              <?php echo Helpers::e($adm['email']); ?>
            </td>
            <td>
              <span class="admin-badge" style="background: rgba(255,255,255,0.06); color: var(--text-white);">
                <?php echo $adm['role'] === 'SUPER_ADMIN' ? 'Super Admin' : 'Editor'; ?>
              </span>
            </td>
            <td>
              <?php if ($adm['status'] === 'ATIVO'): ?>
                <span class="admin-badge" style="background: rgba(34, 197, 94, 0.15); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.3);">Ativo</span>
              <?php else: ?>
                <span class="admin-badge" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3);">Inativo</span>
              <?php endif; ?>
            </td>
            <td style="font-size: 12px; color: var(--text-white-40);">
              <?php echo $adm['last_login_at'] ? Helpers::timeAgo($adm['last_login_at']) : 'Nunca acessou'; ?>
            </td>
            <td style="font-size: 12px; color: var(--text-white-40);">
              <?php echo Helpers::formatDate($adm['created_at'], false); ?>
            </td>
            <td style="text-align: right;">
              <div class="admin-actions" style="justify-content: flex-end;">
                <!-- Editar -->
                <a href="/admin/admins/editar?id=<?php echo $adm['id']; ?>" class="admin-action-btn" title="Editar Usuário">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </a>

                <?php if (!$isMe): ?>
                  <!-- Alternar Status -->
                  <form method="POST" style="margin: 0; display: inline;">
                    <?php echo Csrf::input(); ?>
                    <input type="hidden" name="action" value="toggle_status">
                    <input type="hidden" name="admin_id" value="<?php echo $adm['id']; ?>">
                    <input type="hidden" name="current_status" value="<?php echo $adm['status']; ?>">
                    <button type="submit" class="admin-action-btn" title="<?php echo $adm['status'] === 'ATIVO' ? 'Desativar' : 'Ativar'; ?>">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                    </button>
                  </form>

                  <!-- Excluir -->
                  <form method="POST" style="margin: 0; display: inline;">
                    <?php echo Csrf::input(); ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="admin_id" value="<?php echo $adm['id']; ?>">
                    <button type="submit" class="admin-action-btn admin-action-btn--danger" title="Excluir Usuário" data-confirm="Tem certeza que deseja remover este administrador?">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                  </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

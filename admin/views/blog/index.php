<?php
$adminTitle = 'Gerenciamento do Blog';

require_once dirname(__DIR__, 3) . '/core/Database.php';
require_once dirname(__DIR__, 3) . '/core/Helpers.php';
require_once dirname(__DIR__, 3) . '/core/Auth.php';
require_once dirname(__DIR__, 3) . '/core/Csrf.php';
require_once dirname(__DIR__, 3) . '/core/Logger.php';

Auth::requireLogin();

$db = Database::getInstance();

// Ações rápidas (exclusão/arquivamento via POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (Csrf::validate()) {
        $action = $_POST['action'] ?? '';
        $postId = (int)($_POST['post_id'] ?? 0);

        if ($action === 'delete' && $postId > 0) {
            $del = $db->prepare("DELETE FROM posts WHERE id = ?");
            $del->execute([$postId]);
            Logger::log('POST_DELETED', 'post', $postId, "Post #{$postId} excluído");
            header('Location: /admin/blog?deleted=1');
            exit;
        }

        if ($action === 'toggle_status' && $postId > 0) {
            $allowedPostStatuses = ['RASCUNHO', 'PUBLICADO', 'ARQUIVADO'];
            $newSt = in_array($_POST['new_status'] ?? '', $allowedPostStatuses, true) ? $_POST['new_status'] : 'RASCUNHO';
            $pubAt = ($newSt === 'PUBLICADO') ? date('Y-m-d H:i:s') : null;
            $up = $db->prepare("UPDATE posts SET status = ?, published_at = COALESCE(published_at, ?), updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $up->execute([$newSt, $pubAt, $postId]);
            Logger::log('POST_STATUS_CHANGED', 'post', $postId, "Status do post #{$postId} alterado para {$newSt}");
            header('Location: /admin/blog?updated=1');
            exit;
        }
    }
}

// Filtros
$search = trim($_GET['q'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$catFilter = (int)($_GET['category'] ?? 0);

$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(p.title LIKE ? OR p.excerpt LIKE ?)";
    $term = "%{$search}%";
    $params = array_merge($params, [$term, $term]);
}

if (!empty($statusFilter)) {
    $where[] = "p.status = ?";
    $params[] = $statusFilter;
}

if ($catFilter > 0) {
    $where[] = "p.category_id = ?";
    $params[] = $catFilter;
}

$whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$query = "
    SELECT p.*, c.name as category_name, a.name as author_name 
    FROM posts p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN admins a ON p.author_id = a.id 
    {$whereSql} 
    ORDER BY p.created_at DESC
";
$stmt = $db->prepare($query);
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Busca categorias para o filtro
$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

$topbarActions = '
  <a href="/admin/blog/novo" class="btn btn-primary" style="padding: 8px 18px; font-size: 13px;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Novo Artigo
  </a>
';

require_once __DIR__ . '/../layout/header.php';
?>

<div class="admin-card">
  <!-- Filtros -->
  <div class="admin-card__header">
    <form method="GET" action="/admin/blog" class="admin-filter-bar">
      <input 
        type="text" 
        name="q" 
        class="admin-search-input" 
        placeholder="Buscar artigos..." 
        value="<?php echo Helpers::e($search); ?>"
      >

      <select name="status" class="admin-filter-select" onchange="this.form.submit()">
        <option value="">Todos os Status</option>
        <option value="PUBLICADO" <?php echo $statusFilter === 'PUBLICADO' ? 'selected' : ''; ?>>Publicados</option>
        <option value="RASCUNHO" <?php echo $statusFilter === 'RASCUNHO' ? 'selected' : ''; ?>>Rascunhos</option>
        <option value="ARQUIVADO" <?php echo $statusFilter === 'ARQUIVADO' ? 'selected' : ''; ?>>Arquivados</option>
      </select>

      <select name="category" class="admin-filter-select" onchange="this.form.submit()">
        <option value="">Todas as Categorias</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?php echo $cat['id']; ?>" <?php echo $catFilter === (int)$cat['id'] ? 'selected' : ''; ?>>
            <?php echo Helpers::e($cat['name']); ?>
          </option>
        <?php endforeach; ?>
      </select>

      <button type="submit" class="btn btn-primary" style="padding: 8px 16px; font-size: 13px;">Filtrar</button>
      
      <?php if (!empty($search) || !empty($statusFilter) || $catFilter > 0): ?>
        <a href="/admin/blog" style="font-size: 13px; color: var(--text-white-40); text-decoration: underline; margin-left: 6px;">Limpar</a>
      <?php endif; ?>
    </form>

    <div style="display: flex; align-items: center; gap: 12px;">
      <a href="/blog" target="_blank" rel="noopener" style="font-size: 13px; color: var(--accent); text-decoration: none; font-weight: 600;">
        Ver blog público ↗
      </a>
    </div>
  </div>

  <!-- Tabela de Posts -->
  <div class="admin-table-container">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Título do Post</th>
          <th>Categoria</th>
          <th>Autor</th>
          <th>Status</th>
          <th>Publicação</th>
          <th style="text-align: right;">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($posts)): ?>
          <tr>
            <td colspan="6" style="text-align: center; color: var(--text-white-40); padding: 48px;">
              Nenhum artigo cadastrado ainda. Clique em <strong>Novo Artigo</strong> para começar a produzir conteúdo!
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($posts as $p): 
            $badge = Helpers::postStatusBadge($p['status']);
          ?>
            <tr>
              <td>
                <a href="/admin/blog/editar?id=<?php echo $p['id']; ?>" style="font-weight: 600; color: var(--text-white); font-size: 14px;">
                  <?php echo Helpers::e($p['title']); ?>
                </a>
                <div style="font-size: 12px; color: var(--text-white-40); margin-top: 2px;">
                  /blog/<?php echo Helpers::e($p['slug']); ?>
                </div>
              </td>
              <td>
                <span style="font-size: 13px; color: var(--text-white-90);">
                  <?php echo Helpers::e($p['category_name'] ?? 'Sem Categoria'); ?>
                </span>
              </td>
              <td style="font-size: 13px; color: var(--text-white-65);">
                <?php echo Helpers::e($p['author_name'] ?? 'Equipe feito.'); ?>
              </td>
              <td>
                <span class="admin-badge" style="background: <?php echo $badge['bg']; ?>; color: <?php echo $badge['color']; ?>; border: 1px solid <?php echo $badge['border']; ?>;">
                  <?php echo $badge['label']; ?>
                </span>
              </td>
              <td style="font-size: 12px; color: var(--text-white-40); white-space: nowrap;">
                <?php echo $p['published_at'] ? Helpers::formatDate($p['published_at']) : '—'; ?>
              </td>
              <td style="text-align: right;">
                <div class="admin-actions" style="justify-content: flex-end;">
                  <!-- Preview -->
                  <a href="/blog/<?php echo Helpers::e($p['slug']); ?>?preview=true" target="_blank" rel="noopener" class="admin-action-btn" title="Visualizar como ficará no site">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  </a>

                  <!-- Editar -->
                  <a href="/admin/blog/editar?id=<?php echo $p['id']; ?>" class="admin-action-btn" title="Editar post">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  </a>

                  <!-- Excluir -->
                  <form method="POST" style="margin: 0; display: inline;">
                    <?php echo Csrf::input(); ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="post_id" value="<?php echo $p['id']; ?>">
                    <button type="submit" class="admin-action-btn admin-action-btn--danger" title="Excluir" data-confirm="Tem certeza que deseja excluir permanentemente este artigo?">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

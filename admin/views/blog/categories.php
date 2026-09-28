<?php
$adminTitle = 'Categorias do Blog';

require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../../../core/Helpers.php';
require_once __DIR__ . '/../../../core/Auth.php';
require_once __DIR__ . '/../../../core/Csrf.php';
require_once __DIR__ . '/../../../core/Logger.php';

$db = Database::getInstance();
$message = '';
$messageType = '';

// Processa inclusão ou exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (Csrf::validate()) {
        $action = $_POST['action'] ?? '';

        if ($action === 'create') {
            $name = trim($_POST['name'] ?? '');
            $desc = trim($_POST['description'] ?? '');
            $slug = Helpers::slugify($name);

            if (!empty($name)) {
                $ins = $db->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
                try {
                    $ins->execute([$name, $slug, $desc]);
                    Logger::log('CATEGORY_CREATED', 'category', (int)$db->lastInsertId(), "Categoria '{$name}' criada");
                    $message = 'Categoria adicionada com sucesso!';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Já existe uma categoria com este nome ou slug.';
                    $messageType = 'error';
                }
            }
        }

        if ($action === 'delete') {
            $catId = (int)($_POST['category_id'] ?? 0);
            if ($catId > 0) {
                $del = $db->prepare("DELETE FROM categories WHERE id = ?");
                $del->execute([$catId]);
                Logger::log('CATEGORY_DELETED', 'category', $catId, "Categoria #{$catId} excluída");
                $message = 'Categoria removida com sucesso!';
                $messageType = 'success';
            }
        }
    }
}

// Busca categorias com contagem de posts
$categories = $db->query("
    SELECT c.*, COUNT(p.id) as total_posts 
    FROM categories c 
    LEFT JOIN posts p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY c.name ASC
")->fetchAll();

require_once __DIR__ . '/../layout/header.php';
?>

<?php if (!empty($message)): ?>
  <div class="admin-alert admin-alert--<?php echo $messageType; ?>" style="margin-bottom: 24px;">
    <span><?php echo Helpers::e($message); ?></span>
  </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 28px;">

  <!-- Formulário de Nova Categoria -->
  <div class="admin-card" style="padding: 24px; height: fit-content;">
    <h3 class="admin-card__title" style="margin-bottom: 18px;">Nova Categoria</h3>

    <form method="POST" action="/admin/blog/categorias" class="admin-form">
      <?php echo Csrf::input(); ?>
      <input type="hidden" name="action" value="create">

      <div>
        <label class="admin-label" for="cat-name">Nome da Categoria *</label>
        <input type="text" id="cat-name" name="name" class="admin-input" placeholder="Ex: Inteligência Artificial" required>
      </div>

      <div>
        <label class="admin-label" for="cat-desc">Descrição (opcional)</label>
        <textarea id="cat-desc" name="description" class="admin-textarea" placeholder="Breve descrição da categoria..." style="min-height: 80px;"></textarea>
      </div>

      <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; font-size: 13px;">
        Adicionar Categoria
      </button>
    </form>
  </div>

  <!-- Tabela de Categorias -->
  <div class="admin-card">
    <div class="admin-card__header">
      <h3 class="admin-card__title">Categorias Cadastradas</h3>
      <span style="font-size: 13px; color: var(--text-white-40);"><?php echo count($categories); ?> categorias</span>
    </div>

    <div class="admin-table-container">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Nome</th>
            <th>Slug</th>
            <th>Posts Vinculados</th>
            <th style="text-align: right;">Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($categories as $cat): ?>
            <tr>
              <td>
                <strong style="color: var(--text-white);"><?php echo Helpers::e($cat['name']); ?></strong>
                <?php if (!empty($cat['description'])): ?>
                  <div style="font-size: 12px; color: var(--text-white-40); margin-top: 2px;">
                    <?php echo Helpers::e($cat['description']); ?>
                  </div>
                <?php endif; ?>
              </td>
              <td style="font-size: 12px; color: var(--text-white-65);">
                /<?php echo Helpers::e($cat['slug']); ?>
              </td>
              <td>
                <span class="admin-badge" style="background: rgba(255,255,255,0.06); color: var(--text-white-90);">
                  <?php echo $cat['total_posts']; ?> posts
                </span>
              </td>
              <td style="text-align: right;">
                <form method="POST" style="margin: 0; display: inline;">
                  <?php echo Csrf::input(); ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                  <button type="submit" class="admin-action-btn admin-action-btn--danger" title="Excluir Categoria" data-confirm="Excluir esta categoria? Os posts vinculados permanecerão salvos sem categoria.">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

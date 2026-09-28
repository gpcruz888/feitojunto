<?php
require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../../../core/Helpers.php';
require_once __DIR__ . '/../../../core/Auth.php';
require_once __DIR__ . '/../../../core/Csrf.php';
require_once __DIR__ . '/../../../core/Logger.php';

$db = Database::getInstance();
$postId = (int)($_GET['id'] ?? 0);
$isEditing = $postId > 0;

$adminTitle = $isEditing ? 'Editar Artigo' : 'Novo Artigo';

// Inicializa dados do post
$post = [
    'title' => '',
    'slug' => '',
    'category_id' => '',
    'excerpt' => '',
    'content' => '',
    'cover_image' => '',
    'status' => 'RASCUNHO',
    'seo_title' => '',
    'seo_description' => '',
    'og_title' => '',
    'og_description' => '',
    'published_at' => ''
];
$postTagsStr = '';

if ($isEditing) {
    $stmt = $db->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$postId]);
    $existing = $stmt->fetch();
    if (!$existing) {
        header('Location: /admin/blog');
        exit;
    }
    $post = $existing;

    // Busca tags associadas
    $tStmt = $db->prepare("
        SELECT t.name FROM tags t 
        JOIN post_tags pt ON t.id = pt.tag_id 
        WHERE pt.post_id = ?
    ");
    $tStmt->execute([$postId]);
    $postTagsStr = implode(', ', $tStmt->fetchAll(PDO::FETCH_COLUMN));
}

$errorMessage = '';
$successMessage = '';

// Processa salvamento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::validate()) {
        $errorMessage = 'Sessão expirada. Por favor tente novamente.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $excerpt = trim($_POST['excerpt'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $status = in_array($_POST['status'] ?? '', ['RASCUNHO', 'PUBLICADO', 'ARQUIVADO']) ? $_POST['status'] : 'RASCUNHO';
        $seoTitle = trim($_POST['seo_title'] ?? '');
        $seoDesc = trim($_POST['seo_description'] ?? '');
        $ogTitle = trim($_POST['og_title'] ?? '');
        $ogDesc = trim($_POST['og_description'] ?? '');
        $coverImage = trim($_POST['cover_image'] ?? $post['cover_image']);

        // Geração automática de slug se estiver vazio
        if (empty($slug)) {
            $slug = Helpers::slugify($title);
        } else {
            $slug = Helpers::slugify($slug);
        }

        // Validação obrigatória
        if (empty($title)) {
            $errorMessage = 'O título do artigo é obrigatório.';
        } else {
            // Verifica duplicação de slug
            $checkSql = $isEditing 
                ? "SELECT COUNT(*) FROM posts WHERE slug = ? AND id != ?"
                : "SELECT COUNT(*) FROM posts WHERE slug = ?";
            $checkParams = $isEditing ? [$slug, $postId] : [$slug];
            $cStmt = $db->prepare($checkSql);
            $cStmt->execute($checkParams);

            if ((int)$cStmt->fetchColumn() > 0) {
                $slug = $slug . '-' . time();
            }

            // Upload de imagem se fornecido
            if (isset($_FILES['cover_file']) && $_FILES['cover_file']['error'] === UPLOAD_ERR_OK) {
                $f = $_FILES['cover_file'];
                $allowed = ['image/jpeg', 'image/png', 'image/webp'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $f['tmp_name']);
                finfo_close($finfo);

                if (in_array($mime, $allowed, true) && $f['size'] <= 5 * 1024 * 1024) {
                    $ext = match ($mime) {
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'image/webp' => 'webp',
                        default => 'jpg'
                    };
                    $targetDir = __DIR__ . '/../../../uploads/blog';
                    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
                    $newFilename = 'cover_' . bin2hex(random_bytes(16)) . '.' . $ext;
                    if (move_uploaded_file($f['tmp_name'], $targetDir . '/' . $newFilename)) {
                        $coverImage = '/uploads/blog/' . $newFilename;
                    }
                }
            }

            // Data de publicação
            $publishedAt = $post['published_at'];
            if ($status === 'PUBLICADO' && empty($publishedAt)) {
                $publishedAt = date('Y-m-d H:i:s');
            }

            if ($isEditing) {
                $stmt = $db->prepare("
                    UPDATE posts SET 
                        title = ?, slug = ?, category_id = ?, excerpt = ?, content = ?,
                        cover_image = ?, status = ?, seo_title = ?, seo_description = ?,
                        og_title = ?, og_description = ?, published_at = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ");
                $stmt->execute([
                    $title, $slug, $categoryId, $excerpt, $content,
                    $coverImage, $status, $seoTitle, $seoDesc,
                    $ogTitle, $ogDesc, $publishedAt, $postId
                ]);

                Logger::log('POST_UPDATED', 'post', $postId, "Post '{$title}' atualizado");
                $successMessage = 'Artigo atualizado com sucesso!';
            } else {
                $stmt = $db->prepare("
                    INSERT INTO posts (
                        author_id, category_id, title, slug, excerpt, content,
                        cover_image, status, seo_title, seo_description,
                        og_title, og_description, published_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    Auth::id(), $categoryId, $title, $slug, $excerpt, $content,
                    $coverImage, $status, $seoTitle, $seoDesc,
                    $ogTitle, $ogDesc, $publishedAt
                ]);
                $postId = (int)$db->lastInsertId();

                Logger::log('POST_CREATED', 'post', $postId, "Novo post '{$title}' criado");
                header("Location: /admin/blog/editar?id={$postId}&created=1");
                exit;
            }

            // Processa tags
            if (isset($_POST['tags'])) {
                $tagsRaw = explode(',', $_POST['tags']);
                $db->prepare("DELETE FROM post_tags WHERE post_id = ?")->execute([$postId]);

                foreach ($tagsRaw as $rawTag) {
                    $tName = trim($rawTag);
                    if (empty($tName)) continue;
                    $tSlug = Helpers::slugify($tName);

                    $db->prepare("INSERT OR IGNORE INTO tags (name, slug) VALUES (?, ?)")->execute([$tName, $tSlug]);
                    $tagId = $db->query("SELECT id FROM tags WHERE slug = " . $db->quote($tSlug))->fetchColumn();

                    if ($tagId) {
                        $db->prepare("INSERT OR IGNORE INTO post_tags (post_id, tag_id) VALUES (?, ?)")->execute([$postId, $tagId]);
                    }
                }
            }

            // Recarrega post
            $stmt = $db->prepare("SELECT * FROM posts WHERE id = ?");
            $stmt->execute([$postId]);
            $post = $stmt->fetch();
        }
    }
}

if (isset($_GET['created'])) {
    $successMessage = 'Artigo criado com sucesso!';
}

// Categorias disponíveis
$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

$topbarActions = '
  <div style="display: flex; gap: 10px;">
    <a href="/admin/blog" class="btn btn--glass" style="padding: 8px 16px; font-size: 13px;">← Voltar</a>
    ' . ($isEditing ? '<a href="/blog/' . Helpers::e($post['slug']) . '?preview=true" target="_blank" rel="noopener" class="btn btn--glass" style="padding: 8px 16px; font-size: 13px;">Visualizar no Site ↗</a>' : '') . '
  </div>
';

require_once __DIR__ . '/../layout/header.php';
?>

<?php if (!empty($errorMessage)): ?>
  <div class="admin-alert admin-alert--error" style="margin-bottom: 24px;">
    <span><?php echo Helpers::e($errorMessage); ?></span>
  </div>
<?php endif; ?>

<?php if (!empty($successMessage)): ?>
  <div class="admin-alert admin-alert--success" style="margin-bottom: 24px;">
    <span><?php echo Helpers::e($successMessage); ?></span>
  </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="admin-form">
  <?php echo Csrf::input(); ?>

  <div style="display: grid; grid-template-columns: 2.2fr 1fr; gap: 28px;">

    <!-- Coluna Principal: Conteúdo do Post -->
    <div>
      <div class="admin-card" style="padding: 28px;">
        <!-- Título -->
        <div style="margin-bottom: 20px;">
          <label class="admin-label" for="post-title">Título do Artigo *</label>
          <input 
            type="text" 
            id="post-title" 
            name="title" 
            class="admin-input" 
            placeholder="Ex: Como a arquitetura web moderna impulsiona conversões" 
            value="<?php echo Helpers::e($post['title']); ?>" 
            required 
            style="font-size: 16px; font-weight: 600;"
          >
        </div>

        <!-- Slug -->
        <div style="margin-bottom: 20px;">
          <label class="admin-label" for="post-slug">Slug (URL amigável)</label>
          <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 13px; color: var(--text-white-40);">/blog/</span>
            <input 
              type="text" 
              id="post-slug" 
              name="slug" 
              class="admin-input" 
              placeholder="como-arquitetura-web-moderna-impulsiona-conversoes" 
              value="<?php echo Helpers::e($post['slug']); ?>"
            >
          </div>
          <div class="admin-input-hint">Gerado automaticamente a partir do título. Altere apenas se necessário.</div>
        </div>

        <!-- Resumo -->
        <div style="margin-bottom: 20px;">
          <label class="admin-label" for="post-excerpt">Resumo / Subtítulo</label>
          <textarea 
            id="post-excerpt" 
            name="excerpt" 
            class="admin-textarea" 
            placeholder="Uma breve introdução do artigo para exibição em cards e redes sociais..."
            style="min-height: 80px;"
          ><?php echo Helpers::e($post['excerpt']); ?></textarea>
        </div>

        <!-- Conteúdo Principal -->
        <div style="margin-bottom: 20px;">
          <label class="admin-label" for="post-content">Conteúdo Completo (HTML ou Markdown)</label>
          <textarea 
            id="post-content" 
            name="content" 
            class="admin-textarea" 
            placeholder="Escreva seu artigo aqui. Suporta tags HTML como <h2>, <p>, <blockquote>, <ul>, etc."
            style="min-height: 380px; font-family: monospace; font-size: 14px; line-height: 1.6;"
          ><?php echo Helpers::e($post['content']); ?></textarea>
        </div>
      </div>

      <!-- Caixa de SEO & Metatags -->
      <div class="admin-card" style="padding: 28px;">
        <div class="admin-card__title" style="margin-bottom: 18px;">Otimização para Mecanismos de Busca (SEO)</div>

        <div style="margin-bottom: 16px;">
          <label class="admin-label" for="seo-title">SEO Title</label>
          <input 
            type="text" 
            id="seo-title" 
            name="seo_title" 
            class="admin-input" 
            placeholder="Título personalizado para o Google (recomendado até 60 caracteres)"
            value="<?php echo Helpers::e($post['seo_title']); ?>"
          >
        </div>

        <div style="margin-bottom: 16px;">
          <label class="admin-label" for="seo-desc">Meta Description</label>
          <textarea 
            id="seo-desc" 
            name="seo_description" 
            class="admin-textarea" 
            placeholder="Resumo que aparecerá nos resultados de busca do Google (150-160 caracteres)..."
            style="min-height: 70px;"
          ><?php echo Helpers::e($post['seo_description']); ?></textarea>
        </div>

        <!-- Google Search Live Preview -->
        <div style="font-size: 11px; color: var(--text-white-40); text-transform: uppercase; font-weight: 700; margin-top: 20px;">
          Prévia nos Resultados do Google
        </div>
        <div class="admin-seo-preview">
          <div class="admin-seo-preview__url" id="preview-url">
            https://feitojunto.com/blog/<?php echo Helpers::e($post['slug'] ?: 'artigo-exemplo'); ?>
          </div>
          <div class="admin-seo-preview__title" id="preview-title">
            <?php echo Helpers::e($post['seo_title'] ?: ($post['title'] ?: 'Título do Artigo')); ?>
          </div>
          <div class="admin-seo-preview__desc" id="preview-desc">
            <?php echo Helpers::e($post['seo_description'] ?: ($post['excerpt'] ?: 'Descrição do artigo nos mecanismos de busca...')); ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Coluna Lateral: Publicação, Categoria, Imagem -->
    <div>
      <!-- Card de Ações de Publicação -->
      <div class="admin-card" style="padding: 24px;">
        <h3 class="admin-card__title" style="margin-bottom: 16px;">Publicação</h3>

        <div style="margin-bottom: 16px;">
          <label class="admin-label" for="post-status">Status do Artigo</label>
          <select id="post-status" name="status" class="admin-select">
            <option value="RASCUNHO" <?php echo $post['status'] === 'RASCUNHO' ? 'selected' : ''; ?>>Rascunho</option>
            <option value="PUBLICADO" <?php echo $post['status'] === 'PUBLICADO' ? 'selected' : ''; ?>>Publicado</option>
            <option value="ARQUIVADO" <?php echo $post['status'] === 'ARQUIVADO' ? 'selected' : ''; ?>>Arquivado</option>
          </select>
        </div>

        <div style="margin-bottom: 20px;">
          <label class="admin-label" for="post-category">Categoria</label>
          <select id="post-category" name="category_id" class="admin-select">
            <option value="">Selecione uma categoria...</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?php echo $cat['id']; ?>" <?php echo $post['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                <?php echo Helpers::e($cat['name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div style="margin-bottom: 24px;">
          <label class="admin-label" for="post-tags">Tags (separadas por vírgula)</label>
          <input 
            type="text" 
            id="post-tags" 
            name="tags" 
            class="admin-input" 
            placeholder="UI, Frontend, Performance, UX"
            value="<?php echo Helpers::e($postTagsStr); ?>"
          >
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px; font-weight: 700;">
          <?php echo $isEditing ? 'Salvar Alterações' : 'Criar Artigo'; ?>
        </button>
      </div>

      <!-- Imagem de Capa -->
      <div class="admin-card" style="padding: 24px;">
        <h3 class="admin-card__title" style="margin-bottom: 16px;">Imagem de Capa</h3>

        <div style="margin-bottom: 14px;">
          <?php if (!empty($post['cover_image'])): ?>
            <img 
              id="post-cover-preview" 
              src="<?php echo Helpers::e($post['cover_image']); ?>" 
              alt="Capa do artigo" 
              style="width: 100%; height: 160px; object-fit: cover; border-radius: var(--radius-md); border: 1px solid var(--hairline); margin-bottom: 12px;"
            >
          <?php else: ?>
            <img 
              id="post-cover-preview" 
              src="" 
              alt="Preview da capa" 
              style="display: none; width: 100%; height: 160px; object-fit: cover; border-radius: var(--radius-md); border: 1px solid var(--hairline); margin-bottom: 12px;"
            >
          <?php endif; ?>

          <label class="admin-label" for="post-cover-file">Fazer upload de arquivo</label>
          <input type="file" id="post-cover-file" name="cover_file" accept="image/png, image/jpeg, image/webp" class="admin-input" style="padding: 8px;">
          <div class="admin-input-hint">Formatos: JPG, PNG, WEBP (Máx 5MB)</div>
        </div>

        <div>
          <label class="admin-label" for="post-cover-url">Ou link direto de imagem</label>
          <input 
            type="text" 
            id="post-cover-url" 
            name="cover_image" 
            class="admin-input" 
            placeholder="https://..." 
            value="<?php echo Helpers::e($post['cover_image']); ?>"
          >
        </div>
      </div>
    </div>

  </div>
</form>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

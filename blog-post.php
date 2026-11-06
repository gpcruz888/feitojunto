<?php
require_once __DIR__ . '/core/Security.php';
require_once __DIR__ . '/core/Env.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Helpers.php';
require_once __DIR__ . '/core/Auth.php';

$slug = trim((string)($_GET['slug'] ?? ''));
if (empty($slug)) {
    header('Location: /blog');
    exit;
}

$db = Database::getInstance();

$stmt = $db->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug, a.name as author_name 
    FROM posts p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN admins a ON p.author_id = a.id 
    WHERE p.slug = ? 
    LIMIT 1
");
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    $pageTitle = 'Artigo não encontrado | feito.';
    $pageDescription = 'O artigo que você procura não foi encontrado.';
    require_once __DIR__ . '/includes/head.php';
    require_once __DIR__ . '/includes/header.php';
    echo "
    <div class='container' style='padding: 160px 20px; text-align: center;'>
      <h1 style='font-size: 32px;'>Artigo não encontrado<span class='dot-orange'>.</span></h1>
      <p style='color: var(--text-white-65); margin: 16px 0 24px 0;'>O artigo pode ter sido removido ou o link está incorreto.</p>
      <a href='/blog' class='btn btn-primary'>Ver todos os artigos</a>
    </div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Se o post não estiver publicado, exige que seja admin autenticado
$isPreview = false;
if ($post['status'] !== 'PUBLICADO') {
    if (!Auth::check()) {
        http_response_code(404);
        header('Location: /blog');
        exit;
    }
    $isPreview = true;
}

// Meta tags dinâmicas
$pageTitle = !empty($post['seo_title']) ? $post['seo_title'] : "{$post['title']} | feito.";
$pageDescription = !empty($post['seo_description']) ? $post['seo_description'] : ($post['excerpt'] ?? 'Artigo publicado no blog da feito.');
$isHome = false;
$bodyClass = 'blog-post-page';

// Tags vinculadas
$tStmt = $db->prepare("
    SELECT t.name FROM tags t 
    JOIN post_tags pt ON t.id = pt.tag_id 
    WHERE pt.post_id = ?
");
$tStmt->execute([$post['id']]);
$postTags = $tStmt->fetchAll(PDO::FETCH_COLUMN);

// Tempo de leitura estimado (200 palavras por minuto)
$wordCount = str_word_count(strip_tags($post['content']));
$readTime = max(1, ceil($wordCount / 200));

require_once __DIR__ . '/includes/head.php';
require_once __DIR__ . '/includes/header.php';
?>

  <!-- Reading Progress Bar -->
  <div class="reading-progress-bar" id="reading-progress"></div>

  <?php if ($isPreview): ?>
    <div style="background: var(--accent); color: #ffffff; text-align: center; padding: 12px 16px; font-size: 13px; font-weight: 700; position: sticky; top: 0; z-index: 1000; display: flex; justify-content: center; align-items: center; gap: 12px;">
      <span>• MODO DE PRÉ-VISUALIZAÇÃO • Este artigo está como <strong><?php echo Helpers::e($post['status']); ?></strong> e não está público.</span>
      <a href="/admin/blog/editar?id=<?php echo (int)$post['id']; ?>" style="background: #ffffff; color: #090909; padding: 4px 12px; border-radius: var(--radius-pill); text-decoration: none; font-size: 11px; font-weight: 700;">Voltar ao Editor</a>
    </div>
  <?php endif; ?>

  <!-- ==================== POST HEADER / HERO ==================== -->
  <main class="container">
    <article class="blog-article">
      
      <a href="/blog" class="blog-article__back">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
        <span>voltar para todos os artigos</span>
      </a>

      <?php if (!empty($post['category_name'])): ?>
        <div class="blog-article__cat">
          <?php echo Helpers::e($post['category_name']); ?>
        </div>
      <?php endif; ?>

      <h1 class="blog-article__title">
        <?php echo Helpers::e($post['title']); ?><span class="dot-orange">.</span>
      </h1>

      <?php if (!empty($post['excerpt'])): ?>
        <p class="blog-article__excerpt">
          <?php echo Helpers::e($post['excerpt']); ?>
        </p>
      <?php endif; ?>

      <!-- Post Meta Bar with Author & Share Actions -->
      <div class="blog-article__meta">
        <div class="blog-article__meta-left">
          <span>Por <strong class="blog-article__author-name"><?php echo Helpers::e($post['author_name'] ?? 'Equipe feito.'); ?></strong></span>
          <span class="blog-article__meta-dot">•</span>
          <span><?php echo Helpers::formatDate($post['published_at'] ?: $post['created_at'], false); ?></span>
          <span class="blog-article__meta-dot">•</span>
          <span><?php echo $readTime; ?> min de leitura</span>
        </div>

        <div class="blog-article__share">
          <span class="blog-article__share-label">compartilhar:</span>
          <?php
            $appBaseUrl = rtrim((string)Env::get('APP_URL', 'https://feitojunto.com'), '/');
            $currentUrl = $appBaseUrl . '/blog/' . rawurlencode((string)$post['slug']);
            $encodedUrl = urlencode($currentUrl);
            $encodedTitle = urlencode((string)$post['title']);
          ?>
          <a href="https://api.whatsapp.com/send?text=<?php echo $encodedTitle . '%20' . $encodedUrl; ?>" target="_blank" rel="noopener" class="blog-article__share-btn" title="Compartilhar no WhatsApp" aria-label="WhatsApp">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
          </a>
          <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $encodedUrl; ?>" target="_blank" rel="noopener" class="blog-article__share-btn" title="Compartilhar no LinkedIn" aria-label="LinkedIn">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
          </a>
          <button type="button" class="blog-article__share-btn" onclick="copyArticleUrl()" title="Copiar link do artigo" aria-label="Copiar link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
          </button>
        </div>
      </div>

      <!-- Cover Image -->
      <?php if (!empty($post['cover_image'])): ?>
        <?php
          $coverSrc = Helpers::coverUrl($post['cover_image']);
        ?>
        <?php if (!empty($coverSrc)): ?>
        <div class="blog-article__cover">
          <img src="<?php echo Helpers::e($coverSrc); ?>" alt="<?php echo Helpers::e($post['title']); ?>" loading="eager">
        </div>
        <?php endif; ?>
      <?php endif; ?>

      <!-- Post Content -->
      <div class="post-content">
        <?php 
        if (strip_tags((string)$post['content']) !== (string)$post['content']) {
            echo Security::sanitizeHtml((string)$post['content']);
        } else {
            echo nl2br(Helpers::e((string)$post['content']));
        }
        ?>
      </div>

      <!-- Tags -->
      <?php if (!empty($postTags)): ?>
        <div class="blog-article__tags">
          <span class="blog-article__tags-label">Tags:</span>
          <?php foreach ($postTags as $tag): ?>
            <span class="blog-article__tag-pill">
              #<?php echo Helpers::e($tag); ?>
            </span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Final Post Conversion CTA Box -->
      <div class="blog-article__cta-box">
        <div class="blog-article__cta-badge">pronto para evoluir?</div>
        <h2 class="blog-article__cta-title">Vamos construir seu próximo projeto feito junto<span class="dot-orange">.</span></h2>
        <p class="blog-article__cta-sub">
          Transformamos a presença digital de empresas com tecnologia de alta performance, design impecável e estratégia orientada a resultados.
        </p>
        <button type="button" class="btn btn-primary" data-open-modal="project-modal" onclick="openProjectModal()">
          iniciar projeto
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
        </button>
      </div>

    </article>
  </main>

  <div class="toast-notice" id="toast-copied">Link copiado para a área de transferência!</div>

  <script>
  // Reading Progress Bar
  window.addEventListener('scroll', function() {
    var winScroll = document.body.scrollTop || document.documentElement.scrollTop;
    var height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    var scrolled = (winScroll / height) * 100;
    var bar = document.getElementById('reading-progress');
    if (bar) bar.style.width = scrolled + '%';
  });

  // Copy Link Function
  function copyArticleUrl() {
    navigator.clipboard.writeText(window.location.href).then(function() {
      var toast = document.getElementById('toast-copied');
      if (toast) {
        toast.classList.add('show');
        setTimeout(function(){ toast.classList.remove('show'); }, 3000);
      }
    });
  }
  </script>

<?php
require_once __DIR__ . '/includes/footer.php';
require_once __DIR__ . '/includes/whatsapp-fab.php';
?>
  <script src="/script.js?v=<?= time(); ?>"></script>
</body>
</html>

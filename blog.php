<?php
$pageTitle = 'blog & insights | feito.';
$pageDescription = 'Projetos, decisões, processos e aprendizados sobre design, tecnologia e estratégia digital.';
$isHome = false;
$bodyClass = 'blog-index-page';

require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Helpers.php';

$db = Database::getInstance();

$catSlug = trim($_GET['categoria'] ?? '');
$searchQuery = trim($_GET['q'] ?? '');

$where = ["p.status = 'PUBLICADO'"];
$params = [];

if (!empty($catSlug)) {
    $where[] = "c.slug = ?";
    $params[] = $catSlug;
}

if (!empty($searchQuery)) {
    $where[] = "(p.title LIKE ? OR p.excerpt LIKE ? OR p.content LIKE ?)";
    $searchWildcard = "%{$searchQuery}%";
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
}

$whereSql = "WHERE " . implode(" AND ", $where);

$query = "
    SELECT p.*, c.name as category_name, c.slug as category_slug, a.name as author_name 
    FROM posts p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN admins a ON p.author_id = a.id 
    {$whereSql} 
    ORDER BY p.published_at DESC
";
$stmt = $db->prepare($query);
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Categorias ativas
$categories = $db->query("
    SELECT c.*, COUNT(p.id) as total_posts 
    FROM categories c 
    JOIN posts p ON c.id = p.category_id AND p.status = 'PUBLICADO' 
    GROUP BY c.id 
    ORDER BY c.id ASC
")->fetchAll();

// Separação do post em Destaque (primeiro) e os Mais Artigos (restante)
$featuredPost = null;
$morePosts = [];

if (!empty($posts)) {
    // Se não for busca por texto, o primeiro é destaque
    if (empty($searchQuery)) {
        $featuredPost = $posts[0];
        $morePosts = array_slice($posts, 1);
    } else {
        $morePosts = $posts;
    }
}

// Helper para tempo de leitura
function getReadTime($content) {
    $words = str_word_count(strip_tags($content));
    return max(1, ceil($words / 200));
}

// Helper para formatar data em PT-BR curta maiúscula (ex: 08 SET 2026)
function formatShortDate($dateStr) {
    if (!$dateStr) return '';
    $timestamp = strtotime($dateStr);
    $months = [
        1 => 'JAN', 2 => 'FEV', 3 => 'MAR', 4 => 'ABR',
        5 => 'MAI', 6 => 'JUN', 7 => 'JUL', 8 => 'AGO',
        9 => 'SET', 10 => 'OUT', 11 => 'NOV', 12 => 'DEZ'
    ];
    $day = date('d', $timestamp);
    $month = $months[(int)date('n', $timestamp)] ?? date('M', $timestamp);
    $year = date('Y', $timestamp);
    return "{$day} {$month} {$year}";
}

require_once __DIR__ . '/includes/head.php';
require_once __DIR__ . '/includes/header.php';
?>

  <!-- ==================== 1. HERO DO BLOG (JOURNAL) ==================== -->
  <section class="journal-hero-section">
    <div class="container">
      <div class="journal-hero__grid">
        
        <!-- Coluna Esquerda: Texto Principal -->
        <div class="journal-hero__left reveal">
          <div class="journal-hero__tag">journal</div>
          <h1 class="journal-hero__title">
            ideias que<br>
            viraram coisa <span class="accent-word">feita<span class="dot-orange">.</span></span>
          </h1>
          <p class="journal-hero__subtitle">
            Projetos, decisões, processos e aprendizados sobre design, tecnologia e estratégia digital.
          </p>
        </div>

        <!-- Coluna Direita: Foto Desk + Notas Flutuantes -->
        <div class="journal-hero__right reveal reveal-d1">
          <div class="journal-hero__visual">
            <img src="/assets/blog/hero-desk.jpg" alt="feito. journal desk" loading="eager">
          </div>
          <div class="journal-hero__note">
            conteúdo real para um trabalho mais inteligente.
          </div>
          <div class="journal-hero__topics">
            <a href="/blog?categoria=design-e-produto" class="journal-hero__topic-item">Design</a>
            <a href="/blog?categoria=tecnologia-e-dev" class="journal-hero__topic-item">Tecnologia</a>
            <a href="/blog?categoria=negocios-e-estrategia" class="journal-hero__topic-item">Negócios</a>
            <a href="/blog" class="journal-hero__topic-item">Pessoas</a>
          </div>
        </div>

      </div>

      <!-- ==================== 2. BARRA DE FILTROS & BUSCA ==================== -->
      <div class="journal-nav reveal">
        <div class="journal-nav__tabs">
          <a href="/blog" class="journal-nav__tab <?php echo empty($catSlug) ? 'journal-nav__tab--active' : ''; ?>">
            todos
          </a>
          <?php foreach ($categories as $cat): ?>
            <a 
              href="/blog?categoria=<?php echo htmlspecialchars($cat['slug']); ?>" 
              class="journal-nav__tab <?php echo ($catSlug === $cat['slug']) ? 'journal-nav__tab--active' : ''; ?>"
            >
              <?php echo htmlspecialchars($cat['name']); ?>
            </a>
          <?php endforeach; ?>
        </div>

        <form action="/blog" method="GET" class="journal-search">
          <?php if (!empty($catSlug)): ?>
            <input type="hidden" name="categoria" value="<?php echo htmlspecialchars($catSlug); ?>">
          <?php endif; ?>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          <input type="text" name="q" placeholder="Buscar artigos..." value="<?php echo htmlspecialchars($searchQuery); ?>">
        </form>
      </div>

      <!-- ==================== 3. ARTIGO EM DESTAQUE ==================== -->
      <?php if ($featuredPost): ?>
        <?php
          $featCover = Helpers::coverUrl($featuredPost['cover_image'] ?? '');
          $featDate = formatShortDate($featuredPost['published_at'] ?: $featuredPost['created_at']);
          $featTime = getReadTime($featuredPost['content']);
          $featCat = $featuredPost['category_name'] ?? 'Design & Produto';
        ?>
        <article class="journal-featured reveal" onclick="location.href='/blog/<?php echo htmlspecialchars($featuredPost['slug']); ?>'">
          <div class="journal-featured__visual">
            <?php if (!empty($featCover)): ?>
              <img src="<?php echo htmlspecialchars($featCover); ?>" alt="<?php echo htmlspecialchars($featuredPost['title']); ?>" loading="eager">
            <?php endif; ?>
          </div>

          <div class="journal-featured__content">
            <div class="journal-featured__badge-row">
              <span class="journal-featured__badge">destaque</span>
              <span class="journal-featured__cat"><?php echo htmlspecialchars($featCat); ?></span>
            </div>

            <h2 class="journal-featured__title">
              <a href="/blog/<?php echo htmlspecialchars($featuredPost['slug']); ?>" onclick="event.stopPropagation()">
                <?php echo htmlspecialchars($featuredPost['title']); ?>
              </a>
            </h2>

            <?php if (!empty($featuredPost['excerpt'])): ?>
              <p class="journal-featured__excerpt">
                <?php echo htmlspecialchars($featuredPost['excerpt']); ?>
              </p>
            <?php endif; ?>

            <div class="journal-featured__footer">
              <span><?php echo $featDate; ?> · <?php echo $featTime; ?> MIN DE LEITURA</span>
              <a href="/blog/<?php echo htmlspecialchars($featuredPost['slug']); ?>" class="journal-featured__read" onclick="event.stopPropagation()">
                ler artigo →
              </a>
            </div>
          </div>
        </article>
      <?php endif; ?>

    </div>
  </section>

  <!-- ==================== 4. SEÇÃO "MAIS ARTIGOS" (FUNDO CLARO) ==================== -->
  <section class="journal-more-section">
    <div class="container">
      <div class="journal-more__layout">
        
        <!-- Sidebar Esquerda -->
        <aside class="journal-more__sidebar reveal">
          <div class="journal-more__sidebar-dash"></div>
          <h2 class="journal-more__sidebar-title">
            mais<br>artigos
          </h2>
          <p class="journal-more__sidebar-sub">
            Conteúdo prático e sem enrolação sobre o que a gente vive no dia a dia.
          </p>
          <a href="/blog" class="journal-more__sidebar-link">
            ver todos os artigos →
          </a>
        </aside>

        <!-- Grid da Direita -->
        <div class="journal-more__content">
          <?php if (empty($morePosts) && empty($featuredPost)): ?>
            <div style="text-align: center; padding: 60px 20px; background: #ffffff; border-radius: var(--radius-xl); border: 1px solid rgba(0,0,0,0.06);">
              <h3 style="font-size: 20px; font-weight: 700; color: var(--text-dark); margin-bottom: 8px;">Nenhum artigo encontrado</h3>
              <p style="color: var(--text-dark-65); font-size: 14px; margin-bottom: 20px;">Tente buscar por outros termos ou explore todas as categorias.</p>
              <a href="/blog" class="btn btn-primary" style="font-size: 13px;">Limpar filtros</a>
            </div>
          <?php else: ?>
            <div class="journal-more__grid">
              <?php foreach ($morePosts as $post): ?>
                <?php
                  $pCover = Helpers::coverUrl($post['cover_image'] ?? '');
                  $pDate = formatShortDate($post['published_at'] ?: $post['created_at']);
                  $pTime = getReadTime($post['content']);
                  $pCat  = $post['category_name'] ?? 'Design & Produto';
                ?>
                <article class="journal-card reveal" onclick="location.href='/blog/<?php echo htmlspecialchars($post['slug']); ?>'">
                  <div class="journal-card__visual">
                    <?php if (!empty($pCover)): ?>
                      <img src="<?php echo htmlspecialchars($pCover); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" loading="lazy">
                    <?php endif; ?>
                  </div>

                  <div class="journal-card__body">
                    <span class="journal-card__cat"><?php echo htmlspecialchars($pCat); ?></span>
                    
                    <h3 class="journal-card__title">
                      <a href="/blog/<?php echo htmlspecialchars($post['slug']); ?>" onclick="event.stopPropagation()">
                        <?php echo htmlspecialchars($post['title']); ?>
                      </a>
                    </h3>

                    <?php if (!empty($post['excerpt'])): ?>
                      <p class="journal-card__excerpt">
                        <?php echo htmlspecialchars($post['excerpt']); ?>
                      </p>
                    <?php endif; ?>

                    <div class="journal-card__footer">
                      <span><?php echo $pDate; ?> · <?php echo $pTime; ?> MIN</span>
                      <a href="/blog/<?php echo htmlspecialchars($post['slug']); ?>" class="journal-card__read" onclick="event.stopPropagation()">
                        ler artigo →
                      </a>
                    </div>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </section>

  <!-- ==================== 5. BANNER PRÉ-RODAPÉ ==================== -->
  <section class="journal-cta-section">
    <div class="container">
      <div class="journal-cta-card reveal">
        <div class="journal-cta-card__content">
          <div class="journal-cta-card__dash"></div>
          <h2 class="journal-cta-card__title">
            tem uma ideia<br>
            para colocar de pé<span class="dot-orange">?</span>
          </h2>
          <p class="journal-cta-card__sub">
            Vamos conversar sobre o seu projeto e entender como a feito pode ajudar.
          </p>
        </div>

        <div class="journal-cta-card__btn-wrap">
          <button type="button" class="journal-cta-btn" data-open-modal="project-modal" onclick="openProjectModal()">
            <span>iniciar projeto</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
          </button>
        </div>
      </div>
    </div>
  </section>

<?php
require_once __DIR__ . '/includes/footer.php';
require_once __DIR__ . '/includes/whatsapp-fab.php';
?>
  <script src="/script.js?v=<?= time(); ?>"></script>
</body>
</html>

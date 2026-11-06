<?php
$pageTitle = 'feito. presença digital feita do jeito certo.';
$pageDescription = 'feito. — sua presença digital, feita do jeito certo. Sites, landing pages e sistemas web desenvolvidos para empresas.';
$isHome = true;
$bodyClass = '';

// Busca os posts recentes publicados
$recentPosts = [];
try {
    require_once __DIR__ . '/core/Database.php';
    require_once __DIR__ . '/core/Schema.php';
    Schema::up();
    $db = Database::getInstance();
    $stmt = $db->prepare("
        SELECT p.id, p.title, p.slug, p.excerpt, p.cover_image, p.published_at, c.name as category_name
        FROM posts p
        LEFT JOIN categories c ON c.id = p.category_id
        WHERE p.status = 'PUBLICADO'
        ORDER BY p.published_at DESC
        LIMIT 3
    ");
    $stmt->execute();
    $recentPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $recentPosts = [];
}

require_once __DIR__ . '/includes/head.php';
require_once __DIR__ . '/includes/header.php';
?>

  <!-- ==================== HERO ==================== -->
  <section class="hero" id="hero">
    <!-- Background Video with Fade Overlay -->
    <div class="hero__video-bg" aria-hidden="true">
      <video class="hero__video-element" autoplay loop muted playsinline preload="auto">
        <source src="assets/video/hero-bg.mp4" type="video/mp4">
        <source src="assets/video/realistic-planet-earth-rotation-from-space-with-ci-2026-01-28-04-20-46-utc%20(2).mp4" type="video/mp4">
      </video>
      <div class="hero__video-overlay"></div>
    </div>

    <div class="container hero__inner">
      <!-- Left: Content -->
      <div class="hero__content">
        <h1 class="hero__title reveal">
          sua presença<br>
          digital, feita<br>
          do jeito certo<span class="dot-orange">.</span>
        </h1>
        <p class="hero__subtitle reveal reveal-d1">
          desenvolvemos produtos digitais estratégicos que conectam marcas às pessoas e geram resultados reais.
        </p>
        <div class="hero__cta-group reveal reveal-d2">
          <button type="button" class="btn btn--white" data-open-modal="project-modal" onclick="openProjectModal()">
            iniciar projeto
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
          </button>
          <a href="https://api.whatsapp.com/send/?phone=5511946898620&text=Ol%C3%A1%2C+Gabriel%21+Vim+pelo+site+do+feito+junto+e+gostaria+de+conversar+sobre+um+projeto+para+minha+empresa.&type=phone_number&app_absent=0" target="_blank" rel="noopener" class="btn btn--glass btn--whatsapp">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" fill="currentColor"><path d="M16.004 2.667A13.28 13.28 0 0 0 2.69 15.923a13.17 13.17 0 0 0 1.795 6.64L2.667 29.333l7.006-1.782A13.3 13.3 0 0 0 16.004 29.3 13.28 13.28 0 0 0 29.333 16.02 13.28 13.28 0 0 0 16.004 2.667Zm7.68 18.518c-.325.913-1.893 1.746-2.608 1.856-.715.11-1.373.325-4.612-1.014-3.24-1.34-5.267-4.685-5.425-4.9-.157-.216-1.288-1.712-1.288-3.264s.814-2.315 1.104-2.631c.29-.316.632-.395.843-.395s.421.005.605.01c.194.008.454-.074.71.542.26.632.883 2.156.961 2.313.078.157.13.341.026.551-.104.21-.157.341-.314.525-.157.184-.33.41-.473.551-.157.157-.32.327-.138.643.184.316.814 1.341 1.748 2.172 1.2 1.068 2.211 1.399 2.527 1.556.316.157.5.131.684-.079.184-.21.789-.913.999-1.229.21-.316.42-.263.71-.157.29.104 1.84.868 2.156 1.025.316.157.526.236.604.368.079.13.079.762-.246 1.672Z"/></svg>
            falar no whatsapp
          </a>
        </div>
      </div>

      <!-- Right: Floating Notebook Mockup with 3D Mouse Parallax -->
      <div class="hero__mockup-wrapper reveal-scale">
        <div class="hero__notebook-container">
          <img class="hero__notebook" src="assets/img/mockup.png" alt="Plataforma digital desenvolvido pela feito." loading="eager">
        </div>
      </div>
    </div>

    <!-- Bottom Stats Bar (Glass Island with Icon Boxes) -->
    <div class="hero__stats-wrapper container">
      <div class="hero__stats-card reveal reveal-d3">
        <!-- Stat 1 -->
        <div class="stat-card">
          <div class="stat-card__icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#FF5A00" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a4 4 0 0 0-4 4c0 3 4 6 4 6s4-3 4-6a4 4 0 0 0-4-4z"/><path d="M12 22a4 4 0 0 0 4-4c0-3-4-6-4-6s-4 3-4 6a4 4 0 0 0 4 4z"/><path d="M2 12a4 4 0 0 0 4 4c3 0 6-4 6-4s-3-4-6-4a4 4 0 0 0-4 4z"/><path d="M22 12a4 4 0 0 0-4-4c-3 0-6 4-6 4s3 4 6 4a4 4 0 0 0 4-4z"/></svg>
          </div>
          <div class="stat-card__content">
            <div class="stat-card__number" data-target="9" data-prefix="+" data-suffix=" anos">+9 anos</div>
            <div class="stat-card__label">de experiência no digital</div>
          </div>
        </div>

        <div class="stat-card__divider"></div>

        <!-- Stat 2 -->
        <div class="stat-card">
          <div class="stat-card__icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#FF5A00" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.5" fill="#FF5A00"/></svg>
          </div>
          <div class="stat-card__content">
            <div class="stat-card__number" data-target="200" data-prefix="+" data-suffix="">+200</div>
            <div class="stat-card__label">projetos realizados</div>
          </div>
        </div>

        <div class="stat-card__divider"></div>

        <!-- Stat 3 -->
        <div class="stat-card">
          <div class="stat-card__icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#FF5A00" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
          </div>
          <div class="stat-card__content">
            <div class="stat-card__number" data-target="10" data-prefix="+" data-suffix="M">+10M</div>
            <div class="stat-card__label">de cliques transformados<br>em resultados</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ==================== SERVICES — "o que podemos fazer juntos." ==================== -->
  <section class="services" id="servicos">
    <div class="container">
      <div class="services__header reveal">
        <h2 class="services__title">o que podemos<br>fazer juntos<span class="dot-orange">.</span></h2>
      </div>
      <div class="services__grid">
        <!-- 01 Sites -->
        <div class="svc-card svc-card--glass reveal">
          <div class="svc-card__top">
            <div class="svc-card__icon">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            </div>
            <div class="svc-card__num">01</div>
          </div>
          <h3 class="svc-card__title">sites</h3>
          <p class="svc-card__text">presença digital profissional, rápida e responsiva que transmite autoridade e confiança para o seu negócio.</p>
          <div class="svc-card__arrow">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 19.5 15-15m0 0H8.25m11.25 0v11.25"/></svg>
          </div>
        </div>

        <!-- 02 Landing Pages -->
        <div class="svc-card svc-card--glass reveal reveal-d1">
          <div class="svc-card__top">
            <div class="svc-card__icon">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            </div>
            <div class="svc-card__num">02</div>
          </div>
          <h3 class="svc-card__title">landing pages</h3>
          <p class="svc-card__text">páginas de alta conversão criadas para campanhas, lançamentos, produtos e serviços.</p>
          <div class="svc-card__arrow">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 19.5 15-15m0 0H8.25m11.25 0v11.25"/></svg>
          </div>
        </div>

        <!-- 03 Sistemas Web -->
        <div class="svc-card svc-card--glass reveal reveal-d2">
          <div class="svc-card__top">
            <div class="svc-card__icon">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/><line x1="14" y1="4" x2="10" y2="20"/></svg>
            </div>
            <div class="svc-card__num">03</div>
          </div>
          <h3 class="svc-card__title">sistemas web</h3>
          <p class="svc-card__text">plataformas, painéis e ferramentas sob medida desenvolvidas para processos operacionais complexos.</p>
          <div class="svc-card__arrow">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 19.5 15-15m0 0H8.25m11.25 0v11.25"/></svg>
          </div>
        </div>

        <!-- 04 E-commerce -->
        <div class="svc-card svc-card--glass reveal reveal-d3">
          <div class="svc-card__top">
            <div class="svc-card__icon">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            </div>
            <div class="svc-card__num">04</div>
          </div>
          <h3 class="svc-card__title">e-commerce</h3>
          <p class="svc-card__text">lojas virtuais completas e intuitivas, pensadas para vender mais e oferecer uma experiência melhor.</p>
          <div class="svc-card__arrow">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 19.5 15-15m0 0H8.25m11.25 0v11.25"/></svg>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ==================== CLIENTS — "mais de 200 projetos criados." ==================== -->
  <section class="clients" id="clientes">
    <div class="container">
      <div class="clients__header reveal">
        <h2 class="clients__title">mais de 200<br>projetos criados<span class="dot-orange">.</span></h2>
      </div>
    </div>

    <div class="clients__marquee-wrapper">
      <div class="clients__mask-left"></div>
      <div class="clients__mask-right"></div>
      
      <div class="clients__track">
        <!-- Group 1 -->
        <div class="clients__group">
          <div class="client-logo-item" title="Intelbras"><img class="client-logo-img" src="assets/clientes/intelbras.svg" alt="Intelbras" loading="lazy" decoding="async"></div>
          <div class="client-logo-item" title="Kawasaki"><img class="client-logo-img" src="assets/clientes/kawasaki.svg" alt="Kawasaki" loading="lazy" decoding="async"></div>
          <div class="client-logo-item" title="3 Corações"><img class="client-logo-img" src="assets/clientes/3coracoes.svg" alt="3 Corações" loading="lazy" decoding="async"></div>
          <div class="client-logo-item" title="Leveros"><img class="client-logo-img" src="assets/clientes/leveros.svg" alt="Leveros" loading="lazy" decoding="async"></div>
          <div class="client-logo-item" title="Momesso"><img class="client-logo-img" src="assets/clientes/momesso.svg" alt="Momesso" loading="lazy" decoding="async"></div>
          <div class="client-logo-item" title="Cantagallo"><img class="client-logo-img" src="assets/clientes/cantagallo.svg" alt="Cantagallo" loading="lazy" decoding="async"></div>
          <div class="client-logo-item" title="Bonini Guedes"><img class="client-logo-img" src="assets/clientes/bonini-guedes.svg" alt="Bonini Guedes" loading="lazy" decoding="async"></div>
          <div class="client-logo-item" title="DM11"><img class="client-logo-img" src="assets/clientes/dm11.svg" alt="DM11" loading="lazy" decoding="async"></div>
          <div class="client-logo-item" title="Lumiss"><img class="client-logo-img" src="assets/clientes/lumiss.svg" alt="Lumiss" loading="lazy" decoding="async"></div>
        </div>

        <!-- Group 2 (Duplicate for Seamless Infinite Marquee) -->
        <div class="clients__group" aria-hidden="true">
          <div class="client-logo-item" title="Intelbras"><img class="client-logo-img" src="assets/clientes/intelbras.svg" alt="Intelbras" loading="lazy" decoding="async"></div>
          <div class="client-logo-item" title="Kawasaki"><img class="client-logo-img" src="assets/clientes/kawasaki.svg" alt="Kawasaki" loading="lazy" decoding="async"></div>
          <div class="client-logo-item" title="3 Corações"><img class="client-logo-img" src="assets/clientes/3coracoes.svg" alt="3 Corações" loading="lazy" decoding="async"></div>
          <div class="client-logo-item" title="Leveros"><img class="client-logo-img" src="assets/clientes/leveros.svg" alt="Leveros" loading="lazy" decoding="async"></div>
          <div class="client-logo-item" title="Momesso"><img class="client-logo-img" src="assets/clientes/momesso.svg" alt="Momesso" loading="lazy" decoding="async"></div>
          <div class="client-logo-item" title="Cantagallo"><img class="client-logo-img" src="assets/clientes/cantagallo.svg" alt="Cantagallo" loading="lazy" decoding="async"></div>
          <div class="client-logo-item" title="Bonini Guedes"><img class="client-logo-img" src="assets/clientes/bonini-guedes.svg" alt="Bonini Guedes" loading="lazy" decoding="async"></div>
          <div class="client-logo-item" title="DM11"><img class="client-logo-img" src="assets/clientes/dm11.svg" alt="DM11" loading="lazy" decoding="async"></div>
          <div class="client-logo-item" title="Lumiss"><img class="client-logo-img" src="assets/clientes/lumiss.svg" alt="Lumiss" loading="lazy" decoding="async"></div>
        </div>
      </div>
    </div>
  </section>

  <!-- ==================== PROJECTS — "alguns trabalhos feitos." (Carousel) ==================== -->
  <section class="projects" id="projetos">
    <div class="container">
      <div class="projects__header">
        <h2 class="projects__title">alguns trabalhos<br>feitos<span class="dot-orange">.</span></h2>
        <a href="https://api.whatsapp.com/send/?phone=5511946898620&text=Ol%C3%A1%2C+Gabriel%21+Vim+pelo+site+do+feito+junto+e+gostaria+de+conversar+sobre+um+projeto+para+minha+empresa.&type=phone_number&app_absent=0" target="_blank" rel="noopener" class="projects__more">
          ver todos os projetos
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
        </a>
      </div>

      <article class="gestcorp-feature gestcorp-enter" aria-labelledby="gestcorp-destaque">
        <div class="gestcorp-feature__copy">
          <h3 id="gestcorp-destaque">gestcorp<span>.</span></h3>
          <p>design e desenvolvimento do site do curso de especialização da eca/usp.</p>
          <div class="gestcorp-actions">
            <a href="/projetos/gestcorp" class="btn btn--gestcorp">ver projeto</a>
            <a href="https://gestcorp.eca.usp.br/" target="_blank" rel="noopener" class="btn btn--glass">ver ao vivo</a>
          </div>
        </div>
        <img class="gestcorp-image" src="/assets/projetos/gestcorp/abertura-1200.webp"
          srcset="/assets/projetos/gestcorp/abertura-720.webp 720w, /assets/projetos/gestcorp/abertura-1200.webp 1200w, /assets/projetos/gestcorp/abertura.webp 1920w"
          sizes="(max-width: 768px) calc(100vw - 48px), (max-width: 1240px) 57vw, 700px"
          width="1200" height="645" alt="Página inicial do Gestcorp com a navegação da ECA-USP e uma fotografia em sala de aula"
          loading="lazy" decoding="async">
      </article>
    </div>

    <!-- Continuous Hardware-Accelerated Rolling Carousel (Infinite Marquee) -->
    <div class="projects__carousel-container">
      <div class="projects__carousel-mask-left"></div>
      <div class="projects__carousel-mask-right"></div>
      
      <div class="projects__marquee">
        <!-- Group 1 -->
        <div class="projects__marquee-group">
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/projeto64b96251974681.Y3JvcCwxMzQyLDEwNTprojetosMjksMprojeto.jpg" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/5fd23595077003.Y3JvcCw1NzU4LDQ1MDQsMCww.png" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/1225db246924013.Y3JvcCwyNDMyLDE5MDIsMCww.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/e6cbd5162108719.Y3JvcCwxNjE2LDEyNjQsMCww.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/06f6f697007209.Y3JvcCw4MDgsNjMyLDprojetosMprojeto.png" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/32d5e3244106701.Y3JvcCwxNjE2LDEyNjQsMCww.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/508590246924655.Y3JvcCwyMzprojetoxLDE4MDprojetosNjIsMprojeto.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/5340d9247114747.Y3JvcCwyNDMyLDE5MDIsMCww.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/56382f246918917.Y3JvcCwyMzprojetoxLDE4MDprojetosNjIsMprojeto.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/5df9e3246920423.Y3JvcCwxMzI0LDEwMzYsMzksMprojeto.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/61155projeto100794849.Y3JvcCw4NDprojetosNjU3LDc2LDg4Mg.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/7091bc250237965.Y3JvcCwyNDkxLDE5NDksMjQwLDprojeto.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/8projeto1f03249678427.Y3JvcCwxODI4LDE0MjksMCw3Ng.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/9f7projetoprojetob216803591.Y3JvcCwxMjE1LDk1MCwwLDprojeto.jpg" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/106e7b213987031.Y3JvcCw4MDgsNjMyLDprojetosMprojeto.png" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/14845c110646613.Y3JvcCw1NzU4LDQ1MDQsMCww.png" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/3e7503214446583.Y3JvcCwxOTIwLDE1MDEsMCww.png" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/4projeto67projeto4109590463.Y3JvcCw0ODE2LDM3NjcsMjc0LDMzMw.png" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/b28534249536161.Y3JvcCwxNzg1LDEzOTYsNzksMTI4.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/bc03bd114388377.Y3JvcCw1NzU4LDQ1MDQsMCww.jpg" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/c4b0cprojeto108487459.Y3JvcCw1NzU4LDQ1MDQsMCww.png" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/cprojeto222f246923697.Y3JvcCwyNDMyLDE5MDIsMCww.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/d02bbprojeto247115237.Y3JvcCwyNDMyLDE5MDIsMCwzNzM.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/d3b83c249739747.Y3JvcCw5ODEsNzY4LDIxLDprojeto.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/e97594246953845.Y3JvcCwyMTYwLDE2ODksMCw2OQ.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/ed0020253585571.Y3JvcCwxNjE2LDEyNjQsMCww.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/f3projetod43249680605.Y3JvcCwxNzU2LDEzNzQsMzprojetosMprojeto.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/f4980b113871583.Y3JvcCw1NzU4LDQ1MDQsMCww.jpg" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/projeto6b016248491217.Y3JvcCwyNDMyLDE5MDIsMCww.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
        </div>

        <!-- Group 2 (Duplicate for Seamless Infinite Loop) -->
        <div class="projects__marquee-group" aria-hidden="true">
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/projeto64b96251974681.Y3JvcCwxMzQyLDEwNTprojetosMjksMprojeto.jpg" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/5fd23595077003.Y3JvcCw1NzU4LDQ1MDQsMCww.png" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/1225db246924013.Y3JvcCwyNDMyLDE5MDIsMCww.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/e6cbd5162108719.Y3JvcCwxNjE2LDEyNjQsMCww.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/06f6f697007209.Y3JvcCw4MDgsNjMyLDprojetosMprojeto.png" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/32d5e3244106701.Y3JvcCwxNjE2LDEyNjQsMCww.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/508590246924655.Y3JvcCwyMzprojetoxLDE4MDprojetosNjIsMprojeto.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/5340d9247114747.Y3JvcCwyNDMyLDE5MDIsMCww.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/56382f246918917.Y3JvcCwyMzprojetoxLDE4MDprojetosNjIsMprojeto.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/5df9e3246920423.Y3JvcCwxMzI0LDEwMzYsMzksMprojeto.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/61155projeto100794849.Y3JvcCw4NDprojetosNjU3LDc2LDg4Mg.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/7091bc250237965.Y3JvcCwyNDkxLDE5NDksMjQwLDprojeto.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/8projeto1f03249678427.Y3JvcCwxODI4LDE0MjksMCw3Ng.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/9f7projetoprojetob216803591.Y3JvcCwxMjE1LDk1MCwwLDprojeto.jpg" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/106e7b213987031.Y3JvcCw4MDgsNjMyLDprojetosMprojeto.png" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/14845c110646613.Y3JvcCw1NzU4LDQ1MDQsMCww.png" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/3e7503214446583.Y3JvcCwxOTIwLDE1MDEsMCww.png" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/4projeto67projeto4109590463.Y3JvcCw0ODE2LDM3NjcsMjc0LDMzMw.png" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/b28534249536161.Y3JvcCwxNzg1LDEzOTYsNzksMTI4.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/bc03bd114388377.Y3JvcCw1NzU4LDQ1MDQsMCww.jpg" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/c4b0cprojeto108487459.Y3JvcCw1NzU4LDQ1MDQsMCww.png" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/cprojeto222f246923697.Y3JvcCwyNDMyLDE5MDIsMCww.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/d02bbprojeto247115237.Y3JvcCwyNDMyLDE5MDIsMCwzNzM.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/d3b83c249739747.Y3JvcCw5ODEsNzY4LDIxLDprojeto.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/e97594246953845.Y3JvcCwyMTYwLDE2ODksMCw2OQ.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/ed0020253585571.Y3JvcCwxNjE2LDEyNjQsMCww.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/f3projetod43249680605.Y3JvcCwxNzU2LDEzNzQsMzprojetosMprojeto.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/f4980b113871583.Y3JvcCw1NzU4LDQ1MDQsMCww.jpg" alt="projeto feito." loading="lazy" decoding="async"></div></div>
          <div class="proj-card"><div class="proj-card__visual"><img class="proj-card__img" src="assets/projetos/projeto6b016248491217.Y3JvcCwyNDMyLDE5MDIsMCww.webp" alt="projeto feito." loading="lazy" decoding="async"></div></div>
        </div>
      </div>
    </div>
  </section>

  <!-- ==================== PROCESS — "feito junto, do início ao fim." ==================== -->
  <section class="process" id="processo">
    <div class="container process__inner">
      <div class="process__left reveal">
        <h2 class="process__title">feito junto,<br>do início ao fim<span class="dot-orange">.</span></h2>
        <p class="process__sub">um método ágil e colaborativo para transformar objetivos em produtos digitais de alta performance.</p>
      </div>

      <div class="process__right">
        <div class="process__progress-line">
          <div class="process__progress-fill" id="process-progress"></div>
        </div>
        <div class="process__steps">
          <!-- Step 1 -->
          <div class="process-step reveal">
            <div class="process-step__num">01</div>
            <h3 class="process-step__heading">imersão & estratégia</h3>
            <p class="process-step__text">entendimento profundo do seu negócio, público, objetivos comerciais e diferenciais competitivos.</p>
          </div>
          <!-- Step 2 -->
          <div class="process-step reveal reveal-d1">
            <div class="process-step__num">02</div>
            <h3 class="process-step__heading">design & arquitetura</h3>
            <p class="process-step__text">criação de interfaces elegantes, fluxos intuitivos e protótipos focados na melhor experiência.</p>
          </div>
          <!-- Step 3 -->
          <div class="process-step reveal reveal-d2">
            <div class="process-step__num">03</div>
            <h3 class="process-step__heading">desenvolvimento ágil</h3>
            <p class="process-step__text">código limpo, responsivo, performático e otimizado para SEO, conversão e carregamento rápido.</p>
          </div>
          <!-- Step 4 -->
          <div class="process-step reveal reveal-d3">
            <div class="process-step__num">04</div>
            <h3 class="process-step__heading">entrega & suporte</h3>
            <p class="process-step__text">testes rigorosos, publicação segura, treinamento e acompanhamento contínuo pós-lançamento.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ==================== TESTIMONIAL / SOBRE ==================== -->
  <section class="testimonial" id="sobre">
    <div class="container">
      <div class="testimonial__header reveal">
        <h2 class="testimonial__title">quem já<br>fez junto<span class="dot-orange">.</span></h2>
      </div>

      <div class="testimonial__card reveal">
        <blockquote class="testimonial__quote">
          <p class="testimonial__text">"O trabalho do feito. foi essencial para a evolução da nossa presença digital. Organização, agilidade e um resultado acima do esperado."</p>
        </blockquote>

        <div class="testimonial__author">
          <div class="testimonial__author-info">
            <div class="testimonial__avatar-pill">BG</div>
            <div class="testimonial__info">
              <strong class="testimonial__name">Bruno Gabriel</strong>
              <span class="testimonial__role">Santa Maria Imobiliária</span>
            </div>
          </div>
          <div class="testimonial__arrows">
            <button type="button" class="testimonial__arrow" id="test-prev" aria-label="Depoimento anterior">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            </button>
            <button type="button" class="testimonial__arrow" id="test-next" aria-label="Próximo depoimento">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ==================== BLOG RECENTES ==================== -->
<?php if (!empty($recentPosts)): ?>
  <section class="blog-recent" id="blog-preview">
    <div class="container">
      <div class="blog-recent__inner">
        <div class="blog-recent__head-row reveal">
          <div class="blog-recent__header">
            <div class="blog-recent__tag">do blog</div>
            <h2 class="blog-recent__title">ideias que <span class="blog-recent__accent">geram resultado<span class="dot-orange">.</span></span></h2>
            <p class="blog-recent__subtitle">Conteúdo sobre design, tecnologia e estratégia digital para quem quer crescer.</p>
          </div>
          <a href="/blog" class="blog-recent__link-all">
            <span>ver todos os artigos</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
          </a>
        </div>

        <div class="blog-recent__grid">
          <?php foreach ($recentPosts as $i => $post): ?>
            <?php
              $coverUrl = '';
              if (!empty($post['cover_image'])) {
                $safeCover = Security::sanitizeCoverUrl($post['cover_image']);
                $coverUrl = htmlspecialchars($safeCover, ENT_QUOTES, 'UTF-8');
              }
              $catName  = $post['category_name'] ?? 'Blog';
              $pubDate  = $post['published_at'] ? date('d M Y', strtotime($post['published_at'])) : '';
              $excerpt  = $post['excerpt'] ? htmlspecialchars(mb_substr($post['excerpt'], 0, 130), ENT_QUOTES, 'UTF-8') . '…' : '';
              $numStr   = str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT);
            ?>
            <article class="blog-recent__card reveal reveal-d<?php echo ($i + 1); ?>" onclick="location.href='/blog/<?php echo htmlspecialchars($post['slug']); ?>'">
              <div class="blog-recent__card-img-wrap">
                <?php if ($coverUrl): ?>
                  <div class="blog-recent__card-img" style="background-image: url('<?php echo $coverUrl; ?>')"></div>
                <?php else: ?>
                  <div class="blog-recent__card-img blog-recent__card-img--placeholder">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                  </div>
                <?php endif; ?>
                <span class="blog-recent__card-img-cat"><?php echo htmlspecialchars($catName); ?></span>
              </div>
              <div class="blog-recent__card-body">
                <div class="blog-recent__card-num"><?php echo $numStr; ?></div>
                <h3 class="blog-recent__card-title">
                  <a href="/blog/<?php echo htmlspecialchars($post['slug']); ?>" onclick="event.stopPropagation()"><?php echo htmlspecialchars($post['title']); ?></a>
                </h3>
                <?php if ($excerpt): ?>
                  <p class="blog-recent__card-excerpt"><?php echo $excerpt; ?></p>
                <?php endif; ?>
                <div class="blog-recent__card-footer">
                  <span class="blog-recent__card-date"><?php echo $pubDate; ?></span>
                  <div class="blog-recent__card-arrow" aria-label="Ler artigo">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                  </div>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>
<?php endif; ?>

  <!-- ==================== CTA SECTION — "sua nova presença digital começa aqui." ==================== -->
  <section class="cta-section" id="contato">
    <div class="container">
      <div class="cta-card reveal">
        <div class="cta-card__bg-glow" aria-hidden="true"></div>
        <div class="cta-card__content">
          <h2 class="cta-card__title">
            sua nova presença<br>
            digital começa aqui<span class="dot-orange">.</span>
          </h2>
          <p class="cta-card__sub">
            Vamos planejar e construir o seu projeto juntos. Envie uma mensagem e receba uma análise e proposta personalizada.
          </p>
          <div class="cta-card__actions">
            <button type="button" class="btn btn--white" data-open-modal="project-modal" onclick="openProjectModal()">
              iniciar projeto
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
            </button>
            <a href="https://api.whatsapp.com/send/?phone=5511946898620&text=Ol%C3%A1%2C+Gabriel%21+Vim+pelo+site+do+feito+junto+e+gostaria+de+conversar+sobre+um+projeto+para+minha+empresa.&type=phone_number&app_absent=0" target="_blank" rel="noopener" class="btn btn--glass btn--whatsapp">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" fill="currentColor"><path d="M16.004 2.667A13.28 13.28 0 0 0 2.69 15.923a13.17 13.17 0 0 0 1.795 6.64L2.667 29.333l7.006-1.782A13.3 13.3 0 0 0 16.004 29.3 13.28 13.28 0 0 0 29.333 16.02 13.28 13.28 0 0 0 16.004 2.667Zm7.68 18.518c-.325.913-1.893 1.746-2.608 1.856-.715.11-1.373.325-4.612-1.014-3.24-1.34-5.267-4.685-5.425-4.9-.157-.216-1.288-1.712-1.288-3.264s.814-2.315 1.104-2.631c.29-.316.632-.395.843-.395s.421.005.605.01c.194.008.454-.074.71.542.26.632.883 2.156.961 2.313.078.157.13.341.026.551-.104.21-.157.341-.314.525-.157.184-.33.41-.473.551-.157.157-.32.327-.138.643.184.316.814 1.341 1.748 2.172 1.2 1.068 2.211 1.399 2.527 1.556.316.157.5.131.684-.079.184-.21.789-.913.999-1.229.21-.316.42-.263.71-.157.29.104 1.84.868 2.156 1.025.316.157.526.236.604.368.079.13.079.762-.246 1.672Z"/></svg>
              falar no whatsapp
            </a>
          </div>

          <div class="cta__info-row">
            <div class="cta__info-item">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
              <span>+55 11 94689-8620</span>
            </div>
            <div class="cta__info-item">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
              <span>contato@feitojunto.com</span>
            </div>
            <div class="cta__info-item">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
              <span>atendimento remoto</span>
            </div>
          </div>
        </div>

        <!-- Right: Briefing Form Container -->
        <div class="cta-card__form-container">
          <div id="modal-form-wrapper">
            <form class="modal-form" id="project-briefing-form">
              <input type="text" name="_hp" value="" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0;pointer-events:none;">
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label" for="form-nome">Seu nome <span class="req">*</span></label>
                  <input type="text" id="form-nome" name="nome" class="form-input" placeholder="Como podemos te chamar?" required>
                </div>
                <div class="form-group">
                  <label class="form-label" for="form-email">Seu e-mail corporativo / pessoal <span class="req">*</span></label>
                  <input type="email" id="form-email" name="email" class="form-input" placeholder="seuemail@empresa.com" required>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-label" for="form-whatsapp">WhatsApp com DDD <span class="req">*</span></label>
                  <input type="tel" id="form-whatsapp" name="whatsapp" class="form-input" placeholder="(11) 99999-9999" required>
                </div>
                <div class="form-group">
                  <label class="form-label" for="form-tipo">Tipo de projeto</label>
                  <select id="form-tipo" name="tipo_projeto" class="form-select">
                    <option value="Site Institucional">Site Institucional</option>
                    <option value="Landing Page de Alta Conversão">Landing Page de Alta Conversão</option>
                    <option value="Sistema Web / Painel">Sistema Web / Painel</option>
                    <option value="E-commerce">E-commerce</option>
                    <option value="Redesign / Otimização">Redesign / Otimização</option>
                    <option value="Outro">Outro</option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label" for="form-orcamento">Investimento / Prazo</label>
                <input type="text" id="form-orcamento" name="orcamento" class="form-input" placeholder="Ex: R$ 5k a R$ 15k / Início imediato">
              </div>

              <div class="form-group">
                <label class="form-label" for="form-msg">Conte um pouco sobre o projeto</label>
                <textarea id="form-msg" name="mensagem" class="form-textarea" placeholder="Descreva brevemente o que você precisa alcançar..."></textarea>
              </div>

              <button type="submit" class="btn-primary form-submit-btn">
                enviar briefing &amp; iniciar projeto
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
              </button>
            </form>
          </div>

          <div id="modal-success-screen" class="modal-success">
            <div class="modal-success__icon">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h3 class="modal-success__title">Briefing enviado com sucesso!</h3>
            <p class="modal-success__desc">Nossa equipe entrará em contato em menos de 24 horas.</p>
            
            <div class="modal-success__actions">
              <a href="https://api.whatsapp.com/send/?phone=5511946898620" target="_blank" rel="noopener" class="btn-primary" id="modal-wa-success">
                falar agora no WhatsApp
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
              </a>
              <button type="button" class="modal-success__btn-reset" id="modal-reset-btn">enviar outro projeto</button>
            </div>
          </div>
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

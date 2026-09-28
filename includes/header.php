<?php
$isHome = isset($isHome) ? $isHome : true;
$homeUrl = '/';
$navPrefix = $isHome ? '' : '/';
?>
  <!-- ==================== HEADER — Floating Pill Navbar ==================== -->
  <header class="header" id="header">
    <div class="header__bar">
      <a href="<?php echo $homeUrl; ?>" class="header__logo" aria-label="feito. início">
        <img src="/assets/feitojunto logo/1.png" alt="feito.">
      </a>
      <nav class="header__nav">
        <ul class="header__links">
          <li><a href="<?php echo $navPrefix; ?>#projetos" class="header__link">projetos</a></li>
          <li><a href="<?php echo $navPrefix; ?>#servicos" class="header__link">serviços</a></li>
          <li><a href="<?php echo $navPrefix; ?>#processo" class="header__link">processo</a></li>
          <li><a href="<?php echo $navPrefix; ?>#sobre" class="header__link">sobre</a></li>
          <li><a href="/blog" class="header__link">blog</a></li>
        </ul>
        <button type="button" class="header__cta" data-open-modal="project-modal" onclick="openProjectModal()">
          <span>iniciar projeto</span>
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
        </button>
      </nav>
      <button class="header__toggle" id="mobile-toggle" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </header>

  <!-- Mobile Menu -->
  <div class="mobile-menu" id="mobile-menu">
    <a href="<?php echo $navPrefix; ?>#projetos" data-mob>projetos</a>
    <a href="<?php echo $navPrefix; ?>#servicos" data-mob>serviços</a>
    <a href="<?php echo $navPrefix; ?>#processo" data-mob>processo</a>
    <a href="<?php echo $navPrefix; ?>#sobre" data-mob>sobre</a>
    <a href="/blog" data-mob>blog</a>
    <a href="/politica-de-privacidade" data-mob>privacidade</a>
    <button type="button" data-mob class="btn btn--white" data-open-modal="project-modal" onclick="openProjectModal()" style="margin-top:12px;">iniciar projeto</button>
  </div>

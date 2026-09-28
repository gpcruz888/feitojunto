<?php
$isHome = isset($isHome) ? $isHome : true;
$homeUrl = '/';
$navPrefix = $isHome ? '' : '/';
?>
  <!-- ==================== FOOTER ==================== -->
  <footer class="footer">
    <div class="container footer__inner">
      <div class="footer__left">
        <a href="<?php echo $homeUrl; ?>" class="footer__logo">
          <img src="/assets/feitojunto logo/1.png" alt="feito.">
        </a>
        <span class="footer__copy">© <?php echo date('Y'); ?> feito. CNPJ 65.988.531/0001-80 — todos os direitos reservados.</span>
      </div>

      <nav class="footer__links">
        <a href="<?php echo $navPrefix; ?>#projetos" class="footer__link">projetos</a>
        <a href="<?php echo $navPrefix; ?>#servicos" class="footer__link">serviços</a>
        <a href="<?php echo $navPrefix; ?>#processo" class="footer__link">processo</a>
        <a href="<?php echo $navPrefix; ?>#sobre" class="footer__link">sobre</a>
        <a href="/blog" class="footer__link">blog</a>
        <a href="<?php echo $navPrefix; ?>#contato" class="footer__link">contato</a>
        <a href="/politica-de-privacidade" class="footer__link">privacidade</a>
      </nav>

      <div class="footer__socials">
        <a href="https://www.instagram.com/feitojunto_" target="_blank" rel="noopener" class="footer__social" aria-label="Instagram">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="5"/><circle cx="17.5" cy="6.5" r="1.5" fill="currentColor" stroke="none"/></svg>
        </a>
      </div>
    </div>
  </footer>

  <!-- Analytics Tracker: registra visita de forma assíncrona e anônima (LGPD) -->
  <script>
  (function(){
    try {
      var sid = sessionStorage.getItem('_fsid');
      if (!sid) {
        sid = Math.random().toString(36).slice(2) + Date.now().toString(36);
        sessionStorage.setItem('_fsid', sid);
      }
      setTimeout(function(){
        fetch('/api/track', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({
            url: window.location.href,
            referrer: document.referrer || '',
            session_id: sid
          })
        }).catch(function(){});
      }, 500);
    } catch(e) {}
  })();
  </script>


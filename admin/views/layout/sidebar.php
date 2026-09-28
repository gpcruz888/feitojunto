<?php
require_once __DIR__ . '/../../../core/Auth.php';
require_once __DIR__ . '/../../../core/Database.php';

$currentRoute = $currentRoute ?? 'dashboard';
$user = Auth::user();
$isSuper = Auth::isSuperAdmin();

// Busca contagem de leads novos para o badge dinâmico
$newLeadsCount = 0;
try {
    $db = Database::getInstance();
    $stmt = $db->query("SELECT COUNT(*) FROM leads WHERE status = 'NOVO'");
    $newLeadsCount = (int)$stmt->fetchColumn();
} catch (Exception $e) {
    // Silencia em caso de primeira inicialização
}

// Iniciais do usuário
$initials = 'AD';
if (!empty($user['name'])) {
    $parts = explode(' ', trim($user['name']));
    $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
}
?>
<div id="admin-sidebar-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 95;"></div>

<aside class="admin-sidebar">
  <div class="admin-sidebar__header">
    <a href="/admin/dashboard" class="admin-sidebar__logo">
      <img src="/assets/feitojunto logo/1.png" alt="feito.">
    </a>
    <span class="admin-sidebar__badge">cms</span>
  </div>

  <nav class="admin-sidebar__nav">
    <div>
      <div class="admin-nav__group-label">Visão Geral</div>
      <ul class="admin-nav__list">
        <li>
          <a href="/admin/dashboard" class="admin-nav__link <?php echo $currentRoute === 'dashboard' ? 'is-active' : ''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Dashboard
          </a>
        </li>
      </ul>
    </div>

    <div>
      <div class="admin-nav__group-label">Comercial</div>
      <ul class="admin-nav__list">
        <li>
          <a href="/admin/leads" class="admin-nav__link <?php echo str_starts_with($currentRoute, 'leads') ? 'is-active' : ''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Leads
            <?php if ($newLeadsCount > 0): ?>
              <span class="admin-nav__counter"><?php echo $newLeadsCount; ?></span>
            <?php endif; ?>
          </a>
        </li>
      </ul>
    </div>

    <div>
      <div class="admin-nav__group-label">Conteúdo & Blog</div>
      <ul class="admin-nav__list">
        <li>
          <a href="/admin/blog" class="admin-nav__link <?php echo $currentRoute === 'blog' ? 'is-active' : ''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            Todos os Posts
          </a>
        </li>
        <li>
          <a href="/admin/blog/novo" class="admin-nav__link <?php echo $currentRoute === 'blog/novo' ? 'is-active' : ''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Novo Artigo
          </a>
        </li>
        <li>
          <a href="/admin/blog/categorias" class="admin-nav__link <?php echo $currentRoute === 'blog/categorias' ? 'is-active' : ''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
            Categorias
          </a>
        </li>
      </ul>
    </div>

    <?php if ($isSuper): ?>
    <div>
      <div class="admin-nav__group-label">Sistema & Segurança</div>
      <ul class="admin-nav__list">
        <li>
          <a href="/admin/admins" class="admin-nav__link <?php echo str_starts_with($currentRoute, 'admins') ? 'is-active' : ''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Administradores
          </a>
        </li>
        <li>
          <a href="/admin/configuracoes" class="admin-nav__link <?php echo $currentRoute === 'configuracoes' ? 'is-active' : ''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            Configurações
          </a>
        </li>
        <li>
          <a href="/admin/logs" class="admin-nav__link <?php echo $currentRoute === 'logs' ? 'is-active' : ''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            Logs de Auditoria
          </a>
        </li>
      </ul>
    </div>
    <?php endif; ?>
  </nav>

  <div class="admin-sidebar__footer">
    <div class="admin-user-card">
      <div class="admin-user-card__avatar">
        <?php echo Helpers::e($initials); ?>
      </div>
      <div class="admin-user-card__info">
        <div class="admin-user-card__name"><?php echo Helpers::e($user['name'] ?? 'Admin'); ?></div>
        <div class="admin-user-card__role"><?php echo Helpers::e($user['role'] ?? 'Editor'); ?></div>
      </div>
      <form action="/admin/logout" method="POST" style="margin: 0;">
        <?php echo Csrf::input(); ?>
        <button type="submit" class="admin-logout-btn" title="Encerrar sessão" data-confirm="Deseja realmente sair?">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </button>
      </form>
    </div>
  </div>
</aside>

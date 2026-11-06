<?php
$adminTitle = 'Dashboard';

require_once dirname(__DIR__, 2) . '/core/Database.php';
require_once dirname(__DIR__, 2) . '/core/Helpers.php';
require_once dirname(__DIR__, 2) . '/core/Auth.php';

Auth::requireLogin();

$db = Database::getInstance();

// 1. Estatísticas de Leads
$leadStats = ['total' => 0, 'novos' => 0, 'em_contato' => 0, 'convertidos' => 0];
try {
    $stmt = $db->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'NOVO' THEN 1 ELSE 0 END) as novos,
            SUM(CASE WHEN status = 'EM_CONTATO' THEN 1 ELSE 0 END) as em_contato,
            SUM(CASE WHEN status = 'CONVERTIDO' THEN 1 ELSE 0 END) as convertidos
        FROM leads
    ");
    $res = $stmt->fetch();
    if ($res) {
        $leadStats['total']       = (int)$res['total'];
        $leadStats['novos']       = (int)$res['novos'];
        $leadStats['em_contato']  = (int)$res['em_contato'];
        $leadStats['convertidos'] = (int)$res['convertidos'];
    }
} catch (Exception $e) {}

// 2. Estatísticas do Blog
$blogStats = ['total' => 0, 'publicados' => 0, 'rascunhos' => 0];
try {
    $stmt = $db->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'PUBLICADO' THEN 1 ELSE 0 END) as publicados,
            SUM(CASE WHEN status = 'RASCUNHO' THEN 1 ELSE 0 END) as rascunhos
        FROM posts
    ");
    $res = $stmt->fetch();
    if ($res) {
        $blogStats['total']      = (int)$res['total'];
        $blogStats['publicados'] = (int)$res['publicados'];
        $blogStats['rascunhos']  = (int)$res['rascunhos'];
    }
} catch (Exception $e) {}

// 3. Últimos Leads Recebidos (máx 5)
$recentLeads = [];
try {
    $stmt = $db->query("SELECT * FROM leads ORDER BY created_at DESC LIMIT 5");
    $recentLeads = $stmt->fetchAll();
} catch (Exception $e) {}

// 4. Últimos Logs de Atividade
$recentLogs = [];
try {
    $stmt = $db->query("
        SELECT l.*, a.name as admin_name 
        FROM audit_logs l 
        LEFT JOIN admins a ON l.admin_id = a.id 
        ORDER BY l.created_at DESC 
        LIMIT 6
    ");
    $recentLogs = $stmt->fetchAll();
} catch (Exception $e) {}

// ─── 5. Analytics de Visitas ────────────────────────────────────────────────
$analytics = [
    'total_views'      => 0,
    'today_views'      => 0,
    'unique_today'     => 0,
    'unique_7d'        => 0,
    'unique_30d'       => 0,
    'views_7d'         => 0,
    'views_30d'        => 0,
    'top_pages'        => [],
    'chart_7d'         => [],
];

try {
    // Total geral de pageviews
    $r = $db->query("SELECT COUNT(*) as c FROM page_views")->fetch();
    $analytics['total_views'] = (int)$r['c'];

    // Hoje
    $r = $db->query("SELECT COUNT(*) as c FROM page_views WHERE date(viewed_at) = date('now','localtime')")->fetch();
    $analytics['today_views'] = (int)$r['c'];

    // Visitantes únicos hoje (por ip_hash)
    $r = $db->query("SELECT COUNT(DISTINCT ip_hash) as c FROM page_views WHERE date(viewed_at) = date('now','localtime')")->fetch();
    $analytics['unique_today'] = (int)$r['c'];

    // Visitantes únicos 7 dias
    $r = $db->query("SELECT COUNT(DISTINCT ip_hash) as c FROM page_views WHERE viewed_at >= datetime('now','-7 days','localtime')")->fetch();
    $analytics['unique_7d'] = (int)$r['c'];

    // Visitantes únicos 30 dias
    $r = $db->query("SELECT COUNT(DISTINCT ip_hash) as c FROM page_views WHERE viewed_at >= datetime('now','-30 days','localtime')")->fetch();
    $analytics['unique_30d'] = (int)$r['c'];

    // Pageviews últimos 7 dias
    $r = $db->query("SELECT COUNT(*) as c FROM page_views WHERE viewed_at >= datetime('now','-7 days','localtime')")->fetch();
    $analytics['views_7d'] = (int)$r['c'];

    // Pageviews últimos 30 dias
    $r = $db->query("SELECT COUNT(*) as c FROM page_views WHERE viewed_at >= datetime('now','-30 days','localtime')")->fetch();
    $analytics['views_30d'] = (int)$r['c'];

    // Top 5 páginas mais visitadas (últimos 30 dias)
    $stmt = $db->query("
        SELECT url, COUNT(*) as views, COUNT(DISTINCT ip_hash) as uniques
        FROM page_views
        WHERE viewed_at >= datetime('now','-30 days','localtime')
        GROUP BY url
        ORDER BY views DESC
        LIMIT 5
    ");
    $analytics['top_pages'] = $stmt->fetchAll();

    // Gráfico dos últimos 7 dias (dia a dia)
    $stmt = $db->query("
        SELECT 
            date(viewed_at, 'localtime') as day,
            COUNT(*) as views,
            COUNT(DISTINCT ip_hash) as uniques
        FROM page_views
        WHERE viewed_at >= datetime('now','-6 days','localtime')
        GROUP BY day
        ORDER BY day ASC
    ");
    $rows = $stmt->fetchAll();

    // Preenche dias sem dados
    $chartMap = [];
    foreach ($rows as $row) {
        $chartMap[$row['day']] = ['views' => (int)$row['views'], 'uniques' => (int)$row['uniques']];
    }
    for ($i = 6; $i >= 0; $i--) {
        $day = date('Y-m-d', strtotime("-$i days"));
        $analytics['chart_7d'][] = [
            'day'     => date('d/m', strtotime($day)),
            'views'   => $chartMap[$day]['views']   ?? 0,
            'uniques' => $chartMap[$day]['uniques'] ?? 0,
        ];
    }
} catch (Exception $e) {}
// ─────────────────────────────────────────────────────────────────────────────

// Botões do Topbar
$topbarActions = '
  <a href="/admin/blog/novo" class="btn btn-primary" style="padding: 8px 18px; font-size: 13px;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Novo Post
  </a>
';

require_once __DIR__ . '/layout/header.php';
?>

<!-- Cards de Estatísticas em Grid -->
<div class="admin-stats-grid">
  <!-- Leads Novos -->
  <div class="admin-stat-card">
    <div class="admin-stat-card__top">
      <span class="admin-stat-card__label">Novos Leads</span>
      <div class="admin-stat-card__icon" style="color: var(--accent); background: var(--accent-soft);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      </div>
    </div>
    <div class="admin-stat-card__value"><?php echo $leadStats['novos']; ?></div>
    <div class="admin-stat-card__meta">aguardando primeiro contato</div>
  </div>

  <!-- Total de Leads -->
  <div class="admin-stat-card">
    <div class="admin-stat-card__top">
      <span class="admin-stat-card__label">Total de Leads</span>
      <div class="admin-stat-card__icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
    </div>
    <div class="admin-stat-card__value"><?php echo $leadStats['total']; ?></div>
    <div class="admin-stat-card__meta"><?php echo $leadStats['convertidos']; ?> convertidos em clientes</div>
  </div>

  <!-- Visitantes hoje -->
  <div class="admin-stat-card">
    <div class="admin-stat-card__top">
      <span class="admin-stat-card__label">Visitas Hoje</span>
      <div class="admin-stat-card__icon" style="color: #3b82f6; background: rgba(59,130,246,0.12);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 3v18h18"/><path d="M7 16l4-4 4 4 4-8"/></svg>
      </div>
    </div>
    <div class="admin-stat-card__value"><?php echo $analytics['today_views']; ?></div>
    <div class="admin-stat-card__meta"><?php echo $analytics['unique_today']; ?> visitantes únicos hoje</div>
  </div>

  <!-- Visitas 7 dias -->
  <div class="admin-stat-card">
    <div class="admin-stat-card__top">
      <span class="admin-stat-card__label">Últimos 7 dias</span>
      <div class="admin-stat-card__icon" style="color: #10b981; background: rgba(16,185,129,0.12);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
      </div>
    </div>
    <div class="admin-stat-card__value"><?php echo $analytics['views_7d']; ?></div>
    <div class="admin-stat-card__meta"><?php echo $analytics['unique_7d']; ?> únicos · <?php echo $analytics['views_30d']; ?> no mês</div>
  </div>
</div>

<!-- ── Gráfico de Visitas 7 dias + Top Páginas ── -->
<div style="display: grid; grid-template-columns: 1.8fr 1fr; gap: 24px; margin-bottom: 24px;">

  <!-- Gráfico SVG de barras (sem dependência JS) -->
  <div class="admin-card">
    <div class="admin-card__header">
      <div class="admin-card__title">Visitas — Últimos 7 Dias</div>
      <span style="font-size: 12px; color: var(--text-white-40);">pageviews / visitantes únicos</span>
    </div>
    <div style="padding: 20px 24px 24px;">

      <?php
      $chartData  = $analytics['chart_7d'];
      $maxViews   = max(1, max(array_column($chartData, 'views')));
      $barMaxH    = 120;
      ?>
      <!-- Barras -->
      <div style="display: flex; align-items: flex-end; gap: 10px; height: <?php echo $barMaxH + 30; ?>px; padding-bottom: 24px;">
        <?php foreach ($chartData as $point): ?>
          <?php
            $hViews   = round(($point['views']   / $maxViews) * $barMaxH);
            $hUniques = round(($point['uniques'] / $maxViews) * $barMaxH);
            $hViews   = max($hViews, 2);
            $hUniques = max($hUniques, 2);
          ?>
          <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px;">
            <!-- Tooltip ao hover -->
            <div style="display: flex; align-items: flex-end; gap: 2px; height: <?php echo $barMaxH; ?>px; width: 100%;">
              <!-- Barra de views -->
              <div title="Pageviews: <?php echo $point['views']; ?>"
                   style="flex: 1; height: <?php echo $hViews; ?>px; background: var(--accent); border-radius: 4px 4px 0 0; opacity: 0.85; transition: opacity .2s; cursor: default;"
                   onmouseenter="this.style.opacity='1'"
                   onmouseleave="this.style.opacity='0.85'">
              </div>
              <!-- Barra de únicos -->
              <div title="Únicos: <?php echo $point['uniques']; ?>"
                   style="flex: 1; height: <?php echo $hUniques; ?>px; background: rgba(255,90,0,0.25); border-radius: 4px 4px 0 0; transition: opacity .2s; cursor: default;"
                   onmouseenter="this.style.opacity='1'"
                   onmouseleave="this.style.opacity=''">
              </div>
            </div>
            <span style="font-size: 10px; color: var(--text-white-40); white-space: nowrap;"><?php echo $point['day']; ?></span>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Legenda -->
      <div style="display: flex; gap: 18px; margin-top: 4px;">
        <div style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--text-white-65);">
          <span style="display: inline-block; width: 10px; height: 10px; background: var(--accent); border-radius: 2px;"></span>Pageviews
        </div>
        <div style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--text-white-65);">
          <span style="display: inline-block; width: 10px; height: 10px; background: rgba(255,90,0,0.25); border-radius: 2px;"></span>Visitantes únicos
        </div>
      </div>
    </div>
  </div>

  <!-- Top Páginas -->
  <div class="admin-card">
    <div class="admin-card__header">
      <div class="admin-card__title">Top Páginas</div>
      <span style="font-size: 12px; color: var(--text-white-40);">últimos 30 dias</span>
    </div>
    <div style="padding: 4px 0 20px;">
      <?php if (empty($analytics['top_pages'])): ?>
        <p style="font-size: 13px; color: var(--text-white-40); text-align: center; padding: 32px 24px;">Nenhum dado ainda.</p>
      <?php else: ?>
        <?php
        $maxPv = max(1, max(array_column($analytics['top_pages'], 'views')));
        foreach ($analytics['top_pages'] as $pg):
          // Simplifica a URL para exibição
          $parsed  = parse_url($pg['url']);
          $display = ($parsed['path'] ?? '/') . (isset($parsed['query']) ? '?...' : '');
          if (strlen($display) > 32) $display = substr($display, 0, 29) . '…';
          $pct = round(($pg['views'] / $maxPv) * 100);
        ?>
          <div style="padding: 10px 24px; border-bottom: 1px solid var(--hairline);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
              <span style="font-size: 13px; color: var(--text-white-90); font-weight: 500;"><?php echo htmlspecialchars($display); ?></span>
              <span style="font-size: 12px; color: var(--text-white-65); font-weight: 600;"><?php echo $pg['views']; ?></span>
            </div>
            <div style="background: var(--hairline); border-radius: 100px; height: 3px; overflow: hidden;">
              <div style="height: 100%; width: <?php echo $pct; ?>%; background: var(--accent); border-radius: 100px;"></div>
            </div>
            <div style="font-size: 11px; color: var(--text-white-40); margin-top: 3px;"><?php echo $pg['uniques']; ?> únicos</div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

</div>

<!-- Layout em duas colunas: Leads Recentes e Atividades -->
<div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 24px;">

  <!-- Tabela de Leads Recentes -->
  <div class="admin-card">
    <div class="admin-card__header">
      <div class="admin-card__title">Últimos Leads Recebidos</div>
      <a href="/admin/leads" style="font-size: 13px; color: var(--accent); text-decoration: none; font-weight: 600;">Ver todos →</a>
    </div>

    <div class="admin-table-container">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Nome</th>
            <th>WhatsApp</th>
            <th>Projeto</th>
            <th>Status</th>
            <th>Data</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recentLeads)): ?>
            <tr>
              <td colspan="5" style="text-align: center; color: var(--text-white-40); padding: 36px;">
                Nenhum lead recebido até o momento.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($recentLeads as $l): 
              $badge = Helpers::leadStatusBadge($l['status']);
            ?>
              <tr>
                <td>
                  <a href="/admin/leads/view?id=<?php echo $l['id']; ?>" style="font-weight: 600; color: var(--text-white);">
                    <?php echo Helpers::e($l['nome']); ?>
                  </a>
                  <div style="font-size: 12px; color: var(--text-white-40);"><?php echo Helpers::e($l['email']); ?></div>
                </td>
                <td>
                  <a href="https://wa.me/<?php echo preg_replace('/\D/', '', $l['whatsapp']); ?>" target="_blank" rel="noopener" style="color: #25d366; font-size: 13px;">
                    <?php echo Helpers::e($l['whatsapp']); ?>
                  </a>
                </td>
                <td style="font-size: 13px;"><?php echo Helpers::e($l['tipo_projeto']); ?></td>
                <td>
                  <span class="admin-badge" style="background: <?php echo $badge['bg']; ?>; color: <?php echo $badge['color']; ?>; border: 1px solid <?php echo $badge['border']; ?>;">
                    <?php echo $badge['label']; ?>
                  </span>
                </td>
                <td style="font-size: 12px; color: var(--text-white-40);">
                  <?php echo Helpers::timeAgo($l['created_at']); ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Feed de Atividades Recentes -->
  <div class="admin-card">
    <div class="admin-card__header">
      <div class="admin-card__title">Atividades do Sistema</div>
      <?php if (Auth::isSuperAdmin()): ?>
        <a href="/admin/logs" style="font-size: 13px; color: var(--text-white-40); text-decoration: none;">Ver logs →</a>
      <?php endif; ?>
    </div>

    <div style="padding: 20px 24px;">
      <?php if (empty($recentLogs)): ?>
        <p style="font-size: 13px; color: var(--text-white-40); text-align: center; margin: 20px 0;">
          Nenhum registro de atividade ainda.
        </p>
      <?php else: ?>
        <div class="lead-timeline">
          <?php foreach ($recentLogs as $log): ?>
            <div class="lead-timeline__item">
              <div class="lead-timeline__dot"></div>
              <div class="lead-timeline__content">
                <div class="lead-timeline__time"><?php echo Helpers::timeAgo($log['created_at']); ?></div>
                <div class="lead-timeline__text"><?php echo Helpers::e($log['description']); ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>

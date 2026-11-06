<?php
$adminTitle = 'Detalhes do Lead';

require_once dirname(__DIR__, 3) . '/core/Database.php';
require_once dirname(__DIR__, 3) . '/core/Helpers.php';
require_once dirname(__DIR__, 3) . '/core/Auth.php';
require_once dirname(__DIR__, 3) . '/core/Csrf.php';
require_once dirname(__DIR__, 3) . '/core/Logger.php';

Auth::requireLogin();

$db = Database::getInstance();
$leadId = (int)($_GET['id'] ?? 0);

if ($leadId <= 0) {
    header('Location: /admin/leads');
    exit;
}

// Processa inclusão de nota ou alteração de status via POST tradicional
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (Csrf::validate()) {
        $allowedStatuses = ['NOVO', 'EM_CONTATO', 'QUALIFICADO', 'CONVERTIDO', 'PERDIDO', 'ARQUIVADO'];
        if (!empty($_POST['new_status'])) {
            $newStatus = trim((string)$_POST['new_status']);
            if (in_array($newStatus, $allowedStatuses, true)) {
                $up = $db->prepare("UPDATE leads SET status = ?, updated_at = CURRENT_TIMESTAMP, last_contact_at = CURRENT_TIMESTAMP WHERE id = ?");
                $up->execute([$newStatus, $leadId]);

                $adminUser = Auth::user();
                $adminName = $adminUser['name'] ?? 'Administrador';
                $hist = $db->prepare("INSERT INTO lead_history (lead_id, admin_id, action, description) VALUES (?, ?, 'STATUS_CHANGED', ?)");
                $hist->execute([$leadId, Auth::id(), "Status alterado para '{$newStatus}' por {$adminName}"]);
                Logger::log('LEAD_STATUS_UPDATE', 'lead', $leadId, "Status do lead #{$leadId} alterado para {$newStatus}");
            }
        }

        if (!empty($_POST['note'])) {
            $noteText = mb_substr(trim((string)$_POST['note']), 0, 2000);
            if ($noteText !== '') {
                $adminUser = Auth::user();
                $adminName = $adminUser['name'] ?? 'Administrador';

                $hist = $db->prepare("INSERT INTO lead_history (lead_id, admin_id, action, description) VALUES (?, ?, 'NOTE_ADDED', ?)");
                $hist->execute([$leadId, Auth::id(), "Nota de {$adminName}: " . strip_tags($noteText)]);

                $up = $db->prepare("UPDATE leads SET last_contact_at = CURRENT_TIMESTAMP WHERE id = ?");
                $up->execute([$leadId]);
                Logger::log('LEAD_NOTE_ADDED', 'lead', $leadId, "Nota adicionada ao lead #{$leadId}");
            }
        }
    }
}

// Busca lead
$stmt = $db->prepare("SELECT * FROM leads WHERE id = ?");
$stmt->execute([$leadId]);
$lead = $stmt->fetch();

if (!$lead) {
    header('Location: /admin/leads');
    exit;
}

// Busca histórico
$histStmt = $db->prepare("
    SELECT h.*, a.name as admin_name 
    FROM lead_history h 
    LEFT JOIN admins a ON h.admin_id = a.id 
    WHERE h.lead_id = ? 
    ORDER BY h.created_at DESC
");
$histStmt->execute([$leadId]);
$history = $histStmt->fetchAll();

$cleanPhone = preg_replace('/\D/', '', $lead['whatsapp']);
$waText = urlencode("Olá, {$lead['nome']}! Gabriel aqui da feito. Recebemos seu briefing pelo nosso site e gostaria de bater um papo sobre o seu projeto ({$lead['tipo_projeto']}).");

$topbarActions = '
  <a href="/admin/leads" class="btn btn--glass" style="padding: 8px 16px; font-size: 13px;">
    ← Voltar aos Leads
  </a>
';

require_once __DIR__ . '/../layout/header.php';
?>

<div class="lead-detail-grid">

  <!-- Coluna 1: Informações e Mensagem do Lead -->
  <div>
    <div class="admin-card">
      <div class="admin-card__header">
        <div style="display: flex; align-items: center; gap: 12px;">
          <h2 class="admin-card__title"><?php echo Helpers::e($lead['nome']); ?></h2>
          <?php $badge = Helpers::leadStatusBadge($lead['status']); ?>
          <span class="admin-badge" style="background: <?php echo $badge['bg']; ?>; color: <?php echo $badge['color']; ?>; border: 1px solid <?php echo $badge['border']; ?>;">
            <?php echo $badge['label']; ?>
          </span>
        </div>

        <div style="display: flex; gap: 10px;">
          <a href="https://wa.me/<?php echo $cleanPhone; ?>?text=<?php echo $waText; ?>" target="_blank" rel="noopener" class="btn" style="background: #25d366; color: #ffffff; padding: 8px 18px; font-size: 13px; gap: 8px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
            Chamar no WhatsApp
          </a>
          <a href="mailto:<?php echo Helpers::e($lead['email']); ?>" class="btn--glass btn" style="padding: 8px 16px; font-size: 13px;">
            E-mail
          </a>
        </div>
      </div>

      <div style="padding: 24px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
          <div>
            <div style="font-size: 11px; color: var(--text-white-40); text-transform: uppercase; font-weight: 700;">E-mail</div>
            <div style="font-size: 15px; color: var(--text-white); margin-top: 4px;"><?php echo Helpers::e($lead['email']); ?></div>
          </div>
          <div>
            <div style="font-size: 11px; color: var(--text-white-40); text-transform: uppercase; font-weight: 700;">WhatsApp / Telefone</div>
            <div style="font-size: 15px; color: var(--text-white); margin-top: 4px;"><?php echo Helpers::e($lead['whatsapp']); ?></div>
          </div>
          <div>
            <div style="font-size: 11px; color: var(--text-white-40); text-transform: uppercase; font-weight: 700;">Tipo de Projeto</div>
            <div style="font-size: 15px; color: var(--text-white); margin-top: 4px;"><?php echo Helpers::e($lead['tipo_projeto']); ?></div>
          </div>
          <div>
            <div style="font-size: 11px; color: var(--text-white-40); text-transform: uppercase; font-weight: 700;">Investimento / Prazo Estimado</div>
            <div style="font-size: 15px; color: var(--text-white); margin-top: 4px;"><?php echo Helpers::e($lead['prazo_orcamento']); ?></div>
          </div>
          <div>
            <div style="font-size: 11px; color: var(--text-white-40); text-transform: uppercase; font-weight: 700;">Data do Recebimento</div>
            <div style="font-size: 15px; color: var(--text-white); margin-top: 4px;"><?php echo Helpers::formatDate($lead['created_at']); ?></div>
          </div>
          <div>
            <div style="font-size: 11px; color: var(--text-white-40); text-transform: uppercase; font-weight: 700;">Notificação de E-mail</div>
            <div style="font-size: 13px; margin-top: 4px;">
              <?php if ($lead['mail_notified']): ?>
                <span style="color: #4ade80;">✓ Notificação enviada à equipe</span>
              <?php else: ?>
                <span style="color: #facc15;">⚠ Não enviada ou pendente (Lead salvo com sucesso no banco)</span>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div>
          <div style="font-size: 11px; color: var(--text-white-40); text-transform: uppercase; font-weight: 700; margin-bottom: 8px;">Mensagem / Briefing do Projeto</div>
          <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid var(--hairline); border-radius: var(--radius-md); padding: 20px; font-size: 14px; line-height: 1.6; color: var(--text-white-90); white-space: pre-wrap;">
            <?php echo Helpers::e($lead['mensagem']); ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Adicionar Nota Interna -->
    <div class="admin-card">
      <div class="admin-card__header">
        <h3 class="admin-card__title">Adicionar Observação no CRM</h3>
      </div>
      <div style="padding: 24px;">
        <form method="POST" action="/admin/leads/view?id=<?php echo $leadId; ?>" class="admin-form">
          <?php echo Csrf::input(); ?>
          <textarea 
            name="note" 
            class="admin-textarea" 
            placeholder="Ex: Liguei para o cliente, reunião marcada para quinta-feira às 14h..." 
            required
            style="min-height: 90px;"
          ></textarea>
          <div style="display: flex; justify-content: flex-end;">
            <button type="submit" class="btn btn-primary" style="padding: 8px 20px; font-size: 13px;">
              Salvar Observação
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Coluna 2: Status & Timeline de Histórico -->
  <div>
    <!-- Gestão de Status -->
    <div class="admin-card">
      <div class="admin-card__header">
        <h3 class="admin-card__title">Etapa do Funil</h3>
      </div>
      <div style="padding: 20px;">
        <form method="POST" action="/admin/leads/view?id=<?php echo $leadId; ?>">
          <?php echo Csrf::input(); ?>
          <label class="admin-label">Status do Lead</label>
          <select name="new_status" class="admin-select" style="margin-bottom: 14px;">
            <option value="NOVO" <?php echo $lead['status'] === 'NOVO' ? 'selected' : ''; ?>>Novo</option>
            <option value="EM_CONTATO" <?php echo $lead['status'] === 'EM_CONTATO' ? 'selected' : ''; ?>>Em Contato</option>
            <option value="QUALIFICADO" <?php echo $lead['status'] === 'QUALIFICADO' ? 'selected' : ''; ?>>Qualificado</option>
            <option value="CONVERTIDO" <?php echo $lead['status'] === 'CONVERTIDO' ? 'selected' : ''; ?>>Convertido (Ganho)</option>
            <option value="PERDIDO" <?php echo $lead['status'] === 'PERDIDO' ? 'selected' : ''; ?>>Perdido</option>
            <option value="ARQUIVADO" <?php echo $lead['status'] === 'ARQUIVADO' ? 'selected' : ''; ?>>Arquivado</option>
          </select>
          <button type="submit" class="btn btn--glass" style="width: 100%; justify-content: center; font-size: 13px;">
            Atualizar Status
          </button>
        </form>
      </div>
    </div>

    <!-- Timeline do Lead -->
    <div class="admin-card">
      <div class="admin-card__header">
        <h3 class="admin-card__title">Histórico de Atendimento</h3>
      </div>
      <div style="padding: 20px;">
        <?php if (empty($history)): ?>
          <p style="font-size: 13px; color: var(--text-white-40); text-align: center;">Nenhum histórico registrado.</p>
        <?php else: ?>
          <div class="lead-timeline">
            <?php foreach ($history as $item): ?>
              <div class="lead-timeline__item">
                <div class="lead-timeline__dot"></div>
                <div class="lead-timeline__content">
                  <div class="lead-timeline__time"><?php echo Helpers::formatDate($item['created_at']); ?></div>
                  <div class="lead-timeline__text"><?php echo Helpers::e($item['description']); ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

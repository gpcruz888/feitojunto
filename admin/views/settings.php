<?php
$adminTitle = 'Configurações Globais';

require_once dirname(__DIR__, 2) . '/core/Database.php';
require_once dirname(__DIR__, 2) . '/core/Helpers.php';
require_once dirname(__DIR__, 2) . '/core/Auth.php';
require_once dirname(__DIR__, 2) . '/core/Csrf.php';
require_once dirname(__DIR__, 2) . '/core/Logger.php';

Auth::requireRole('SUPER_ADMIN');

$db = Database::getInstance();
$successMessage = '';
$errorMessage = '';

// Processa salvamento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::validate()) {
        $errorMessage = 'Sessão expirada. Tente novamente.';
    } else {
        $allowedKeys = [
            'site_name',
            'site_description',
            'contact_email',
            'notification_email',
            'auto_reply_enabled',
            'auto_reply_subject',
            'auto_reply_message',
            'posts_per_page',
            'instagram_url',
            'whatsapp_number'
        ];

        $stmt = $db->prepare("INSERT OR REPLACE INTO settings (key, value, updated_at) VALUES (?, ?, CURRENT_TIMESTAMP)");

        foreach ($allowedKeys as $key) {
            $val = trim($_POST[$key] ?? '');
            $stmt->execute([$key, $val]);
        }

        Logger::log('SETTINGS_UPDATED', 'settings', null, "Configurações gerais do sistema atualizadas");
        $successMessage = 'Configurações salvas com sucesso!';
    }
}

// Carrega configurações atuais
$allSettings = $db->query("SELECT key, value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);

require_once __DIR__ . '/layout/header.php';
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

<form method="POST" action="/admin/configuracoes" class="admin-form">
  <?php echo Csrf::input(); ?>

  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 28px;">

    <!-- Card 1: Leads & Notificações -->
    <div class="admin-card" style="padding: 28px;">
      <h3 class="admin-card__title" style="margin-bottom: 20px; color: var(--accent);">
        Notificações & Formulários
      </h3>

      <div style="margin-bottom: 20px;">
        <label class="admin-label" for="notification_email">E-mails que recebem novos leads</label>
        <input 
          type="text" 
          id="notification_email" 
          name="notification_email" 
          class="admin-input" 
          value="<?php echo Helpers::e($allSettings['notification_email'] ?? 'gabriel@feitojunto.com, contato@feitojunto.com'); ?>"
        >
        <div class="admin-input-hint">Separe múltiplos e-mails por vírgula.</div>
      </div>

      <div style="margin-bottom: 20px;">
        <label class="admin-label" for="auto_reply_enabled">Confirmação automática para o lead</label>
        <select id="auto_reply_enabled" name="auto_reply_enabled" class="admin-select">
          <option value="0" <?php echo ($allSettings['auto_reply_enabled'] ?? '0') === '0' ? 'selected' : ''; ?>>Desativada</option>
          <option value="1" <?php echo ($allSettings['auto_reply_enabled'] ?? '0') === '1' ? 'selected' : ''; ?>>Ativada (Envia e-mail ao visitante)</option>
        </select>
        <div class="admin-input-hint">Envia um e-mail simples e elegante confirmando o recebimento da proposta.</div>
      </div>

      <div style="margin-bottom: 20px;">
        <label class="admin-label" for="auto_reply_subject">Assunto da confirmação</label>
        <input 
          type="text" 
          id="auto_reply_subject" 
          name="auto_reply_subject" 
          class="admin-input" 
          value="<?php echo Helpers::e($allSettings['auto_reply_subject'] ?? 'Recebemos seu projeto — feito.'); ?>"
        >
      </div>

      <div>
        <label class="admin-label" for="auto_reply_message">Mensagem de confirmação</label>
        <textarea 
          id="auto_reply_message" 
          name="auto_reply_message" 
          class="admin-textarea" 
          style="min-height: 100px;"
        ><?php echo Helpers::e($allSettings['auto_reply_message'] ?? "Olá!\n\nRecebemos sua solicitação de projeto com sucesso. Em menos de 24 horas nossa equipe entrará em contato via WhatsApp e E-mail."); ?></textarea>
      </div>
    </div>

    <!-- Card 2: Informações do Site & SEO Geral -->
    <div class="admin-card" style="padding: 28px;">
      <h3 class="admin-card__title" style="margin-bottom: 20px; color: var(--accent);">
        Informações do Site & Blog
      </h3>

      <div style="margin-bottom: 20px;">
        <label class="admin-label" for="site_name">Nome do Projeto / Empresa</label>
        <input 
          type="text" 
          id="site_name" 
          name="site_name" 
          class="admin-input" 
          value="<?php echo Helpers::e($allSettings['site_name'] ?? 'feito.'); ?>"
        >
      </div>

      <div style="margin-bottom: 20px;">
        <label class="admin-label" for="site_description">Slogan / Descrição Padrão</label>
        <input 
          type="text" 
          id="site_description" 
          name="site_description" 
          class="admin-input" 
          value="<?php echo Helpers::e($allSettings['site_description'] ?? 'sua presença digital, feita do jeito certo.'); ?>"
        >
      </div>

      <div style="margin-bottom: 20px;">
        <label class="admin-label" for="contact_email">E-mail de Contato Público</label>
        <input 
          type="email" 
          id="contact_email" 
          name="contact_email" 
          class="admin-input" 
          value="<?php echo Helpers::e($allSettings['contact_email'] ?? 'contato@feitojunto.com'); ?>"
        >
      </div>

      <div style="margin-bottom: 20px;">
        <label class="admin-label" for="posts_per_page">Artigos por página no blog</label>
        <input 
          type="number" 
          id="posts_per_page" 
          name="posts_per_page" 
          class="admin-input" 
          min="3" 
          max="30" 
          value="<?php echo Helpers::e($allSettings['posts_per_page'] ?? '9'); ?>"
        >
      </div>

      <div>
        <label class="admin-label" for="instagram_url">Link do Instagram</label>
        <input 
          type="url" 
          id="instagram_url" 
          name="instagram_url" 
          class="admin-input" 
          value="<?php echo Helpers::e($allSettings['instagram_url'] ?? 'https://www.instagram.com/feitojunto_'); ?>"
        >
      </div>
    </div>

  </div>

  <div style="display: flex; justify-content: flex-end; margin-top: 8px;">
    <button type="submit" class="btn btn-primary" style="padding: 12px 32px; font-weight: 700; font-size: 14px;">
      Salvar Todas as Configurações
    </button>
  </div>
</form>

<?php require_once __DIR__ . '/layout/footer.php'; ?>

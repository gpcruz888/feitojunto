<?php
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/Csrf.php';
require_once __DIR__ . '/../../core/Helpers.php';
require_once __DIR__ . '/../../core/Logger.php';
require_once __DIR__ . '/../../core/Env.php';

$message = '';
$messageType = '';
$token = trim($_GET['token'] ?? '');
$validToken = false;
$adminUser = null;

$db = Database::getInstance();

if (!empty($token)) {
    // Valida token existente
    $stmt = $db->prepare("
        SELECT * FROM admins 
        WHERE password_reset_token = ? 
          AND password_reset_expires_at > CURRENT_TIMESTAMP 
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $adminUser = $stmt->fetch();
    $validToken = (bool)$adminUser;
}

// Processa envio de solicitação ou redefinição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::validate()) {
        $message = 'Sessão inválida. Tente novamente.';
        $messageType = 'error';
    } else {
        if (!empty($_POST['new_password']) && $validToken && $adminUser) {
            // Passo 2: Define nova senha
            $newPass = $_POST['new_password'];
            $confirmPass = $_POST['confirm_password'] ?? '';

            if (strlen($newPass) < 8) {
                $message = 'A nova senha deve ter pelo menos 8 caracteres.';
                $messageType = 'error';
            } elseif ($newPass !== $confirmPass) {
                $message = 'As senhas digitadas não coincidem.';
                $messageType = 'error';
            } else {
                $newHash = password_hash($newPass, PASSWORD_DEFAULT);
                $up = $db->prepare("
                    UPDATE admins 
                    SET password_hash = ?, 
                        password_reset_token = NULL, 
                        password_reset_expires_at = NULL 
                    WHERE id = ?
                ");
                $up->execute([$newHash, $adminUser['id']]);

                Logger::log('PASSWORD_RESET_SUCCESS', 'admin', (int)$adminUser['id'], "Senha redefinida via token", (int)$adminUser['id']);

                $message = 'Sua senha foi redefinida com sucesso! Você já pode fazer login.';
                $messageType = 'success';
                $validToken = false;
            }
        } else {
            // Passo 1: Solicita token
            $email = trim($_POST['email'] ?? '');
            if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $st = $db->prepare("SELECT * FROM admins WHERE email = ? AND status = 'ATIVO' LIMIT 1");
                $st->execute([$email]);
                $userFound = $st->fetch();

                if ($userFound) {
                    $rawToken = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hora

                    $updateToken = $db->prepare("
                        UPDATE admins 
                        SET password_reset_token = ?, password_reset_expires_at = ? 
                        WHERE id = ?
                    ");
                    $updateToken->execute([$rawToken, $expires, $userFound['id']]);

                    // Dispara e-mail de recuperação
                    $appUrl = rtrim(Env::get('APP_URL', 'https://feitojunto.com'), '/');
                    $resetUrl = "{$appUrl}/admin/recuperar-senha?token={$rawToken}";
                    $fromEmail = Env::get('MAIL_FROM_EMAIL', 'noreply@feitojunto.com');
                    $fromName = Env::get('MAIL_FROM_NAME', 'feito. Suporte');

                    $mailBody = "Olá, {$userFound['name']}!\n\nRecebemos uma solicitação para redefinir a sua senha de acesso à área administrativa do feito.\n\nPara cadastrar uma nova senha, acesse o link seguro abaixo (válido por 1 hora):\n{$resetUrl}\n\nSe você não solicitou esta alteração, por favor ignore este e-mail.";
                    @mail($email, "=?UTF-8?B?" . base64_encode("Redefinição de Senha — feito.") . "?=", $mailBody, "From: {$fromName} <{$fromEmail}>\r\nX-Mailer: PHP/" . phpversion());

                    Logger::log('PASSWORD_RESET_REQUEST', 'admin', (int)$userFound['id'], "Token de redefinição solicitado para: {$email}");
                }
            }

            // Mensagem neutra para proteção contra enumeração de e-mails
            $message = 'Se o e-mail informado estiver cadastrado em nosso sistema, as instruções foram enviadas para sua caixa de entrada.';
            $messageType = 'success';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recuperação de Senha | feito.</title>
  <link rel="stylesheet" href="/css/variables.css">
  <link rel="stylesheet" href="/admin/assets/css/admin.css">
</head>
<body class="admin-login-body">
  <div class="admin-login-card">
    <div class="admin-login-logo">
      <a href="/"><img src="/assets/feitojunto logo/1.png" alt="feito."></a>
    </div>

    <h2 class="admin-login-title">Recuperação de Acesso<span style="color: var(--accent);">.</span></h2>

    <?php if (!empty($message)): ?>
      <div class="admin-alert admin-alert--<?php echo $messageType; ?>">
        <span><?php echo Helpers::e($message); ?></span>
      </div>
    <?php endif; ?>

    <?php if ($validToken): ?>
      <p class="admin-login-desc">Digite sua nova senha abaixo para continuar.</p>
      <form method="POST" class="admin-form">
        <?php echo Csrf::input(); ?>
        <div>
          <label class="admin-label" for="new_password">Nova Senha (mínimo 8 caracteres)</label>
          <input type="password" id="new_password" name="new_password" class="admin-input" required minlength="8" autofocus>
        </div>
        <div>
          <label class="admin-label" for="confirm_password">Confirme a Nova Senha</label>
          <input type="password" id="confirm_password" name="confirm_password" class="admin-input" required minlength="8">
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Salvar Nova Senha</button>
      </form>
    <?php else: ?>
      <p class="admin-login-desc">Informe seu e-mail cadastrado para receber o link de redefinição.</p>
      <form method="POST" class="admin-form">
        <?php echo Csrf::input(); ?>
        <div>
          <label class="admin-label" for="email">E-mail cadastrado</label>
          <input type="email" id="email" name="email" class="admin-input" placeholder="admin@feitojunto.com" required autofocus>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Enviar Instruções</button>
      </form>
    <?php endif; ?>

    <div style="margin-top: 24px; text-align: center; border-top: 1px solid var(--hairline); padding-top: 16px;">
      <a href="/admin/login" style="font-size: 13px; color: var(--text-white-40); text-decoration: none;">← Voltar para o login</a>
    </div>
  </div>
</body>
</html>

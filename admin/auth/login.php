<?php
require_once __DIR__ . '/../../core/Security.php';
Security::sendHeaders(true);

require_once __DIR__ . '/../../core/Schema.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Csrf.php';
require_once __DIR__ . '/../../core/Helpers.php';

// Garante que tabelas e seed existam
try {
    Schema::up();
} catch (Exception $e) {
    error_log("Schema initialization in login failed: " . $e->getMessage());
}

// Se já estiver logado, redireciona para o dashboard
if (Auth::check()) {
    header('Location: /admin/dashboard');
    exit;
}

$errorMessage = '';
$emailInput = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::validate()) {
        $errorMessage = 'Sessão do formulário expirada. Por favor, recarregue a página.';
    } else {
        $emailInput = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($emailInput) || empty($password)) {
            $errorMessage = 'Preencha o e-mail e a senha.';
        } else {
            $result = Auth::attempt($emailInput, $password);
            if ($result['success']) {
                header('Location: /admin/dashboard');
                exit;
            } else {
                $errorMessage = $result['message'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Login Administrativo | feito.</title>
  
  <link rel="preconnect" href="https://api.fontshare.com">
  <link href="https://api.fontshare.com/v2/css?f[]=satoshi@900,700,600,500,400,300&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="/css/variables.css?v=<?= time(); ?>">
  <link rel="stylesheet" href="/css/components/buttons.css?v=<?= time(); ?>">
  <link rel="stylesheet" href="/admin/assets/css/admin.css?v=<?= time(); ?>">
  <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon.png?v=3">
</head>
<body class="admin-login-body">

  <div class="admin-login-card">
    <div class="admin-login-logo">
      <a href="/">
        <img src="/assets/feitojunto logo/1.png" alt="feito.">
      </a>
    </div>

    <h2 class="admin-login-title">Área Administrativa<span style="color: var(--accent);">.</span></h2>
    <p class="admin-login-desc">Entre com suas credenciais seguras para gerenciar o site e leads.</p>

    <?php if (!empty($errorMessage)): ?>
      <div class="admin-alert admin-alert--error">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span><?php echo Helpers::e($errorMessage); ?></span>
      </div>
    <?php endif; ?>

    <form method="POST" action="/admin/login" class="admin-form">
      <?php echo Csrf::input(); ?>

      <div>
        <label class="admin-label" for="login-email">E-mail corporativo</label>
        <input 
          type="email" 
          id="login-email" 
          name="email" 
          class="admin-input" 
          placeholder="admin@feitojunto.com" 
          value="<?php echo Helpers::e($emailInput); ?>" 
          required 
          autocomplete="email"
          autofocus
        >
      </div>

      <div>
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <label class="admin-label" for="login-password">Senha de acesso</label>
          <a href="/admin/recuperar-senha" style="font-size: 12px; color: var(--text-white-40); text-decoration: none;">Esqueceu?</a>
        </div>
        <input 
          type="password" 
          id="login-password" 
          name="password" 
          class="admin-input" 
          placeholder="••••••••••••" 
          required 
          autocomplete="current-password"
        >
      </div>

      <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 8px;">
        Entrar no Painel
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
      </button>
    </form>

    <div style="margin-top: 28px; text-align: center; border-top: 1px solid var(--hairline); padding-top: 18px;">
      <a href="/" style="font-size: 12px; color: var(--text-white-40); text-decoration: none;">← Voltar ao site feito.</a>
    </div>
  </div>

</body>
</html>

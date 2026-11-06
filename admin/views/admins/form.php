<?php
require_once dirname(__DIR__, 3) . '/core/Database.php';
require_once dirname(__DIR__, 3) . '/core/Helpers.php';
require_once dirname(__DIR__, 3) . '/core/Auth.php';
require_once dirname(__DIR__, 3) . '/core/Csrf.php';
require_once dirname(__DIR__, 3) . '/core/Logger.php';

Auth::requireRole('SUPER_ADMIN');

$db = Database::getInstance();
$targetId = (int)($_GET['id'] ?? 0);
$isEditing = $targetId > 0;

$adminTitle = $isEditing ? 'Editar Administrador' : 'Novo Administrador';

$userObj = [
    'name' => '',
    'email' => '',
    'role' => 'EDITOR',
    'status' => 'ATIVO'
];

if ($isEditing) {
    $stmt = $db->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$targetId]);
    $existing = $stmt->fetch();
    if (!$existing) {
        header('Location: /admin/admins');
        exit;
    }
    $userObj = $existing;
}

$errorMessage = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::validate()) {
        $errorMessage = 'Sessão expirada. Tente novamente.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = in_array($_POST['role'] ?? '', ['SUPER_ADMIN', 'EDITOR']) ? $_POST['role'] : 'EDITOR';
        $status = in_array($_POST['status'] ?? '', ['ATIVO', 'INATIVO']) ? $_POST['status'] : 'ATIVO';
        $password = $_POST['password'] ?? '';

        if (empty($name) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = 'Preencha o nome e um e-mail válido.';
        } elseif (!$isEditing && (empty($password) || strlen($password) < 8 || strlen($password) > 128)) {
            $errorMessage = 'Para novos usuários, a senha deve ter entre 8 e 128 caracteres.';
        } elseif ($isEditing && $targetId === Auth::id() && ($status === 'INATIVO' || $role !== 'SUPER_ADMIN')) {
            $errorMessage = 'Por segurança, você não pode desativar ou remover o nível de Super Administrador da sua própria conta.';
        } else {
            // Checa duplicidade de e-mail
            $checkSql = $isEditing 
                ? "SELECT COUNT(*) FROM admins WHERE email = ? AND id != ?" 
                : "SELECT COUNT(*) FROM admins WHERE email = ?";
            $checkParams = $isEditing ? [$email, $targetId] : [$email];
            $cStmt = $db->prepare($checkSql);
            $cStmt->execute($checkParams);

            if ((int)$cStmt->fetchColumn() > 0) {
                $errorMessage = 'Já existe um administrador com este endereço de e-mail.';
            } else {
                if ($isEditing) {
                    if (!empty($password)) {
                        if (strlen($password) < 8) {
                            $errorMessage = 'A nova senha deve ter pelo menos 8 caracteres.';
                        } else {
                            $hash = password_hash($password, PASSWORD_DEFAULT);
                            $stmt = $db->prepare("UPDATE admins SET name = ?, email = ?, role = ?, status = ?, password_hash = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                            $stmt->execute([$name, $email, $role, $status, $hash, $targetId]);
                            $successMessage = 'Dados e senha do administrador atualizados!';
                        }
                    } else {
                        $stmt = $db->prepare("UPDATE admins SET name = ?, email = ?, role = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                        $stmt->execute([$name, $email, $role, $status, $targetId]);
                        $successMessage = 'Dados do administrador atualizados!';
                    }
                    Logger::log('ADMIN_UPDATED', 'admin', $targetId, "Dados do admin #{$targetId} atualizados");
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("INSERT INTO admins (name, email, password_hash, role, status) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $email, $hash, $role, $status]);
                    $newId = (int)$db->lastInsertId();

                    Logger::log('ADMIN_CREATED', 'admin', $newId, "Novo administrador '{$name}' criado");
                    header('Location: /admin/admins?created=1');
                    exit;
                }

                // Atualiza objeto em memória
                $userObj['name'] = $name;
                $userObj['email'] = $email;
                $userObj['role'] = $role;
                $userObj['status'] = $status;
            }
        }
    }
}

$topbarActions = '
  <a href="/admin/admins" class="btn btn--glass" style="padding: 8px 16px; font-size: 13px;">
    ← Voltar aos Administradores
  </a>
';

require_once __DIR__ . '/../layout/header.php';
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

<div class="admin-card" style="max-width: 650px; padding: 32px;">
  <form method="POST" class="admin-form">
    <?php echo Csrf::input(); ?>

    <div>
      <label class="admin-label" for="admin-name">Nome Completo *</label>
      <input 
        type="text" 
        id="admin-name" 
        name="name" 
        class="admin-input" 
        placeholder="Gabriel Silva" 
        value="<?php echo Helpers::e($userObj['name']); ?>" 
        required
      >
    </div>

    <div>
      <label class="admin-label" for="admin-email">E-mail de Acesso *</label>
      <input 
        type="email" 
        id="admin-email" 
        name="email" 
        class="admin-input" 
        placeholder="gabriel@feitojunto.com" 
        value="<?php echo Helpers::e($userObj['email']); ?>" 
        required
      >
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
      <div>
        <label class="admin-label" for="admin-role">Nível de Permissão</label>
        <select id="admin-role" name="role" class="admin-select">
          <option value="EDITOR" <?php echo $userObj['role'] === 'EDITOR' ? 'selected' : ''; ?>>Editor (Blog e Leads)</option>
          <option value="SUPER_ADMIN" <?php echo $userObj['role'] === 'SUPER_ADMIN' ? 'selected' : ''; ?>>Super Administrador (Acesso Total)</option>
        </select>
      </div>

      <div>
        <label class="admin-label" for="admin-status">Status da Conta</label>
        <select id="admin-status" name="status" class="admin-select">
          <option value="ATIVO" <?php echo $userObj['status'] === 'ATIVO' ? 'selected' : ''; ?>>Ativo (Pode acessar)</option>
          <option value="INATIVO" <?php echo $userObj['status'] === 'INATIVO' ? 'selected' : ''; ?>>Inativo (Bloqueado)</option>
        </select>
      </div>
    </div>

    <div>
      <label class="admin-label" for="admin-password">
        Senha de Acesso <?php echo $isEditing ? '(deixe em branco para manter a atual)' : '*'; ?>
      </label>
      <input 
        type="password" 
        id="admin-password" 
        name="password" 
        class="admin-input" 
        placeholder="••••••••••••" 
        <?php echo $isEditing ? '' : 'required minlength="8"'; ?>
      >
      <div class="admin-input-hint">Mínimo de 8 caracteres. Utilize letras, números e símbolos para segurança.</div>
    </div>

    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 12px;">
      <a href="/admin/admins" class="btn btn--glass" style="padding: 10px 20px; font-size: 13px;">Cancelar</a>
      <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-size: 13px; font-weight: 700;">
        <?php echo $isEditing ? 'Salvar Alterações' : 'Cadastrar Administrador'; ?>
      </button>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

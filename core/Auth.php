<?php
/**
 * Authentication & Authorization Service — feito.
 * Gestão de sessões seguras, RBAC, proteção contra brute-force e tokens.
 */
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Logger.php';
require_once __DIR__ . '/Env.php';

class Auth {
    private static function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            $isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
                       (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
            session_set_cookie_params([
                'lifetime' => 0, // Encerra ao fechar o navegador
                'path' => '/',
                'domain' => '',
                'secure' => $isHttps,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            session_start();
        }
    }

    public static function attempt(string $email, string $password): array {
        self::startSession();
        $db = Database::getInstance();

        $ip = self::getClientIp();

        // 1. Verifica Brute Force Lockout
        $lockoutMinutes = self::checkLockout($db, $ip, $email);
        if ($lockoutMinutes > 0) {
            Logger::log('LOGIN_BLOCKED', 'admin', null, "Tentativa bloqueada por excesso de falhas ({$email})");
            return [
                'success' => false,
                'message' => "Muitas tentativas inválidas. Acesso temporariamente bloqueado por {$lockoutMinutes} minuto(s)."
            ];
        }

        // 2. Busca o usuário
        $stmt = $db->prepare("SELECT * FROM admins WHERE email = ? AND status = 'ATIVO' LIMIT 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        // 3. Validação de senha
        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            self::recordFailedAttempt($db, $ip, $email);
            Logger::log('LOGIN_FAILED', 'admin', null, "Tentativa de login falha para: {$email}");
            return [
                'success' => false,
                'message' => 'Credenciais incorretas ou usuário inativo.'
            ];
        }

        // 4. Sucesso: regenera ID de sessão para prevenir session fixation
        session_regenerate_id(true);

        // Limpa tentativas de login anteriores
        self::clearAttempts($db, $ip, $email);

        // Atualiza last_login_at
        $update = $db->prepare("UPDATE admins SET last_login_at = CURRENT_TIMESTAMP WHERE id = ?");
        $update->execute([$admin['id']]);

        // Grava sessão
        $_SESSION['admin_user'] = [
            'id' => (int)$admin['id'],
            'name' => $admin['name'],
            'email' => $admin['email'],
            'role' => $admin['role'],
            'logged_at' => time()
        ];

        Logger::log('LOGIN_SUCCESS', 'admin', (int)$admin['id'], "Login bem-sucedido para: {$email}", (int)$admin['id']);

        return ['success' => true];
    }

    public static function check(): bool {
        self::startSession();

        if (empty($_SESSION['admin_user']['id'])) {
            return false;
        }

        // Verifica expiração de inatividade (padrão 6 horas)
        $maxLifetime = (int)Env::get('SESSION_LIFETIME_HOURS', 6) * 3600;
        if (isset($_SESSION['admin_user']['logged_at']) && (time() - $_SESSION['admin_user']['logged_at'] > $maxLifetime)) {
            self::logout();
            return false;
        }

        return true;
    }

    public static function user(): ?array {
        if (!self::check()) {
            return null;
        }
        return $_SESSION['admin_user'];
    }

    public static function id(): ?int {
        return $_SESSION['admin_user']['id'] ?? null;
    }

    public static function role(): ?string {
        return $_SESSION['admin_user']['role'] ?? null;
    }

    public static function isSuperAdmin(): bool {
        return self::role() === 'SUPER_ADMIN';
    }

    public static function requireLogin(): void {
        if (!self::check()) {
            if (self::isAjax()) {
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Sessão expirada. Faça login novamente.']);
                exit;
            }
            header('Location: /admin/login');
            exit;
        }
    }

    public static function requireRole(string ...$roles): void {
        self::requireLogin();
        $userRole = self::role();

        if (!in_array($userRole, $roles, true)) {
            if (self::isAjax()) {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Acesso não autorizado para seu nível de usuário.']);
                exit;
            }
            http_response_code(403);
            require_once __DIR__ . '/../admin/views/403.php';
            exit;
        }
    }

    public static function logout(): void {
        self::startSession();
        if (isset($_SESSION['admin_user']['id'])) {
            Logger::log('LOGOUT', 'admin', (int)$_SESSION['admin_user']['id'], "Logout efetuado");
        }

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
    }

    private static function checkLockout(PDO $db, string $ip, string $email): int {
        $windowMinutes = (int)Env::get('LOGIN_LOCKOUT_MINUTES', 15);
        $maxAttempts = (int)Env::get('LOGIN_MAX_ATTEMPTS', 5);

        $stmt = $db->prepare("
            SELECT COUNT(*) as total FROM login_attempts
            WHERE (ip_address = ? OR email = ?)
              AND attempted_at > datetime('now', '-' || ? || ' minutes')
        ");
        $stmt->execute([$ip, $email, $windowMinutes]);
        $attempts = (int)$stmt->fetchColumn();

        if ($attempts >= $maxAttempts) {
            return $windowMinutes;
        }

        return 0;
    }

    private static function recordFailedAttempt(PDO $db, string $ip, string $email): void {
        $stmt = $db->prepare("INSERT INTO login_attempts (ip_address, email) VALUES (?, ?)");
        $stmt->execute([$ip, $email]);
    }

    private static function clearAttempts(PDO $db, string $ip, string $email): void {
        $stmt = $db->prepare("DELETE FROM login_attempts WHERE ip_address = ? OR email = ?");
        $stmt->execute([$ip, $email]);
    }

    private static function getClientIp(): string {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (str_contains($ip, ',')) {
            $ip = trim(explode(',', $ip)[0]);
        }
        return $ip;
    }

    private static function isAjax(): bool {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
               (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));
    }
}

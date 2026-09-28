<?php
/**
 * Audit Logger — feito.
 * Registro centralizado de auditoria para ações no CMS e no painel admin.
 */
require_once __DIR__ . '/Database.php';

class Logger {
    public static function log(
        string $action,
        string $entityType,
        ?int $entityId,
        string $description,
        ?int $adminId = null
    ): void {
        try {
            $db = Database::getInstance();

            if ($adminId === null && isset($_SESSION['admin_user']['id'])) {
                $adminId = (int)$_SESSION['admin_user']['id'];
            }

            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            // Se vier múltiplos IPs separados por vírgula em X-Forwarded-For
            if (str_contains($ip, ',')) {
                $ip = trim(explode(',', $ip)[0]);
            }
            $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255);

            $stmt = $db->prepare("
                INSERT INTO audit_logs (admin_id, action, entity_type, entity_id, description, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $adminId,
                $action,
                $entityType,
                $entityId,
                $description,
                $ip,
                $ua
            ]);
        } catch (Exception $e) {
            error_log("Audit log recording failed: " . $e->getMessage());
        }
    }
}

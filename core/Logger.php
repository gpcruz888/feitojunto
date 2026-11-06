<?php
/**
 * Audit Logger — feito.
 * Registro centralizado de auditoria para ações no CMS e no painel admin.
 */
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Security.php';

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

            $ip = Security::getClientIp();
            $ua = preg_replace('/[\x00-\x1F\x7F]+/', ' ', $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown') ?? 'Unknown';
            $ua = substr(trim($ua), 0, 255);
            $cleanDesc = preg_replace('/[\x00-\x1F\x7F]+/', ' ', $description) ?? $description;
            $cleanDesc = substr(trim($cleanDesc), 0, 500);
            $cleanAction = substr(preg_replace('/[^A-Z0-9_]/i', '', $action) ?? $action, 0, 64);
            $cleanEntity = substr(preg_replace('/[^a-zA-Z0-9_-]/', '', $entityType) ?? $entityType, 0, 64);

            $stmt = $db->prepare("
                INSERT INTO audit_logs (admin_id, action, entity_type, entity_id, description, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $adminId,
                $cleanAction,
                $cleanEntity,
                $entityId,
                $cleanDesc,
                $ip,
                $ua
            ]);
        } catch (Exception $e) {
            error_log("Audit log recording failed: " . $e->getMessage());
        }
    }
}

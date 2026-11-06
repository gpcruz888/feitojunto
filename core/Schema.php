<?php
/**
 * Schema & Migrations — feito.
 * Criação e versionamento de tabelas e dados iniciais (seed).
 */
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Helpers.php';
require_once __DIR__ . '/BlogSeed.php';

class Schema {
    public static function up(): void {
        $db = Database::getInstance();

        // 1. Administradores
        $db->exec("
            CREATE TABLE IF NOT EXISTS admins (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT UNIQUE NOT NULL,
                password_hash TEXT NOT NULL,
                role TEXT NOT NULL DEFAULT 'EDITOR',
                status TEXT NOT NULL DEFAULT 'ATIVO',
                password_reset_token TEXT,
                password_reset_expires_at DATETIME,
                last_login_at DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // 2. Leads
        $db->exec("
            CREATE TABLE IF NOT EXISTS leads (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nome TEXT NOT NULL,
                email TEXT NOT NULL,
                whatsapp TEXT NOT NULL,
                empresa TEXT,
                tipo_projeto TEXT,
                prazo_orcamento TEXT,
                mensagem TEXT,
                source TEXT DEFAULT 'site_modal',
                status TEXT NOT NULL DEFAULT 'NOVO',
                mail_notified INTEGER DEFAULT 0,
                mail_error TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                last_contact_at DATETIME
            );
        ");

        // Índices para pesquisa rápida de leads
        $db->exec("CREATE INDEX IF NOT EXISTS idx_leads_status ON leads(status);");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_leads_created_at ON leads(created_at);");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_leads_email ON leads(email);");

        // 3. Histórico e Notas do Lead
        $db->exec("
            CREATE TABLE IF NOT EXISTS lead_history (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                lead_id INTEGER NOT NULL,
                admin_id INTEGER,
                action TEXT NOT NULL,
                description TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY(lead_id) REFERENCES leads(id) ON DELETE CASCADE,
                FOREIGN KEY(admin_id) REFERENCES admins(id) ON DELETE SET NULL
            );
        ");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_lead_hist_lead ON lead_history(lead_id);");

        // 4. Categorias
        $db->exec("
            CREATE TABLE IF NOT EXISTS categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                slug TEXT UNIQUE NOT NULL,
                description TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // 5. Posts do Blog
        $db->exec("
            CREATE TABLE IF NOT EXISTS posts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                author_id INTEGER NOT NULL,
                category_id INTEGER,
                title TEXT NOT NULL,
                slug TEXT UNIQUE NOT NULL,
                excerpt TEXT,
                content TEXT,
                cover_image TEXT,
                status TEXT NOT NULL DEFAULT 'RASCUNHO',
                seo_title TEXT,
                seo_description TEXT,
                og_title TEXT,
                og_description TEXT,
                og_image TEXT,
                published_at DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY(author_id) REFERENCES admins(id),
                FOREIGN KEY(category_id) REFERENCES categories(id) ON DELETE SET NULL
            );
        ");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_posts_slug ON posts(slug);");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_posts_status ON posts(status);");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_posts_published ON posts(published_at);");

        // 6. Tags e Post_Tags
        $db->exec("
            CREATE TABLE IF NOT EXISTS tags (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT UNIQUE NOT NULL,
                slug TEXT UNIQUE NOT NULL
            );
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS post_tags (
                post_id INTEGER NOT NULL,
                tag_id INTEGER NOT NULL,
                PRIMARY KEY(post_id, tag_id),
                FOREIGN KEY(post_id) REFERENCES posts(id) ON DELETE CASCADE,
                FOREIGN KEY(tag_id) REFERENCES tags(id) ON DELETE CASCADE
            );
        ");

        // 7. Auditoria (Audit Logs)
        $db->exec("
            CREATE TABLE IF NOT EXISTS audit_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                admin_id INTEGER,
                action TEXT NOT NULL,
                entity_type TEXT NOT NULL,
                entity_id INTEGER,
                description TEXT NOT NULL,
                ip_address TEXT,
                user_agent TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY(admin_id) REFERENCES admins(id) ON DELETE SET NULL
            );
        ");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_logs_created ON audit_logs(created_at);");

        // 8. Configurações Globais
        $db->exec("
            CREATE TABLE IF NOT EXISTS settings (
                key TEXT PRIMARY KEY,
                value TEXT NOT NULL,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // 9. Tentativas de Login (Rate Limiting)
        $db->exec("
            CREATE TABLE IF NOT EXISTS login_attempts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ip_address TEXT NOT NULL,
                email TEXT NOT NULL,
                attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_attempts_ip_email ON login_attempts(ip_address, email);");

        // 10. Page Views (Analytics)
        $db->exec("
            CREATE TABLE IF NOT EXISTS page_views (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                url TEXT NOT NULL,
                referrer TEXT,
                ip_hash TEXT,
                user_agent TEXT,
                session_id TEXT,
                viewed_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_pv_url ON page_views(url);");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_pv_viewed ON page_views(viewed_at);");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_pv_session ON page_views(session_id);");

        // Seed inicial
        self::seed($db);
    }

    private static function seed(PDO $db): void {
        // Seed Administrador Padrão se não existir nenhum
        $stmt = $db->query("SELECT COUNT(*) as total FROM admins");
        $adminCount = (int)$stmt->fetchColumn();

        if ($adminCount === 0) {
            $defaultPass = (string)Env::get('ADMIN_INITIAL_PASSWORD', 'FeitoAdmin@2026!');
            $defaultEmail = (string)Env::get('ADMIN_INITIAL_EMAIL', 'admin@feitojunto.com');
            $hash = password_hash($defaultPass, PASSWORD_DEFAULT);
            $insert = $db->prepare("
                INSERT INTO admins (name, email, password_hash, role, status)
                VALUES (?, ?, ?, 'SUPER_ADMIN', 'ATIVO')
            ");
            $insert->execute(['Gabriel - feito.', $defaultEmail, $hash]);
        }

        // Migração única de redefinição de acesso administrativo (executa apenas 1 vez por banco)
        $recStmt = $db->prepare("SELECT value FROM settings WHERE key = 'admin_recovery_v1'");
        $recStmt->execute();
        if ($recStmt->fetchColumn() !== '1') {
            $resetPass = (string)Env::get('ADMIN_INITIAL_PASSWORD', 'FeitoAdmin@2026!');
            $resetHash = password_hash($resetPass, PASSWORD_DEFAULT);

            foreach (['admin@feitojunto.com', 'gabriel@feitojunto.com'] as $admEmail) {
                $chk = $db->prepare("SELECT id FROM admins WHERE LOWER(email) = LOWER(?) LIMIT 1");
                $chk->execute([$admEmail]);
                $existingId = $chk->fetchColumn();

                if ($existingId) {
                    $upAdm = $db->prepare("
                        UPDATE admins
                        SET password_hash = ?, role = 'SUPER_ADMIN', status = 'ATIVO', updated_at = CURRENT_TIMESTAMP
                        WHERE id = ?
                    ");
                    $upAdm->execute([$resetHash, $existingId]);
                } else {
                    $insAdm = $db->prepare("
                        INSERT INTO admins (name, email, password_hash, role, status)
                        VALUES ('Gabriel - feito.', ?, ?, 'SUPER_ADMIN', 'ATIVO')
                    ");
                    $insAdm->execute([$admEmail, $resetHash]);
                }
            }

            // Limpa eventuais bloqueios temporários de tentativas anteriores
            $db->exec("DELETE FROM login_attempts WHERE email NOT LIKE 'rl:%'");

            $db->exec("DELETE FROM settings WHERE key = 'admin_recovery_v1'");
            $markDone = $db->prepare("INSERT INTO settings (key, value) VALUES ('admin_recovery_v1', '1')");
            $markDone->execute();
        }

        // Seed Categorias Padrão
        $catStmt = $db->query("SELECT COUNT(*) as total FROM categories");
        if ((int)$catStmt->fetchColumn() === 0) {
            $cats = [
                ['Design & Produto', 'design-e-produto', 'Artigos sobre UI/UX, design de interface e produtos digitais.'],
                ['Tecnologia & Dev', 'tecnologia-e-dev', 'Boas práticas de frontend, backend, performance e arquitetura web.'],
                ['Negócios & Estratégia', 'negocios-e-estrategia', 'Como presença digital impacta conversão e receita.'],
                ['Cases & Projetos', 'cases-e-projetos', 'Bastidores dos projetos desenvolvidos pela feito.']
            ];
            $catIns = $db->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
            foreach ($cats as $c) {
                $catIns->execute($c);
            }
        }

        // Seed Configurações Padrão
        $defaults = [
            'site_name' => 'feito.',
            'site_description' => 'sua presença digital, feita do jeito certo.',
            'contact_email' => 'gabriel@feitojunto.com, contato@feitojunto.com',
            'notification_email' => 'gabriel@feitojunto.com, contato@feitojunto.com',
            'auto_reply_enabled' => '0',
            'auto_reply_subject' => 'Recebemos seu projeto — feito.',
            'auto_reply_message' => "Olá!\n\nRecebemos sua solicitação de projeto com sucesso. Nossa equipe analisará as informações e entrará em contato em menos de 24 horas via WhatsApp e E-mail.\n\nAtenciosamente,\nEquipe feito.",
            'posts_per_page' => '9'
        ];

        $setIns = $db->prepare("INSERT OR IGNORE INTO settings (key, value) VALUES (?, ?)");
        foreach ($defaults as $k => $v) {
            $setIns->execute([$k, $v]);
        }

        BlogSeed::sync($db);
    }
}

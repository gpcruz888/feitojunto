#!/usr/bin/env python3
"""
Suite de Testes de Segurança e Regressão — feito.
Referência: OWASP Top 10:2025 & OWASP ASVS 5.0
Executa validação estática, resolução de caminhos, invariantes de segurança e regressão.
"""
import os
import re
import sqlite3
import subprocess
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent


class SecurityTestSuite:
    def __init__(self) -> None:
        self.passed = 0
        self.failed = 0
        self.errors = []

    def assert_true(self, condition: bool, name: str, detail: str = "") -> None:
        if condition:
            self.passed += 1
            print(f"  [PASS] {name}")
        else:
            self.failed += 1
            msg = f"  [FAIL] {name}" + (f" — {detail}" if detail else "")
            print(msg)
            self.errors.append(msg)

    def run_all(self) -> int:
        print("=== 1. Validação de Sintaxe JavaScript (Frontend & Admin) ===")
        self.test_js_syntax()

        print("\n=== 2. Validação Estrutural e Resolução de Includes PHP ===")
        self.test_php_structure_and_includes()

        print("\n=== 3. Testes de Segurança: Controle de Acesso & Autenticação (OWASP A01/A07) ===")
        self.test_access_control_and_auth()

        print("\n=== 4. Testes de Segurança: Proteção CSRF em Endpoints e Rotas (OWASP A01) ===")
        self.test_csrf_protection()

        print("\n=== 5. Testes de Segurança: SQL Injection, XSS, CSV & Header Injection (OWASP A05) ===")
        self.test_injection_and_xss()

        print("\n=== 6. Testes de Segurança: Rate Limiting, CORS, Cookies & Headers (OWASP A02/A06) ===")
        self.test_headers_cors_ratelimit()

        print("\n=== 7. Testes de Regressão: Banco de Dados SQLite & Rotas Públicas ===")
        self.test_database_and_regression()

        print("\n============================================================")
        print(f"RESULTADO FINAL: {self.passed} testes aprovados, {self.failed} falhas.")
        print("============================================================")
        return 0 if self.failed == 0 else 1

    def test_js_syntax(self) -> None:
        for rel in ["script.js", "admin/assets/js/admin.js"]:
            path = ROOT / rel
            res = subprocess.run(["node", "--check", str(path)], capture_output=True, text=True)
            self.assert_true(res.returncode == 0, f"Sintaxe JS válida em {rel}", res.stderr.strip())

    def test_php_structure_and_includes(self) -> None:
        php_files = sorted(ROOT.rglob("*.php"))
        self.assert_true(len(php_files) >= 30, f"Encontrados {len(php_files)} arquivos PHP para auditoria")

        req_pattern = re.compile(
            r"""require(?:_once)?\s+(?:__DIR__\s*\.\s*['"]([^'"]+)['"]|dirname\(__DIR__,\s*(\d+)\)\s*\.\s*['"]([^'"]+)['"])"""
        )

        for php_file in php_files:
            rel = php_file.relative_to(ROOT)
            content = php_file.read_text(encoding="utf-8")

            # Verifica erro clássico de espaço após cifrão ($ var)
            bad_var = re.search(r"\$\s+[a-zA-Z_]", content)
            self.assert_true(bad_var is None, f"Sem erro de sintaxe de variável em {rel}")

            # Verifica resolução de todos os require/require_once
            for match in req_pattern.finditer(content):
                direct_rel, levels, dirname_rel = match.groups()
                if direct_rel is not None:
                    target = (php_file.parent / direct_rel.lstrip("/")).resolve()
                else:
                    base = php_file.parent
                    for _ in range(int(levels)):
                        base = base.parent
                    target = (base / dirname_rel.lstrip("/")).resolve()

                self.assert_true(
                    target.is_file(),
                    f"Include resolvido corretamente em {rel} -> {target.relative_to(ROOT) if target.is_relative_to(ROOT) else target}",
                    f"Arquivo não encontrado: {target}"
                )

    def test_access_control_and_auth(self) -> None:
        protected_views = [
            "admin/views/dashboard.php",
            "admin/views/settings.php",
            "admin/views/logs.php",
            "admin/views/layout/header.php",
            "admin/views/layout/sidebar.php",
            "admin/views/leads/index.php",
            "admin/views/leads/view.php",
            "admin/views/leads/export.php",
            "admin/views/admins/index.php",
            "admin/views/admins/form.php",
            "admin/views/blog/index.php",
            "admin/views/blog/form.php",
            "admin/views/blog/categories.php",
        ]
        for rel in protected_views:
            content = (ROOT / rel).read_text(encoding="utf-8")
            has_guard = ("Auth::requireLogin()" in content) or ("Auth::requireRole(" in content)
            self.assert_true(has_guard, f"Proteção de autenticação ativa em {rel}")

        super_admin_views = [
            "admin/views/settings.php",
            "admin/views/logs.php",
            "admin/views/admins/index.php",
            "admin/views/admins/form.php",
        ]
        for rel in super_admin_views:
            content = (ROOT / rel).read_text(encoding="utf-8")
            self.assert_true(
                "Auth::requireRole('SUPER_ADMIN')" in content,
                f"Restrição RBAC SUPER_ADMIN ativa em {rel}",
            )

        admin_htaccess = (ROOT / "admin/.htaccess").read_text(encoding="utf-8")
        self.assert_true(
            "RewriteRule ^(views|auth)(/|$) - [F,L]" in admin_htaccess,
            "admin/.htaccess bloqueia acesso direto a views/ e auth/",
        )

        auth_php = (ROOT / "core/Auth.php").read_text(encoding="utf-8")
        self.assert_true(
            "SELECT id, name, email, role, status FROM admins WHERE id = ? LIMIT 1" in auth_php,
            "Auth::check() revalida status ATIVO e role no banco de dados",
        )
        self.assert_true(
            "hash('sha256', $rawToken)" in (ROOT / "admin/auth/reset-password.php").read_text(encoding="utf-8"),
            "Token de recuperação de senha armazenado com hash SHA-256 no banco",
        )

    def test_csrf_protection(self) -> None:
        csrf_endpoints = [
            "admin/api/lead-note.php",
            "admin/api/lead-status.php",
            "admin/api/upload.php",
            "admin/auth/login.php",
            "admin/auth/logout.php",
            "admin/auth/reset-password.php",
            "admin/views/settings.php",
            "admin/views/leads/view.php",
            "admin/views/admins/index.php",
            "admin/views/admins/form.php",
            "admin/views/blog/index.php",
            "admin/views/blog/form.php",
            "admin/views/blog/categories.php",
        ]
        for rel in csrf_endpoints:
            content = (ROOT / rel).read_text(encoding="utf-8")
            self.assert_true("Csrf::validate(" in content, f"Validação CSRF ativa em {rel}")

        logout_php = (ROOT / "admin/auth/logout.php").read_text(encoding="utf-8")
        self.assert_true(
            "$_SERVER['REQUEST_METHOD'] === 'POST' && Csrf::validate()" in logout_php,
            "Logout exige requisição POST autenticada com token CSRF",
        )

    def test_injection_and_xss(self) -> None:
        # 1. Verifica ausência de interpolação direta de variáveis em queries SQL
        admins_idx = (ROOT / "admin/views/admins/index.php").read_text(encoding="utf-8")
        self.assert_true(
            "WHERE id = {$targetId}" not in admins_idx,
            "Query SQL parametrizada em admin/views/admins/index.php",
        )

        blog_form = (ROOT / "admin/views/blog/form.php").read_text(encoding="utf-8")
        self.assert_true(
            "$db->quote(" not in blog_form and "WHERE slug = ?" in blog_form,
            "Query SQL parametrizada para tags em admin/views/blog/form.php",
        )
        self.assert_true(
            "Security::sanitizeHtml(" in blog_form,
            "Sanitização HTML no salvamento de posts em admin/views/blog/form.php",
        )

        blog_post = (ROOT / "blog-post.php").read_text(encoding="utf-8")
        self.assert_true(
            "Security::sanitizeHtml(" in blog_post,
            "Sanitização HTML na renderização pública em blog-post.php",
        )
        self.assert_true(
            "$_SERVER[HTTP_HOST]" not in blog_post,
            "Prevenção de Host Header Injection em links de compartilhamento (blog-post.php)",
        )

        # 2. Upload seguro (sem SVG e com getimagesize)
        upload_php = (ROOT / "admin/api/upload.php").read_text(encoding="utf-8")
        self.assert_true(
            "image/svg+xml" not in upload_php and "getimagesize(" in upload_php,
            "Upload de imagens bloqueia SVG (Stored XSS) e valida cabeçalho binário com getimagesize()",
        )

        # 3. DOM XSS em admin.js
        admin_js = (ROOT / "admin/assets/js/admin.js").read_text(encoding="utf-8")
        self.assert_true(
            "msgSpan.textContent =" in admin_js and "<span>${message}</span>" not in admin_js,
            "Prevenção de DOM XSS em window.showToast (admin/assets/js/admin.js)",
        )

        # 4. CSV Injection em export.php
        export_php = (ROOT / "admin/views/leads/export.php").read_text(encoding="utf-8")
        self.assert_true(
            export_php.count("Security::sanitizeCsvValue(") >= 10,
            "Proteção contra CSV Formula Injection em admin/views/leads/export.php",
        )

        # 5. Email Header Injection em Mailer.php
        mailer_php = (ROOT / "core/Mailer.php").read_text(encoding="utf-8")
        self.assert_true(
            "Security::sanitizeMailHeader(" in mailer_php and "phpversion()" not in mailer_php,
            "Prevenção de Email Header Injection (CRLF) e remoção de X-Mailer em core/Mailer.php",
        )

    def test_headers_cors_ratelimit(self) -> None:
        send_mail = (ROOT / "api/send-mail.php").read_text(encoding="utf-8")
        self.assert_true(
            "Security::isRateLimited($db, 'lead_submit'" in send_mail and "http_response_code(429)" in send_mail,
            "Rate limiting funcional por IP e global em api/send-mail.php",
        )

        track_php = (ROOT / "api/track.php").read_text(encoding="utf-8")
        self.assert_true(
            "Access-Control-Allow-Origin: *" not in track_php,
            "CORS curinga removido de api/track.php",
        )
        self.assert_true(
            "hash_hmac('sha256'" in track_php,
            "Anonimização de IP com HMAC-SHA256 e segredo em api/track.php",
        )
        self.assert_true(
            "$e->getMessage()" not in track_php.split("json_encode")[-1],
            "Ocultação de detalhes internos de exceção na resposta JSON de api/track.php",
        )
        lead_status_php = (ROOT / "admin/api/lead-status.php").read_text(encoding="utf-8")
        self.assert_true(
            "$e->getMessage()" not in lead_status_php.split("json_encode")[-1],
            "Ocultação de detalhes internos de exceção em admin/api/lead-status.php",
        )

        root_htaccess = (ROOT / ".htaccess").read_text(encoding="utf-8")
        self.assert_true(
            "RewriteRule ^(core|includes|data|tests)(/|$) - [F,L]" in root_htaccess,
            "Root .htaccess bloqueia diretórios internos (core, includes, data, tests)",
        )

    def test_database_and_regression(self) -> None:
        db_path = ROOT / "data/feito.sqlite"
        self.assert_true(db_path.is_file(), "Banco de dados data/feito.sqlite preservado e acessível")

        conn = sqlite3.connect(f"file:{db_path}?mode=ro", uri=True)
        cur = conn.cursor()
        cur.execute("SELECT name FROM sqlite_master WHERE type='table'")
        tables = {r[0] for r in cur.fetchall()}
        expected = {
            "admins", "leads", "lead_history", "categories", "posts",
            "tags", "post_tags", "audit_logs", "settings", "login_attempts", "page_views"
        }
        self.assert_true(expected.issubset(tables), "Todas as 11 tabelas do schema permanecem íntegras no SQLite")

        cur.execute("SELECT COUNT(*) FROM posts WHERE status = 'PUBLICADO'")
        pub_posts = cur.fetchone()[0]
        self.assert_true(pub_posts >= 1, f"Artigos publicados no blog preservados ({pub_posts} artigos)")
        conn.close()


if __name__ == "__main__":
    suite = SecurityTestSuite()
    sys.exit(suite.run_all())

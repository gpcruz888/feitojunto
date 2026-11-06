<?php
/**
 * Security Service — feito.
 * Centraliza headers HTTP de segurança, gestão segura de sessão,
 * sanitização HTML/CSV/Mail, resolução segura de IP e Rate Limiting.
 * Referências: OWASP Top 10:2025 & OWASP ASVS 5.0
 */
require_once __DIR__ . '/Env.php';

class Security {
    private static bool $headersSent = false;
    private static bool $errorConfigured = false;

    /**
     * Configura tratamento seguro de erros (oculta stack traces em produção)
     */
    public static function configureErrorHandling(): void {
        if (self::$errorConfigured) {
            return;
        }
        Env::load();
        $env = strtolower((string)Env::get('APP_ENV', 'production'));
        $debug = strtolower((string)Env::get('APP_DEBUG', 'false'));

        if ($env !== 'local' && $env !== 'development' && $debug !== 'true' && $debug !== '1') {
            @ini_set('display_errors', '0');
            @ini_set('display_startup_errors', '0');
            @ini_set('log_errors', '1');
        }
        self::$errorConfigured = true;
    }

    /**
     * Verifica se a requisição atual está sob HTTPS
     */
    public static function isHttps(): bool {
        if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') {
            return true;
        }
        if (Env::get('TRUST_PROXY_HEADERS', 'false') === 'true') {
            if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
                return true;
            }
        }
        return isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443;
    }

    /**
     * Inicializa sessão PHP com parâmetros endurecidos (OWASP ASVS V3)
     */
    public static function startSecureSession(): void {
        self::configureErrorHandling();

        if (session_status() === PHP_SESSION_NONE) {
            $isHttps = self::isHttps();

            @ini_set('session.use_strict_mode', '1');
            @ini_set('session.use_only_cookies', '1');
            @ini_set('session.use_trans_sid', '0');
            @ini_set('session.cookie_httponly', '1');
            @ini_set('session.cookie_samesite', 'Strict');
            if ($isHttps) {
                @ini_set('session.cookie_secure', '1');
            }

            if (!headers_sent()) {
                session_set_cookie_params([
                    'lifetime' => 0,
                    'path' => '/',
                    'domain' => '',
                    'secure' => $isHttps,
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]);
                session_start();
            } else {
                @session_start();
            }
        }
    }

    /**
     * Envia cabeçalhos HTTP de proteção (CSP, HSTS, Clickjacking, Sniffing, Isolamento)
     */
    public static function sendHeaders(bool $isAdmin = false): void {
        self::configureErrorHandling();

        if (self::$headersSent || headers_sent()) {
            return;
        }

        @header_remove('X-Powered-By');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=(), focus-without-user-activation=()');
        header('Cross-Origin-Opener-Policy: same-origin-allow-popups');

        if (self::isHttps()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }

        if ($isAdmin) {
            header('X-Frame-Options: DENY');
            header('Cross-Origin-Resource-Policy: same-origin');
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header(
                "Content-Security-Policy: " .
                "default-src 'self'; " .
                "script-src 'self' 'unsafe-inline'; " .
                "style-src 'self' 'unsafe-inline' https://api.fontshare.com https://cdn.fontshare.com; " .
                "font-src 'self' data: https://api.fontshare.com https://cdn.fontshare.com; " .
                "img-src 'self' data: blob: https:; " .
                "connect-src 'self'; " .
                "object-src 'none'; " .
                "base-uri 'self'; " .
                "frame-ancestors 'none'; " .
                "form-action 'self'"
            );
        } else {
            header('X-Frame-Options: SAMEORIGIN');
            header(
                "Content-Security-Policy: " .
                "default-src 'self'; " .
                "script-src 'self' 'unsafe-inline' https://cdn-cookieyes.com https://*.cookieyes.com; " .
                "style-src 'self' 'unsafe-inline' https://api.fontshare.com https://cdn.fontshare.com; " .
                "font-src 'self' data: https://api.fontshare.com https://cdn.fontshare.com; " .
                "img-src 'self' data: blob: https:; " .
                "media-src 'self' blob:; " .
                "connect-src 'self' https://cdn-cookieyes.com https://*.cookieyes.com https://formsubmit.co; " .
                "object-src 'none'; " .
                "base-uri 'self'; " .
                "frame-ancestors 'self'; " .
                "form-action 'self'"
            );
        }

        self::$headersSent = true;
    }

    /**
     * Obtém o IP real do cliente com proteção contra IP Spoofing via X-Forwarded-For
     */
    public static function getClientIp(): string {
        Env::load();
        $trustProxy = strtolower((string)Env::get('TRUST_PROXY_HEADERS', 'false')) === 'true';

        $candidates = [];
        if ($trustProxy) {
            if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
                $candidates[] = $_SERVER['HTTP_CF_CONNECTING_IP'];
            }
            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $parts = explode(',', (string)$_SERVER['HTTP_X_FORWARDED_FOR']);
                $candidates[] = trim($parts[0]);
            }
        }

        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $candidates[] = $_SERVER['REMOTE_ADDR'];
        }

        foreach ($candidates as $ip) {
            $ip = trim((string)$ip);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        return '0.0.0.0';
    }

    /**
     * Verifica se um identificador excedeu o limite de requisições na janela de tempo (em minutos)
     */
    public static function isRateLimited(PDO $db, string $scope, string $identifier, int $maxAttempts, int $windowMinutes): bool {
        $scopedIp = "rate:{$scope}:" . self::getClientIp();
        $scopedId = "rate:{$scope}:" . substr($identifier, 0, 180);

        // Limpeza probabilística (5% das chamadas) de registros antigos (> 24h)
        if (random_int(1, 20) === 1) {
            try {
                $db->exec("DELETE FROM login_attempts WHERE attempted_at < datetime('now', '-24 hours')");
            } catch (Exception $e) {}
        }

        $stmt = $db->prepare("
            SELECT COUNT(*) FROM login_attempts
            WHERE (ip_address = ? OR email = ?)
              AND attempted_at > datetime('now', '-' || ? || ' minutes')
        ");
        $stmt->execute([$scopedIp, $scopedId, max(1, $windowMinutes)]);
        $count = (int)$stmt->fetchColumn();

        return $count >= $maxAttempts;
    }

    /**
     * Registra um evento no contador de Rate Limiting
     */
    public static function recordRateLimitHit(PDO $db, string $scope, string $identifier): void {
        $scopedIp = "rate:{$scope}:" . self::getClientIp();
        $scopedId = "rate:{$scope}:" . substr($identifier, 0, 180);

        $stmt = $db->prepare("INSERT INTO login_attempts (ip_address, email) VALUES (?, ?)");
        $stmt->execute([$scopedIp, $scopedId]);
    }

    /**
     * Sanitiza strings usadas em cabeçalhos de e-mail para prevenir Email Header Injection (CRLF)
     */
    public static function sanitizeMailHeader(?string $value): string {
        if ($value === null) {
            return '';
        }
        $clean = preg_replace('/[\r\n\0\x0B]+/', ' ', $value);
        return trim($clean ?? '');
    }

    /**
     * Previne CSV Injection (Formula Injection) ao exportar dados para Excel/Calc
     */
    public static function sanitizeCsvValue(mixed $value): string {
        $str = (string)($value ?? '');
        $trimmed = ltrim($str, " \t\r\n");
        if ($trimmed !== '' && in_array($trimmed[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $str;
        }
        return $str;
    }

    /**
     * Valida e normaliza URLs de imagens de capa (bloqueia javascript:, data: e CSS injection)
     */
    public static function sanitizeCoverUrl(?string $url): string {
        $url = trim((string)($url ?? ''));
        if ($url === '') {
            return '';
        }

        // Remove caracteres de controle e aspas/parênteses que quebram contextos CSS url()
        $url = preg_replace('/[\x00-\x1F\x7F"\'<>\\\\]/', '', $url) ?? '';
        if ($url === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $url)) {
            return filter_var($url, FILTER_VALIDATE_URL) ? $url : '';
        }

        if (str_starts_with($url, '/')) {
            if (str_starts_with($url, '//')) {
                return '';
            }
            return $url;
        }

        return '/uploads/' . ltrim($url, '/');
    }

    /**
     * Sanitiza HTML de artigos do blog preservando formatação editorial e removendo vetores XSS
     */
    public static function sanitizeHtml(?string $html): string {
        $html = (string)($html ?? '');
        if (trim($html) === '') {
            return '';
        }

        // Remove blocos perigosos (script, style, iframe, object, embed, svg, form, etc.)
        $dangerousBlocks = [
            'script', 'style', 'iframe', 'object', 'embed', 'applet',
            'svg', 'math', 'form', 'input', 'button', 'select', 'textarea',
            'meta', 'link', 'base', 'frame', 'frameset', 'template'
        ];
        foreach ($dangerousBlocks as $tag) {
            $html = preg_replace('#<' . $tag . '\b[^>]*>.*?</' . $tag . '>#is', '', $html) ?? $html;
            $html = preg_replace('#<' . $tag . '\b[^>]*\/?>#is', '', $html) ?? $html;
        }

        $allowedTags = [
            'p', 'br', 'hr', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'strong', 'b', 'em', 'i', 'u', 's', 'mark', 'small', 'sub', 'sup',
            'ul', 'ol', 'li', 'blockquote', 'pre', 'code',
            'a', 'img', 'figure', 'figcaption',
            'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td',
            'div', 'span', 'section', 'article'
        ];

        $allowedString = '<' . implode('><', $allowedTags) . '>';
        $html = strip_tags($html, $allowedString);

        // Se DOMDocument estiver disponível, faz sanitização estrutural de atributos
        if (class_exists('DOMDocument')) {
            $dom = new DOMDocument('1.0', 'UTF-8');
            $prev = libxml_use_internal_errors(true);
            $encodedHtml = function_exists('mb_encode_numericentity')
                ? mb_encode_numericentity($html, [0x80, 0x10FFFF, 0, ~0], 'UTF-8')
                : $html;
            $wrapped = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body><div id="__sanitize_root">' . $encodedHtml . '</div></body></html>';
            $loaded = $dom->loadHTML($wrapped, LIBXML_NONET);
            libxml_clear_errors();
            libxml_use_internal_errors($prev);

            if ($loaded) {
                $allowedAttrs = [
                    'href', 'src', 'alt', 'title', 'class', 'id',
                    'target', 'rel', 'width', 'height', 'loading', 'decoding',
                    'colspan', 'rowspan'
                ];

                $xpath = new DOMXPath($dom);
                $nodes = $xpath->query('//*');
                if ($nodes) {
                    foreach ($nodes as $node) {
                        if (!($node instanceof DOMElement)) {
                            continue;
                        }
                        if ($node->getAttribute('id') === '__sanitize_root') {
                            continue;
                        }

                        $attrsToRemove = [];
                        foreach ($node->attributes as $attr) {
                            $attrName = strtolower($attr->nodeName);
                            $attrVal = trim($attr->nodeValue);

                            if (str_starts_with($attrName, 'on') || !in_array($attrName, $allowedAttrs, true)) {
                                $attrsToRemove[] = $attr->nodeName;
                                continue;
                            }

                            if ($attrName === 'href' || $attrName === 'src') {
                                $normalized = preg_replace('/[\x00-\x20]+/', '', strtolower($attrVal)) ?? '';
                                if (
                                    $normalized !== '' &&
                                    !str_starts_with($normalized, 'http://') &&
                                    !str_starts_with($normalized, 'https://') &&
                                    !str_starts_with($normalized, 'mailto:') &&
                                    !str_starts_with($normalized, '/') &&
                                    !str_starts_with($normalized, '#')
                                ) {
                                    $attrsToRemove[] = $attr->nodeName;
                                } elseif (str_starts_with($normalized, '//')) {
                                    $attrsToRemove[] = $attr->nodeName;
                                }
                            }
                        }

                        foreach ($attrsToRemove as $rem) {
                            $node->removeAttribute($rem);
                        }

                        if (strtolower($node->nodeName) === 'a' && strtolower($node->getAttribute('target')) === '_blank') {
                            $node->setAttribute('rel', 'noopener noreferrer');
                        }
                    }
                }

                $root = $dom->getElementById('__sanitize_root');
                if ($root) {
                    $output = '';
                    foreach ($root->childNodes as $child) {
                        $output .= $dom->saveHTML($child);
                    }
                    return $output;
                }
            }
        }

        // Fallback regex caso ext-dom não esteja ativa
        $html = preg_replace('/\s+on[a-z]+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? $html;
        $html = preg_replace('/\s+(?:href|src)\s*=\s*(?:"\s*(?:javascript|vbscript|data):[^"]*"|\'\s*(?:javascript|vbscript|data):[^\']*\'|(?:javascript|vbscript|data):[^\s>]+)/i', '', $html) ?? $html;
        return $html;
    }
}

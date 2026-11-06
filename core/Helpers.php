<?php
/**
 * Helpers — feito.
 * Funções utilitárias reutilizáveis para o admin e blog.
 */

require_once __DIR__ . '/Security.php';

class Helpers {
    /**
     * Sanitiza saída para exibição segura em HTML
     */
    public static function e(?string $str): string {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }

    /**
     * Transforma texto em slug amigável para URLs
     */
    public static function slugify(string $text): string {
        // Translitera caracteres acentuados
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        // Remove caracteres que não são letras ou números
        $text = preg_replace('~[^-\w]+~', '', $text);
        // Remove traços repetidos
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        // Converte para minúsculas
        $text = strtolower($text);

        return empty($text) ? 'n-a-' . time() : $text;
    }

    /**
     * Formata data para o padrão brasileiro
     */
    public static function formatDate(?string $datetime, bool $includeTime = true): string {
        if (!$datetime) return '—';
        $timestamp = strtotime($datetime);
        if (!$timestamp) return '—';

        if ($includeTime) {
            return date('d/m/Y \à\s H:i', $timestamp);
        }
        return date('d/m/Y', $timestamp);
    }

    /**
     * Data editorial do blog: 06 SET 2026
     */
    public static function formatBlogDate(?string $datetime): string {
        if (!$datetime) return '';
        $timestamp = strtotime($datetime);
        if (!$timestamp) return '';

        $months = ['JAN', 'FEV', 'MAR', 'ABR', 'MAI', 'JUN', 'JUL', 'AGO', 'SET', 'OUT', 'NOV', 'DEZ'];
        $month = $months[(int)date('n', $timestamp) - 1];
        return date('d', $timestamp) . ' ' . $month . ' ' . date('Y', $timestamp);
    }

    /**
     * Tempo de leitura estimado (palavras / 200)
     */
    public static function readTime(?string $content, int $fallback = 5): int {
        $words = str_word_count(strip_tags($content ?? ''));
        if ($words < 40) return $fallback;
        return max(1, (int)ceil($words / 200));
    }

    /**
     * Normaliza URL de capa (absoluta, relativa ou arquivo em /uploads) com validação de segurança
     */
    public static function coverUrl(?string $path): string {
        return Security::sanitizeCoverUrl($path);
    }

    /**
     * Formata tempo relativo em português ("há 10 minutos", "ontem", etc.)
     */
    public static function timeAgo(?string $datetime): string {
        if (!$datetime) return '—';
        $timestamp = strtotime($datetime);
        if (!$timestamp) return '—';

        $diff = time() - $timestamp;

        if ($diff < 60) {
            return 'agora mesmo';
        } elseif ($diff < 3600) {
            $mins = max(1, floor($diff / 60));
            return "há {$mins} min";
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return "há {$hours} h";
        } elseif ($diff < 172800) {
            return 'ontem';
        } else {
            $days = floor($diff / 86400);
            if ($days < 30) {
                return "há {$days} dias";
            }
            return date('d/m/Y', $timestamp);
        }
    }

    /**
     * Retorna a classe e texto do badge para o status do Lead
     */
    public static function leadStatusBadge(string $status): array {
        return match ($status) {
            'NOVO' => [
                'label' => 'Novo',
                'bg' => 'rgba(255, 90, 0, 0.15)',
                'color' => '#FF5A00',
                'border' => 'rgba(255, 90, 0, 0.3)'
            ],
            'EM_CONTATO' => [
                'label' => 'Em Contato',
                'bg' => 'rgba(59, 130, 246, 0.15)',
                'color' => '#60a5fa',
                'border' => 'rgba(59, 130, 246, 0.3)'
            ],
            'QUALIFICADO' => [
                'label' => 'Qualificado',
                'bg' => 'rgba(168, 85, 247, 0.15)',
                'color' => '#c084fc',
                'border' => 'rgba(168, 85, 247, 0.3)'
            ],
            'CONVERTIDO' => [
                'label' => 'Convertido',
                'bg' => 'rgba(34, 197, 94, 0.15)',
                'color' => '#4ade80',
                'border' => 'rgba(34, 197, 94, 0.3)'
            ],
            'PERDIDO' => [
                'label' => 'Perdido',
                'bg' => 'rgba(239, 68, 68, 0.15)',
                'color' => '#f87171',
                'border' => 'rgba(239, 68, 68, 0.3)'
            ],
            'ARQUIVADO' => [
                'label' => 'Arquivado',
                'bg' => 'rgba(255, 255, 255, 0.08)',
                'color' => '#a1a1aa',
                'border' => 'rgba(255, 255, 255, 0.12)'
            ],
            default => [
                'label' => $status,
                'bg' => 'rgba(255, 255, 255, 0.08)',
                'color' => '#ffffff',
                'border' => 'rgba(255, 255, 255, 0.12)'
            ],
        };
    }

    /**
     * Retorna a classe e texto do badge para o status do Post
     */
    public static function postStatusBadge(string $status): array {
        return match ($status) {
            'PUBLICADO' => [
                'label' => 'Publicado',
                'bg' => 'rgba(34, 197, 94, 0.15)',
                'color' => '#4ade80',
                'border' => 'rgba(34, 197, 94, 0.3)'
            ],
            'RASCUNHO' => [
                'label' => 'Rascunho',
                'bg' => 'rgba(234, 179, 8, 0.15)',
                'color' => '#facc15',
                'border' => 'rgba(234, 179, 8, 0.3)'
            ],
            'ARQUIVADO' => [
                'label' => 'Arquivado',
                'bg' => 'rgba(255, 255, 255, 0.08)',
                'color' => '#a1a1aa',
                'border' => 'rgba(255, 255, 255, 0.12)'
            ],
            default => [
                'label' => $status,
                'bg' => 'rgba(255, 255, 255, 0.08)',
                'color' => '#ffffff',
                'border' => 'rgba(255, 255, 255, 0.12)'
            ]
        };
    }
}

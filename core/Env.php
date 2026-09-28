<?php
/**
 * Env Helper — feito.
 * Carrega variáveis de ambiente de um arquivo .env simples sem dependências externas.
 */
class Env {
    private static bool $loaded = false;
    private static array $vars = [];

    public static function load(string $path = __DIR__ . '/../.env'): void {
        if (self::$loaded) {
            return;
        }

        if (file_exists($path)) {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }

                if (str_contains($line, '=')) {
                    [$key, $val] = explode('=', $line, 2);
                    $key = trim($key);
                    $val = trim($val);
                    // Remove aspas envolventes se houver
                    if ((str_starts_with($val, '"') && str_ends_with($val, '"')) ||
                        (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
                        $val = substr($val, 1, -1);
                    }
                    self::$vars[$key] = $val;
                    $_ENV[$key] = $val;
                    putenv("{$key}={$val}");
                }
            }
        }

        self::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed {
        if (!self::$loaded) {
            self::load();
        }

        if (array_key_exists($key, self::$vars)) {
            return self::$vars[$key];
        }

        $envVal = getenv($key);
        if ($envVal !== false) {
            return $envVal;
        }

        return $default;
    }
}

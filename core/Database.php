<?php
/**
 * Database Singleton — feito.
 * Provedor de conexão segura PDO com suporte a SQLite (padrão) e MySQL.
 */
require_once __DIR__ . '/Env.php';

class Database {
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            Env::load();

            $driver = Env::get('DB_CONNECTION', 'sqlite');

            try {
                if ($driver === 'mysql') {
                    $host = Env::get('DB_HOST', 'localhost');
                    $port = Env::get('DB_PORT', '3306');
                    $dbname = Env::get('DB_DATABASE', 'feito_cms');
                    $user = Env::get('DB_USERNAME', 'root');
                    $pass = Env::get('DB_PASSWORD', '');
                    $charset = 'utf8mb4';

                    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
                    self::$instance = new PDO($dsn, $user, $pass, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                    ]);
                } else {
                    // SQLite por padrão
                    $dbPath = Env::get('DB_PATH', 'data/feito.sqlite');
                    if (!str_starts_with($dbPath, '/')) {
                        $dbPath = __DIR__ . '/../' . $dbPath;
                    }

                    $dir = dirname($dbPath);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }

                    $dsn = "sqlite:" . $dbPath;
                    self::$instance = new PDO($dsn, null, null, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]);

                    // Ativa chaves estrangeiras e modo WAL para excelente concorrência e velocidade
                    self::$instance->exec("PRAGMA foreign_keys = ON;");
                    self::$instance->exec("PRAGMA journal_mode = WAL;");
                }
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                throw new Exception("Falha na conexão com o banco de dados. Verifique a configuração.");
            }
        }

        return self::$instance;
    }
}

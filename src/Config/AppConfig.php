<?php
declare(strict_types=1);

namespace App\Config;

final class AppConfig
{
    private static ?self $instance = null;

    private function __construct(
        public readonly string $dbHost,
        public readonly string $dbUser,
        public readonly string $dbPassword,
        public readonly string $dbName,
        public readonly string $dbCharset = 'utf8mb4',
    ) {
    }

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = self::fromEnv();
        }
        return self::$instance;
    }

    private static function fromEnv(): self
    {
        $envPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';
        if (is_file($envPath)) {
            foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                if (str_starts_with(trim($line), '#')) {
                    continue;
                }
                [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
                if ($key !== '') {
                    $_ENV[trim($key)] = trim($value);
                }
            }
        }

        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $user = $_ENV['DB_USER'] ?? 'root';
        $password = $_ENV['DB_PASS'] ?? $_ENV['DB_PASSWORD'] ?? '';
        $name = $_ENV['DB_NAME'] ?? 'bionrgg';
        $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

        return new self($host, $user, $password, $name, $charset);
    }
}















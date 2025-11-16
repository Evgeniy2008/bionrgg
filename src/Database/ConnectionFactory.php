<?php
declare(strict_types=1);

namespace App\Database;

use App\Config\AppConfig;
use PDO;
use PDOException;

final class ConnectionFactory
{
    public static function make(): PDO
    {
        $config = AppConfig::instance();
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $config->dbHost,
            $config->dbName,
            $config->dbCharset
        );

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            return new PDO($dsn, $config->dbUser, $config->dbPassword, $options);
        } catch (PDOException $exception) {
            throw new PDOException('Database connection failed: ' . $exception->getMessage(), (int)$exception->getCode(), $exception);
        }
    }
}

















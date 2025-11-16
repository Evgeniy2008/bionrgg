<?php
declare(strict_types=1);

namespace App;

use App\Http\JsonResponse;
use Throwable;

final class Bootstrap
{
    private static bool $bootstrapped = false;

    public static function init(): void
    {
        if (self::$bootstrapped) {
            return;
        }

        self::registerAutoloader();
        self::configureEnvironment();
        self::registerErrorHandlers();

        self::$bootstrapped = true;
    }

    private static function registerAutoloader(): void
    {
        spl_autoload_register(static function (string $class): void {
            $prefix = 'App\\';
            if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
                return;
            }

            $relative = substr($class, strlen($prefix));
            $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
            $baseDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
            $file = $baseDir . $relativePath;

            if (is_file($file)) {
                require_once $file;
            }
        });
    }

    private static function configureEnvironment(): void
    {
        error_reporting(E_ALL);
        ini_set('display_errors', '0');
        ini_set('log_errors', '1');
        ini_set('default_charset', 'UTF-8');
        date_default_timezone_set('UTC');
    }

    private static function registerErrorHandlers(): void
    {
        set_exception_handler(static function (Throwable $throwable): void {
            error_log(sprintf(
                '[Unhandled] %s: %s in %s:%d',
                $throwable::class,
                $throwable->getMessage(),
                $throwable->getFile(),
                $throwable->getLine()
            ));

            JsonResponse::fromThrowable($throwable)->send();
        });

        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });
    }
}























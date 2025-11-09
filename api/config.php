<?php
declare(strict_types=1);

if (!defined('BIONRGG_BOOTSTRAPPED')) {
    define('BIONRGG_BOOTSTRAPPED', true);

    // Global PHP runtime configuration
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('default_charset', 'UTF-8');

    if (function_exists('mysqli_report')) {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    }

    if (ob_get_level() === 0) {
        ob_start();
    }

    /**
     * Convert PHP warnings/notices into ErrorException to keep JSON responses clean.
     */
    set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        throw new ErrorException($message, 0, $severity, $file, $line);
    });

    /**
     * Handle uncaught throwables and ensure JSON response.
     */
    set_exception_handler(static function (Throwable $throwable): void {
        error_log(sprintf(
            'Unhandled exception: %s in %s:%d',
            $throwable->getMessage(),
            $throwable->getFile(),
            $throwable->getLine()
        ));

        if (!headers_sent()) {
            sendJSONResponse([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $throwable->getMessage()
            ], 500);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Internal server error'
            ], JSON_UNESCAPED_UNICODE);
        }
        exit();
    });

    /**
     * Catch fatal errors to keep JSON output consistent.
     */
    register_shutdown_function(static function (): void {
        $error = error_get_last();
        if (!$error) {
            return;
        }

        $fatalErrors = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
        if (!in_array($error['type'], $fatalErrors, true)) {
            return;
        }

        error_log(sprintf(
            'Fatal error: %s in %s:%d',
            $error['message'],
            $error['file'],
            $error['line']
        ));

        if (!headers_sent()) {
            sendJSONResponse([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Internal server error'
            ], JSON_UNESCAPED_UNICODE);
        }
    });
}

// Конфигурация базы данных
define('DB_HOST', 'localhost');
define('DB_USER', 'u743896667_bionrgg');
define('DB_PASS', 'Godzila#9145');
define('DB_NAME', 'u743896667_bionrgg');

function clearAllOutputBuffers(): void {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
}

// Функция для подключения к базе данных
function getDBConnection(): mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    $conn->set_charset("utf8mb4");
    return $conn;
}

function dbColumnExists(mysqli $conn, string $table, string $column): bool {
    $stmt = $conn->prepare("
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND COLUMN_NAME = ?
        LIMIT 1
    ");
    if (!$stmt) {
        throw new Exception('Failed to inspect schema: ' . $conn->error);
    }
    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result !== false && $result->num_rows > 0;
    $stmt->close();

    return $exists;
}

function ensureCoreSchema(mysqli $conn): void {
    static $checked = false;
    if ($checked) {
        return;
    }

    $missing = [];

    foreach (['id', 'password_hash'] as $column) {
        if (!dbColumnExists($conn, 'users', $column)) {
            $missing[] = "users.{$column}";
        }
    }

    foreach (['user_id', 'profile_type', 'extraLinks', 'customLogo', 'customLogoPosition', 'customLogoSize'] as $column) {
        if (!dbColumnExists($conn, 'users_info', $column)) {
            $missing[] = "users_info.{$column}";
        }
    }

    if ($missing !== []) {
        throw new Exception(
            'Database schema is outdated. Missing columns: ' .
            implode(', ', $missing) .
            '. Please recreate the database using database_v2.sql.'
        );
    }

    $checked = true;
}

function fetchUserByUsername(mysqli $conn, string $username): ?array {
    $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc() ?: null;
    $stmt->close();

    return $user;
}

function verifyPassword(string $password, string $hash): bool {
    if ($hash === '') {
        return false;
    }
    if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
        return password_verify($password, $hash);
    }
    return password_verify($password, $hash);
}

function authenticateUser(mysqli $conn, string $username, string $password): array {
    $user = fetchUserByUsername($conn, $username);
    if (!$user) {
        throw new Exception('User not found');
    }

    $hash = (string)$user['password_hash'];
    if (!verifyPassword($password, $hash)) {
        throw new Exception('Invalid credentials');
    }

    if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        if ($newHash !== false) {
            $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->bind_param('si', $newHash, $user['id']);
            $stmt->execute();
            $stmt->close();
            $user['password_hash'] = $newHash;
        }
    }

    return $user;
}

// Функция для отправки JSON ответа
function sendJSONResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    if (ob_get_length() !== false && ob_get_length() > 0) {
        ob_clean();
    }

    $options = JSON_UNESCAPED_UNICODE;
    if (defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
        $options |= JSON_INVALID_UTF8_SUBSTITUTE;
    }

    $json = json_encode($data, $options);

    if ($json === false) {
        $errorMessage = json_last_error_msg();
        error_log('JSON encode error: ' . $errorMessage);

        $fallback = [
            'success' => false,
            'message' => 'Server response encoding error',
            'details' => $errorMessage
        ];

        $json = json_encode($fallback, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            $json = '{"success":false,"message":"Server response encoding error"}';
        }
        http_response_code(500);
    }

    echo $json;
    exit();
}

// Обработка preflight запросов
function handlePreflight() {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

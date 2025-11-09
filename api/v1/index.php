<?php

declare(strict_types=1);

$root = dirname(__DIR__, 1);
$vendorAutoload = $root . '/vendor/autoload.php';
if (is_file($vendorAutoload)) {
    require_once $vendorAutoload;
}

require_once __DIR__ . '/../../app/autoload.php';

use App\Application;

$envPath = $root . '/.env';

try {
    $app = new Application($envPath);
    $app->run();
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Server error',
    ]);
}



<?php
declare(strict_types=1);

namespace App\Http;

use App\Exceptions\HttpException;
use Throwable;

final class JsonResponse
{
    private function __construct(
        private readonly int $status,
        private readonly array $payload
    ) {
    }

    public static function success(array $data = [], int $status = 200): self
    {
        return new self($status, ['success' => true] + $data);
    }

    public static function error(string $message, int $status = 400, array $context = []): self
    {
        return new self($status, ['success' => false, 'message' => $message] + $context);
    }

    public static function fromThrowable(Throwable $throwable): self
    {
        if ($throwable instanceof HttpException) {
            return self::error($throwable->getMessage(), $throwable->getStatus(), $throwable->getContext());
        }

        return self::error('Internal server error', 500, [
            'error' => $throwable->getMessage(),
        ]);
    }

    public function send(): void
    {
        if (!headers_sent()) {
            http_response_code($this->status);
            header('Content-Type: application/json; charset=utf-8');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
        }

        echo json_encode($this->payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit();
    }
}















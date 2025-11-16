<?php
declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class HttpException extends RuntimeException
{
    public function __construct(
        private readonly int $status,
        string $message,
        private readonly array $context = []
    ) {
        parent::__construct($message);
    }

    public static function badRequest(string $message, array $context = []): self
    {
        return new self(400, $message, $context);
    }

    public static function unauthorized(string $message = 'Unauthorized', array $context = []): self
    {
        return new self(401, $message, $context);
    }

    public static function forbidden(string $message = 'Forbidden', array $context = []): self
    {
        return new self(403, $message, $context);
    }

    public static function notFound(string $message = 'Not Found', array $context = []): self
    {
        return new self(404, $message, $context);
    }

    public static function conflict(string $message, array $context = []): self
    {
        return new self(409, $message, $context);
    }

    public static function unprocessable(string $message, array $context = []): self
    {
        return new self(422, $message, $context);
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}

















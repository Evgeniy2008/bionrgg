<?php
declare(strict_types=1);

namespace App\Http;

final class Request
{
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $query,
        private readonly array $headers,
        private readonly array $body,
        private readonly array $rawBody
    ) {
    }

    public static function fromGlobals(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

        if ($scriptName !== '' && str_starts_with($path, $scriptName)) {
            $path = substr($path, strlen($scriptName)) ?: '/';
        } elseif ($baseDir !== '' && $baseDir !== '/' && str_starts_with($path, $baseDir)) {
            $path = substr($path, strlen($baseDir)) ?: '/';
        }

        if ($path === '') {
            $path = '/';
        }

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $normalized = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$normalized] = (string)$value;
            }
        }

        $bodyContent = file_get_contents('php://input');
        $decodedJson = null;
        if ($bodyContent !== false && $bodyContent !== '') {
            $decodedJson = json_decode($bodyContent, true);
        }

        if (is_array($decodedJson)) {
            $body = $decodedJson;
        } elseif (!empty($_POST)) {
            $body = $_POST;
        } else {
            $body = [];
        }

        return new self(
            $method,
            $path,
            $_GET,
            $headers,
            $body,
            [
                'content' => $bodyContent,
                'json' => $decodedJson,
            ]
        );
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function allQuery(): array
    {
        return $this->query;
    }

    public function header(string $key, mixed $default = null): mixed
    {
        return $this->headers[$key] ?? $default;
    }

    public function body(string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->body;
        }
        return $this->body[$key] ?? $default;
    }

    public function rawBody(): array
    {
        return $this->rawBody;
    }
}


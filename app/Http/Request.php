<?php

namespace App\Http;

class Request
{
    public function __construct(
        private array $get,
        private array $post,
        private array $server,
        private array $headers,
        private array $files,
        private array $cookies,
        private string $body,
        private array $attributes = []
    ) {
    }

    public static function capture(): self
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$name] = $value;
            }
        }

        $body = file_get_contents('php://input');

        return new self(
            $_GET,
            $_POST,
            $_SERVER,
            $headers,
            $_FILES,
            $_COOKIE,
            $body === false ? '' : $body
        );
    }

    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);

        return $path ?: '/';
    }

    public function json(): array
    {
        if ($this->body === '') {
            return [];
        }

        $decoded = json_decode($this->body, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function header(string $name, ?string $default = null): ?string
    {
        return $this->headers[$name] ?? $default;
    }

    public function cookie(string $name, ?string $default = null): ?string
    {
        return $this->cookies[$name] ?? $default;
    }

    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    public function query(string $name, mixed $default = null): mixed
    {
        return $this->get[$name] ?? $default;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function file(string $name): ?array
    {
        return $this->files[$name] ?? null;
    }
}



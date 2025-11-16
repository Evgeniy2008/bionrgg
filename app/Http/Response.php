<?php

namespace App\Http;

class Response
{
    private int $status = 200;
    private array $headers = [];

    public function status(int $code): self
    {
        $this->status = $code;
        return $this;
    }

    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function json(array $payload): void
    {
        $this->header('Content-Type', 'application/json; charset=utf-8');
        $this->send(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public function send(string $body = ''): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }
        echo $body;
    }
}







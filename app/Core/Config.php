<?php

namespace App\Core;

use RuntimeException;

class Config
{
    private array $values = [];

    public function __construct(string $envPath)
    {
        $this->values = $this->loadEnv($envPath);
    }

    public function get(string $key, ?string $default = null): ?string
    {
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        if (array_key_exists($key, $this->values)) {
            return $this->values[$key];
        }

        return $default;
    }

    public function require(string $key): string
    {
        $value = $this->get($key);
        if ($value === null) {
            throw new RuntimeException("Environment variable '{$key}' is not set.");
        }

        return $value;
    }

    private function loadEnv(string $envPath): array
    {
        $values = [];

        if (!is_file($envPath)) {
            return $values;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return $values;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
            $key = trim($key);
            $value = trim($value);

            if ($key === '') {
                continue;
            }

            $values[$key] = $value;
        }

        return $values;
    }
}





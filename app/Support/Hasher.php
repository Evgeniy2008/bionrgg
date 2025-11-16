<?php

namespace App\Support;

class Hasher
{
    public function hash(string $value): string
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }

    public function check(string $value, string $hash): bool
    {
        return password_verify($value, $hash);
    }
}







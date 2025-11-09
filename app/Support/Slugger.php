<?php

namespace App\Support;

class Slugger
{
    public static function slug(string $value): string
    {
        $value = mb_strtolower($value, 'UTF-8');
        $value = preg_replace('/[^a-z0-9а-яіїєґ\s-]/u', '', $value) ?? '';
        $value = preg_replace('/[\s_-]+/u', '-', trim($value)) ?? '';
        $value = trim($value, '-');

        return $value !== '' ? $value : 'profile';
    }
}





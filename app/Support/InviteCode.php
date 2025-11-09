<?php

namespace App\Support;

class InviteCode
{
    private const CHARSET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public static function generate(int $length = 8): string
    {
        $characters = self::CHARSET;
        $maxIndex = strlen($characters) - 1;
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, $maxIndex)];
        }

        return $code;
    }
}





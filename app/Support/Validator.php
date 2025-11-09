<?php

namespace App\Support;

use InvalidArgumentException;

class Validator
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, callable|array<int, callable>> $rules
     */
    public function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            $ruleSet = is_array($rule) ? $rule : [$rule];

            foreach ($ruleSet as $callable) {
                $result = $callable($value, $data);

                if ($result !== true) {
                    $errors[$field] = is_string($result) ? $result : 'Invalid value.';
                    break;
                }
            }
        }

        if (!empty($errors)) {
            throw new InvalidArgumentException(json_encode($errors, JSON_UNESCAPED_UNICODE));
        }

        return $data;
    }

    public static function requiredString(int $min = 1, int $max = PHP_INT_MAX): callable
    {
        return static function ($value) use ($min, $max) {
            if (!is_string($value)) {
                return 'Value must be a string.';
            }
            $length = mb_strlen(trim($value));
            if ($length < $min || $length > $max) {
                return "Value length must be between {$min} and {$max}.";
            }
            return true;
        };
    }

    public static function email(): callable
    {
        return static function ($value): bool|string {
            if (!is_string($value) || !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return 'Invalid email.';
            }
            return true;
        };
    }

    public static function optionalString(int $min = 0, int $max = PHP_INT_MAX): callable
    {
        return static function ($value) use ($min, $max): bool|string {
            if ($value === null || $value === '') {
                return true;
            }
            if (!is_string($value)) {
                return 'Value must be a string.';
            }
            $length = mb_strlen(trim($value));
            if ($length < $min || $length > $max) {
                return "Value length must be between {$min} and {$max}.";
            }
            return true;
        };
    }

    public static function optionalEmail(): callable
    {
        return static function ($value): bool|string {
            if ($value === null || $value === '') {
                return true;
            }
            if (!is_string($value) || !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return 'Invalid email.';
            }
            return true;
        };
    }

    public static function password(int $min = 8): callable
    {
        return static function ($value) use ($min): bool|string {
            if (!is_string($value) || strlen($value) < $min) {
                return "Password must be at least {$min} characters.";
            }
            return true;
        };
    }
}



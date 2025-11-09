<?php
declare(strict_types=1);

namespace App\Validation;

use App\Exceptions\ValidationException;

final class Validator
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, list<string>> $rules
     * @return array<string, mixed>
     */
    public static function validate(array $data, array $rules): array
    {
        $errors = [];
        $validated = [];

        foreach ($rules as $field => $fieldRules) {
            $valuePresent = array_key_exists($field, $data);
            $value = $data[$field] ?? null;

            foreach ($fieldRules as $rule) {
                $segments = explode(':', $rule, 2);
                $ruleName = $segments[0];
                $ruleParam = $segments[1] ?? null;

                if ($ruleName === 'required' && ($value === null || $value === '' && $value !== '0')) {
                    $errors[$field] = 'Field is required.';
                    break;
                }

                if (!$valuePresent && $ruleName !== 'required') {
                    continue;
                }

                switch ($ruleName) {
                    case 'string':
                        if ($value !== null && !is_string($value)) {
                            $errors[$field] = 'Must be a string.';
                        }
                        break;
                    case 'int':
                        if ($value !== null && filter_var($value, FILTER_VALIDATE_INT) === false) {
                            $errors[$field] = 'Must be an integer.';
                        }
                        break;
                    case 'min':
                        if (is_string($value) && strlen($value) < (int)$ruleParam) {
                            $errors[$field] = "Minimum length is {$ruleParam}.";
                        }
                        break;
                    case 'max':
                        if (is_string($value) && strlen($value) > (int)$ruleParam) {
                            $errors[$field] = "Maximum length is {$ruleParam}.";
                        }
                        break;
                    case 'regex':
                        if (is_string($value) && $ruleParam !== null && !preg_match('#' . $ruleParam . '#', $value)) {
                            $errors[$field] = 'Invalid format.';
                        }
                        break;
                    case 'in':
                        $allowed = $ruleParam ? explode(',', $ruleParam) : [];
                        if ($value !== null && !in_array((string)$value, $allowed, true)) {
                            $errors[$field] = 'Invalid value.';
                        }
                        break;
                }

                if (isset($errors[$field])) {
                    break;
                }
            }

            if (!$valuePresent) {
                continue;
            }

            $validated[$field] = $value;
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return $validated;
    }
}















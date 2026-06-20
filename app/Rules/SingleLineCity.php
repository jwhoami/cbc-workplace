<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SingleLineCity implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null) {
            return;
        }

        if (! is_string($value)) {
            $fail('La ciudad debe ser un texto.');

            return;
        }

        $length = mb_strlen($value);
        if ($length < 1 || $length > 80) {
            $fail('La ciudad debe tener entre 1 y 80 caracteres.');

            return;
        }

        if (preg_match('/[\x00-\x1F\x7F]/u', $value)) {
            $fail('La ciudad no puede contener saltos de línea ni caracteres de control.');
        }
    }
}

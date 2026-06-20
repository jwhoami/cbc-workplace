<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Category;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class JobListingCategory implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null) {
            return;
        }

        $exists = Category::query()
            ->where('id', $value)
            ->where('scope', 'JobListing')
            ->exists();

        if (! $exists) {
            $fail('La categoría seleccionada no es válida para una alerta de empleo.');
        }
    }
}

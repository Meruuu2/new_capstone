<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PersonName implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match("/^\\p{L}+(?:[ '’\\-]\\p{L}+)*$/u", $value)) {
            $fail('The :attribute may contain letters, spaces, hyphens, and apostrophes only.');
        }
    }
}

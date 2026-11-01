<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class OperatingHours implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value) && preg_match('/^\d{2}:\d{2}$/', $value) && ($value < '06:00' || $value > '19:00')) {
            $fail('Reservations must start and end between 6:00 AM and 7:00 PM.');
        }
    }
}

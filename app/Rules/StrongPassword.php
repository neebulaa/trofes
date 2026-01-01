<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $password = (string) $value;

        $isLongEnough = strlen($password) >= 15;

        $isComplexEnough =
            strlen($password) >= 8 &&
            preg_match('/[a-z]/', $password) &&
            preg_match('/[0-9]/', $password);

        if (!($isLongEnough || $isComplexEnough)) {
            $fail(
                "Password must be at least 15 characters OR at least 8 characters including a number and a lowercase letter."
            );
        }
    }
}

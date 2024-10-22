<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class InternationalPhoneNumber implements Rule
{
    public function passes($attribute, $value)
    {
        // Regex pattern to match Malaysia (+60) and any international calling code
        return preg_match('/^\+(\d{1,4})/', $value) === 1;
    }

    public function message()
    {
        return 'The :attribute must start with a valid international calling code (e.g., +60 for Malaysia, +1 for USA, +44 for UK, etc.).';
    }
}

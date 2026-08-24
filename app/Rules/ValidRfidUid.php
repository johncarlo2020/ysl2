<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidRfidUid implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // UID must be alphanumeric (some readers include colons/hyphens)
        if (!preg_match('/^[A-F0-9:\-]+$/i', $value)) {
            $fail('The :attribute contains invalid characters.');
            return;
        }

        // Remove separators for length check
        $cleanUid = str_replace([':', '-'], '', $value);

        // Most RFID UIDs are 4-10 bytes (8-20 hex chars)
        // Allow range from 6 to 64 characters
        $length = strlen($cleanUid);
        if ($length < 6 || $length > 64) {
            $fail('The :attribute length is invalid (expected 6-64 characters).');
            return;
        }

        // Check for obviously fake/test patterns
        $suspicious = [
            '0000000000',
            '1111111111',
            'AAAAAAAAAA',
            'FFFFFFFFFF',
            '1234567890',
            'ABCDEF1234',
        ];

        foreach ($suspicious as $pattern) {
            if (stripos($cleanUid, $pattern) !== false) {
                $fail('The :attribute appears to be invalid or a test card.');
                return;
            }
        }
    }
}

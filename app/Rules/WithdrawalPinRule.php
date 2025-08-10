<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class WithdrawalPinRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Kiểm tra PIN phải là 4-6 chữ số
        if (!preg_match('/^\d{4,6}$/', $value)) {
            $fail('Mật khẩu rút tiền phải là 4-6 chữ số.');
        }
    }
}

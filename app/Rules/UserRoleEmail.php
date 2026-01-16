<?php

namespace App\Rules;

use App\Constants\Constants;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UserRoleEmail implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        // Query to check if the email exists and belongs to a user with the role named 'user'
        $exists = User::where('email', $value)
            ->whereHas('roles', function ($q) {
                $q->where('name', Constants::USER_ROLE);
            })
            ->exists();

        if (!$exists) {
            $fail(__('messages.email_not_found'));
        }
    }
}

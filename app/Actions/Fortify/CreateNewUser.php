<?php

namespace App\Actions\Fortify;

use App\Enums\UserStatus;
use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered customer. The mobile number is
     * required (orders are confirmed by phone); email is optional.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        $input['phone'] = PhoneNumber::normalize($input['phone'] ?? null);
        $input['email'] = filled($input['email'] ?? null) ? strtolower(trim($input['email'])) : null;

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:'.PhoneNumber::PATTERN, Rule::unique(User::class)],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'password' => $this->passwordRules(),
        ], [
            'phone.regex' => __('Enter a valid 11-digit Bangladeshi mobile number, e.g. 01712345678.'),
            'phone.unique' => __('An account with this mobile number already exists.'),
        ], [
            'phone' => 'mobile number',
        ])->validate();

        return User::create([
            'name' => $input['name'],
            'phone' => $input['phone'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'status' => UserStatus::Active,
        ]);
    }
}

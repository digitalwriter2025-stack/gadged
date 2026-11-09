<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information. An account
     * signs in with its mobile number or email, so it must keep at least one.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function update(User $user, array $input): void
    {
        $input['phone'] = filled($input['phone'] ?? null) ? PhoneNumber::normalize($input['phone']) : null;
        $input['email'] = filled($input['email'] ?? null) ? strtolower(trim($input['email'])) : null;

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'required_without:email', 'string', 'regex:'.PhoneNumber::PATTERN, Rule::unique('users')->ignore($user->id)],
            'email' => ['nullable', 'required_without:phone', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ], [
            'phone.regex' => __('Enter a valid 11-digit Bangladeshi mobile number, e.g. 01712345678.'),
            'phone.required_without' => __('Enter a mobile number or an email address.'),
            'email.required_without' => __('Enter a mobile number or an email address.'),
        ], [
            'phone' => 'mobile number',
        ])->validateWithBag('updateProfileInformation');

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'name' => $input['name'],
                'phone' => $input['phone'],
                'email' => $input['email'],
            ])->save();
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, ?string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'name' => $input['name'],
            'phone' => $input['phone'],
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}

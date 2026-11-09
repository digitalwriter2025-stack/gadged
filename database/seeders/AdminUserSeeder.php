<?php

namespace Database\Seeders;

use App\Enums\StaffRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Creates the first Super Admin. Never overwrites an existing account's password.
 * Set ADMIN_EMAIL / ADMIN_USERNAME / ADMIN_PASSWORD in .env; otherwise a random password is printed once.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) env('ADMIN_EMAIL', 'admin@example.com');
        $username = (string) env('ADMIN_USERNAME', 'superadmin');

        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            $password = (string) (env('ADMIN_PASSWORD') ?: Str::password(16, symbols: false));

            $user = User::query()->forceCreate([
                'name' => 'Super Admin',
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
            ]);

            if (! env('ADMIN_PASSWORD')) {
                $this->command?->warn("Super Admin created: {$username} ({$email}) / {$password} — change it after first login.");
            }
        } elseif ($user->username === null && ! User::query()->where('username', $username)->exists()) {
            $user->forceFill(['username' => $username])->save();
        }

        $user->syncRoles([StaffRole::SuperAdmin->value]);
    }
}

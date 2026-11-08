<?php

namespace Database\Factories;

use App\Enums\StaffRole;
use App\Enums\UserStatus;
use App\Models\User;
use App\Services\AccessControlService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '01'.fake()->unique()->numerify('#########'),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'status' => UserStatus::Active,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function blocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UserStatus::Blocked,
        ]);
    }

    /**
     * A staff member with the given role (roles and permissions are created on demand).
     */
    public function staff(StaffRole $role = StaffRole::Admin): static
    {
        return $this->afterCreating(function (User $user) use ($role): void {
            if (! Role::query()->where('name', $role->value)->exists()) {
                app(AccessControlService::class)->sync();
            }

            $user->assignRole($role->value);
        });
    }

    public function superAdmin(): static
    {
        return $this->staff(StaffRole::SuperAdmin);
    }
}

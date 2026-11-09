<?php

declare(strict_types=1);

namespace App\Filament\Resources\Staff\Schemas;

use App\Enums\StaffRole;
use App\Enums\UserStatus;
use App\Models\User;
use App\Services\StaffAccessService;
use App\Support\Filament\SectionAccessFields;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class StaffForm
{
    public static function configure(Schema $schema): Schema
    {
        $accessService = app(StaffAccessService::class);
        /** @var User $actor */
        $actor = auth()->user();

        $assignableRoles = $accessService->assignableRoles($actor);
        $cannotChangeAccess = fn (?User $record): bool => $record !== null && ! $accessService->canChangeAccessOf($actor, $record);
        $hasUnassignableRole = fn (?User $record): bool => $record !== null
            && $record->roles->pluck('name')->diff(array_keys($assignableRoles))->isNotEmpty();

        return $schema
            ->components([
                Section::make('Login Details')
                    ->description('The staff member signs in at /admin with this username (or email) and password.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('username')
                            ->required()
                            ->maxLength(50)
                            ->alphaDash()
                            ->dehydrateStateUsing(fn (string $state): string => Str::lower($state))
                            ->unique(User::class, 'username', ignoreRecord: true),

                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(User::class, 'email', ignoreRecord: true),

                        Select::make('status')
                            ->options(UserStatus::class)
                            ->default(UserStatus::Active)
                            ->required()
                            ->native(false)
                            ->disabled(fn (?User $record): bool => $record !== null && $actor->is($record)),

                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->rule(Password::default())
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->helperText(fn (string $operation): ?string => $operation === 'edit' ? 'Leave blank to keep the current password.' : null),
                    ])
                    ->columns(2),

                Section::make('Dashboard Access')
                    ->description('Choose a role, tick individual sections, or both. Ticked sections are added on top of the role.')
                    ->schema([
                        Select::make('role')
                            ->options(fn (?User $record): array => [
                                ...$assignableRoles,
                                ...$record?->roles->mapWithKeys(fn (Role $role): array => [$role->name => StaffRole::labelFor($role->name)])->all() ?? [],
                            ])
                            ->placeholder('No role — only the sections ticked below')
                            ->native(false)
                            ->disabled(fn (?User $record): bool => $cannotChangeAccess($record) || $hasUnassignableRole($record))
                            ->helperText(fn (?User $record): ?string => match (true) {
                                $cannotChangeAccess($record) => 'You cannot change your own access.',
                                $hasUnassignableRole($record) => 'This role has access you do not hold, so you cannot change it.',
                                default => null,
                            }),

                        SectionAccessFields::make($accessService->grantableSections($actor), $cannotChangeAccess),
                    ]),
            ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\Staff\Tables;

use App\Enums\Permission;
use App\Enums\StaffRole;
use App\Models\User;
use App\Support\Filament\SectionAccessFields;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Password;

class StaffTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(['name', 'username'])
                    ->weight('bold')
                    ->description(fn (User $record): ?string => $record->username !== null ? '@'.$record->username : null),

                TextColumn::make('email')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => StaffRole::labelFor($state))
                    ->color(fn (string $state): string => $state === StaffRole::SuperAdmin->value ? 'danger' : 'primary')
                    ->icon(fn (string $state): ?string => $state === StaffRole::SuperAdmin->value ? 'heroicon-m-lock-closed' : null)
                    ->placeholder('—'),

                TextColumn::make('sections')
                    ->label('Extra Sections')
                    ->state(fn (User $record): array => array_map(
                        Permission::sectionLabel(...),
                        array_keys(SectionAccessFields::group($record->permissions->pluck('name'))),
                    ))
                    ->badge()
                    ->color('gray')
                    ->limitList(3)
                    ->expandableLimitedList()
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->badge(),

                TextColumn::make('last_login_at')
                    ->label('Last Login')
                    ->since()
                    ->sortable()
                    ->placeholder('Never'),
            ])
            ->defaultSort('id')
            ->recordActions([
                self::changePasswordAction(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    /**
     * The only change allowed on a Super Admin account; also used to reset any staff password.
     */
    private static function changePasswordAction(): Action
    {
        return Action::make('changePassword')
            ->label('Change Password')
            ->icon('heroicon-o-key')
            ->color('gray')
            ->authorize('changePassword')
            ->modalWidth('md')
            ->schema([
                TextInput::make('password')
                    ->label('New Password')
                    ->password()
                    ->revealable()
                    ->required()
                    ->rule(Password::default())
                    ->same('password_confirmation'),

                TextInput::make('password_confirmation')
                    ->label('Confirm New Password')
                    ->password()
                    ->revealable()
                    ->required()
                    ->dehydrated(false),
            ])
            ->action(function (User $record, array $data): void {
                $record->update(['password' => $data['password']]);

                Notification::make()->title('Password changed')->success()->send();
            });
    }
}

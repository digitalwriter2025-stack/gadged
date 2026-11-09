<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles\Tables;

use App\Enums\StaffRole;
use App\Policies\RolePolicy;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Role')
                    ->formatStateUsing(fn (string $state): string => StaffRole::labelFor($state))
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('type')
                    ->state(fn (Role $record): string => RolePolicy::isBuiltIn($record) ? 'Built-in' : 'Custom')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Built-in' ? 'gray' : 'success'),

                TextColumn::make('permissions_count')
                    ->label('Permissions')
                    ->sortable(),

                TextColumn::make('users_count')
                    ->label('Staff')
                    ->sortable(),
            ])
            ->defaultSort('id')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->modalDescription('Staff with this role lose its access. Accounts left with no access can no longer open the dashboard.'),
            ]);
    }
}

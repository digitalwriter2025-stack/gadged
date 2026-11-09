<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles\Schemas;

use App\Models\User;
use App\Services\StaffAccessService;
use App\Support\Filament\SectionAccessFields;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        /** @var User $actor */
        $actor = auth()->user();

        return $schema
            ->components([
                Section::make('Role')
                    ->schema([
                        TextInput::make('name')
                            ->label('Role Name')
                            ->placeholder('e.g. Sales Executive')
                            ->required()
                            ->maxLength(100)
                            ->unique(Role::class, 'name', ignoreRecord: true),
                    ]),

                Section::make('Sections This Role Can Access')
                    ->schema([
                        SectionAccessFields::make(app(StaffAccessService::class)->grantableSections($actor)),
                    ]),
            ]);
    }
}

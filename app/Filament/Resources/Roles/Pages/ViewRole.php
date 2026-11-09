<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles\Pages;

use App\Enums\StaffRole;
use App\Filament\Resources\Roles\RoleResource;
use App\Support\Filament\SectionAccessFields;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Spatie\Permission\Models\Role;

/**
 * @property Role $record
 */
class ViewRole extends ViewRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['name'] = StaffRole::labelFor($data['name']);
        $data[SectionAccessFields::STATE_PATH] = SectionAccessFields::group($this->record->permissions->pluck('name'));

        return $data;
    }
}

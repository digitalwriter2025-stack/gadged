<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\Concerns\SyncsRolePermissions;
use App\Filament\Resources\Roles\RoleResource;
use App\Support\Filament\SectionAccessFields;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

/**
 * @property Role $record
 */
class EditRole extends EditRecord
{
    use SyncsRolePermissions;

    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data[SectionAccessFields::STATE_PATH] = SectionAccessFields::group($this->record->permissions->pluck('name'));

        return $data;
    }

    protected function beforeSave(): void
    {
        $this->ensureSectionsAreTicked();
    }

    /**
     * @param  Role  $record
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update(['name' => $data['name']]);

        $this->syncRolePermissions($record, $data);

        return $record;
    }
}

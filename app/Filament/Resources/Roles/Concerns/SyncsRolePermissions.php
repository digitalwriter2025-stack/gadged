<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles\Concerns;

use App\Models\User;
use App\Services\StaffAccessService;
use App\Support\Filament\SectionAccessFields;
use Filament\Notifications\Notification;
use Spatie\Permission\Models\Role;

trait SyncsRolePermissions
{
    protected function ensureSectionsAreTicked(): void
    {
        if (SectionAccessFields::flatten($this->data) !== []) {
            return;
        }

        Notification::make()
            ->title('Tick at least one section for this role')
            ->danger()
            ->send();

        $this->halt();
    }

    /**
     * Only permissions the current user holds can be put on a role.
     *
     * @param  array<string, mixed>  $data
     */
    protected function syncRolePermissions(Role $role, array $data): void
    {
        /** @var User $actor */
        $actor = auth()->user();

        $role->syncPermissions(array_values(array_intersect(
            SectionAccessFields::flatten($data),
            app(StaffAccessService::class)->grantablePermissions($actor),
        )));
    }
}

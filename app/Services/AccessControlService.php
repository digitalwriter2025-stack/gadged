<?php

namespace App\Services;

use App\Enums\Permission as PermissionName;
use App\Enums\StaffRole;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates every permission from the Permission enum and grants each staff role
 * its default set. Idempotent: safe to run on every deploy.
 */
class AccessControlService
{
    public function __construct(private readonly PermissionRegistrar $registrar) {}

    public function sync(string $guard = 'web'): void
    {
        $this->registrar->forgetCachedPermissions();

        foreach (PermissionName::values() as $name) {
            Permission::findOrCreate($name, $guard);
        }

        foreach (StaffRole::cases() as $staffRole) {
            Role::findOrCreate($staffRole->value, $guard)->syncPermissions(
                array_map(fn (PermissionName $permission): string => $permission->value, $staffRole->permissions()),
            );
        }

        $this->registrar->forgetCachedPermissions();
    }
}

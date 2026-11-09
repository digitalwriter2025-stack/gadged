<?php

namespace App\Services;

use App\Enums\Permission;
use App\Enums\StaffRole;
use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

/**
 * Decides which roles and section permissions a staff manager may hand out, and applies them.
 * A manager can only grant what they hold themselves, never the Super Admin role, and never
 * change their own access; access they cannot grant is left untouched on the target.
 */
class StaffAccessService
{
    /**
     * @return list<string>
     */
    public function grantablePermissions(User $actor): array
    {
        if ($actor->isSuperAdmin()) {
            return Permission::values();
        }

        return array_values(array_intersect(Permission::values(), $actor->getAllPermissions()->pluck('name')->all()));
    }

    /**
     * Sections and abilities the actor may grant, for building the access checkboxes.
     *
     * @return array<string, array<string, string>> section => [permission value => ability label]
     */
    public function grantableSections(User $actor): array
    {
        $grantable = array_flip($this->grantablePermissions($actor));

        return array_filter(array_map(
            fn (array $abilities): array => array_intersect_key($abilities, $grantable),
            Permission::sections(),
        ));
    }

    /**
     * Roles the actor may assign: every role except Super Admin whose permissions the actor fully holds.
     *
     * @return array<string, string> role name => label
     */
    public function assignableRoles(User $actor): array
    {
        $grantable = $this->grantablePermissions($actor);

        return $this->roles()
            ->reject(fn (Role $role): bool => $role->name === StaffRole::SuperAdmin->value)
            ->filter(fn (Role $role): bool => $role->permissions->pluck('name')->diff($grantable)->isEmpty())
            ->mapWithKeys(fn (Role $role): array => [$role->name => StaffRole::labelFor($role->name)])
            ->all();
    }

    public function canChangeAccessOf(User $actor, User $staff): bool
    {
        return ! $staff->isSuperAdmin() && ($actor->isSuperAdmin() || ! $actor->is($staff));
    }

    /**
     * @param  list<string>  $permissions
     */
    public function sync(User $actor, User $staff, ?string $role, array $permissions): void
    {
        if (! $this->canChangeAccessOf($actor, $staff)) {
            return;
        }

        $assignableRoles = array_keys($this->assignableRoles($actor));
        $currentRoles = $staff->roles->pluck('name');
        $keptRoles = $currentRoles->diff($assignableRoles);
        $requestedRoles = in_array($role, $assignableRoles, true) ? [$role] : [];

        // A role the actor can't grant stays; otherwise the selected role replaces it.
        $staff->syncRoles($keptRoles->isNotEmpty() ? $keptRoles->all() : $requestedRoles);

        $grantable = $this->grantablePermissions($actor);
        $keptPermissions = $staff->permissions->pluck('name')->diff($grantable);

        $staff->syncPermissions([
            ...$keptPermissions->all(),
            ...array_values(array_intersect($permissions, $grantable)),
        ]);
    }

    /**
     * @return Collection<int, Role>
     */
    private function roles(): Collection
    {
        return Role::query()->with('permissions')->orderBy('id')->get();
    }
}

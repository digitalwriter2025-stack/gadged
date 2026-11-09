<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Enums\StaffRole;
use App\Models\User;
use Spatie\Permission\Models\Role;

/**
 * Roles are managed with roles.manage. Built-in roles (StaffRole) are defined in code
 * and re-synced on every deploy, so they can be viewed but never edited or deleted.
 */
class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canManage($user);
    }

    public function view(User $user, Role $role): bool
    {
        return $this->canManage($user);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user, Role $role): bool
    {
        return ! self::isBuiltIn($role) && $this->canManage($user);
    }

    public function delete(User $user, Role $role): bool
    {
        return ! self::isBuiltIn($role) && $this->canManage($user);
    }

    public static function isBuiltIn(Role $role): bool
    {
        return StaffRole::tryFrom($role->name) !== null;
    }

    private function canManage(User $user): bool
    {
        return $user->isActive() && $user->checkPermissionTo(Permission::RolesManage->value);
    }
}

<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;

/**
 * Customers are governed by customers.* permissions, staff accounts by staff.manage.
 * Super Admin accounts are locked for everyone: never updated or deleted, only their password changes.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->can($user, Permission::CustomersView) || $this->can($user, Permission::StaffManage);
    }

    public function view(User $user, User $target): bool
    {
        return $user->is($target) || $this->can($user, $this->permissionFor($target, Permission::CustomersView));
    }

    public function create(User $user): bool
    {
        return $this->can($user, Permission::CustomersUpdate) || $this->can($user, Permission::StaffManage);
    }

    public function update(User $user, User $target): bool
    {
        if ($target->isSuperAdmin()) {
            return false;
        }

        return $this->can($user, $this->permissionFor($target, Permission::CustomersUpdate));
    }

    public function delete(User $user, User $target): bool
    {
        if ($user->is($target) || $target->isSuperAdmin()) {
            return false;
        }

        return $this->can($user, $this->permissionFor($target, Permission::CustomersDelete));
    }

    /**
     * A Super Admin account is locked; its password is the only thing that can change, and only by a Super Admin.
     */
    public function changePassword(User $user, User $target): bool
    {
        if ($target->isSuperAdmin()) {
            return $user->isActive() && $user->isSuperAdmin();
        }

        return $this->update($user, $target);
    }

    public function restore(User $user, User $target): bool
    {
        return $this->delete($user, $target);
    }

    public function forceDelete(User $user, User $target): bool
    {
        return false;
    }

    public function manageRoles(User $user): bool
    {
        return $this->can($user, Permission::RolesManage);
    }

    private function permissionFor(User $target, Permission $customerPermission): Permission
    {
        return $target->isStaff() ? Permission::StaffManage : $customerPermission;
    }

    private function can(User $user, Permission $permission): bool
    {
        return $user->isActive() && $user->checkPermissionTo($permission->value);
    }
}

<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Maps the standard resource abilities onto module permissions.
 * Subclasses declare which permission guards each ability; a null permission
 * means the ability is never allowed (except for Super Admin via Gate::before).
 */
abstract class ModulePolicy
{
    protected ?Permission $view = null;

    protected ?Permission $create = null;

    protected ?Permission $update = null;

    protected ?Permission $delete = null;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, $this->view);
    }

    public function view(User $user, Model $model): bool
    {
        return $this->allows($user, $this->view);
    }

    public function create(User $user): bool
    {
        return $this->allows($user, $this->create);
    }

    public function update(User $user, Model $model): bool
    {
        return $this->allows($user, $this->update);
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->allows($user, $this->delete);
    }

    public function deleteAny(User $user): bool
    {
        return $this->allows($user, $this->delete);
    }

    public function restore(User $user, Model $model): bool
    {
        return $this->allows($user, $this->delete);
    }

    public function restoreAny(User $user): bool
    {
        return $this->allows($user, $this->delete);
    }

    /**
     * Permanent deletion is reserved for Super Admin (granted through Gate::before).
     */
    public function forceDelete(User $user, Model $model): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }

    public function reorder(User $user): bool
    {
        return $this->allows($user, $this->update);
    }

    protected function allows(User $user, ?Permission $permission): bool
    {
        return $permission !== null
            && $user->isActive()
            && $user->checkPermissionTo($permission->value);
    }
}

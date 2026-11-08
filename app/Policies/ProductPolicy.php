<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;

class ProductPolicy extends ModulePolicy
{
    protected ?Permission $view = Permission::ProductsView;

    protected ?Permission $create = Permission::ProductsCreate;

    protected ?Permission $update = Permission::ProductsUpdate;

    protected ?Permission $delete = Permission::ProductsDelete;

    /**
     * Purchase / cost prices are hidden from staff without this permission.
     */
    public function viewCostPrice(User $user): bool
    {
        return $this->allows($user, Permission::ProductsViewCostPrice);
    }
}

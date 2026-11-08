<?php

namespace App\Policies;

use App\Enums\Permission;

class CategoryPolicy extends ModulePolicy
{
    protected ?Permission $view = Permission::CategoriesView;

    protected ?Permission $create = Permission::CategoriesCreate;

    protected ?Permission $update = Permission::CategoriesUpdate;

    protected ?Permission $delete = Permission::CategoriesDelete;
}

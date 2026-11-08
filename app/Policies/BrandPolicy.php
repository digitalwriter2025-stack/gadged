<?php

namespace App\Policies;

use App\Enums\Permission;

class BrandPolicy extends ModulePolicy
{
    protected ?Permission $view = Permission::BrandsView;

    protected ?Permission $create = Permission::BrandsCreate;

    protected ?Permission $update = Permission::BrandsUpdate;

    protected ?Permission $delete = Permission::BrandsDelete;
}

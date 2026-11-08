<?php

namespace App\Policies;

use App\Enums\Permission;

class DistrictPolicy extends ModulePolicy
{
    protected ?Permission $view = Permission::ShippingManage;

    protected ?Permission $create = Permission::ShippingManage;

    protected ?Permission $update = Permission::ShippingManage;
}

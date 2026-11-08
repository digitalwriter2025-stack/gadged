<?php

namespace App\Policies;

use App\Enums\Permission;

class ShippingZonePolicy extends ModulePolicy
{
    protected ?Permission $view = Permission::ShippingManage;

    protected ?Permission $create = Permission::ShippingManage;

    protected ?Permission $update = Permission::ShippingManage;

    protected ?Permission $delete = Permission::ShippingManage;
}

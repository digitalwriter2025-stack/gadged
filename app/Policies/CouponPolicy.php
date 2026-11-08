<?php

namespace App\Policies;

use App\Enums\Permission;

class CouponPolicy extends ModulePolicy
{
    protected ?Permission $view = Permission::CouponsView;

    protected ?Permission $create = Permission::CouponsManage;

    protected ?Permission $update = Permission::CouponsManage;

    protected ?Permission $delete = Permission::CouponsManage;
}

<?php

namespace App\Policies;

use App\Enums\Permission;

class RefundPolicy extends ModulePolicy
{
    protected ?Permission $view = Permission::OrdersView;

    protected ?Permission $create = Permission::OrdersRefund;
}

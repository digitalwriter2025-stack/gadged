<?php

namespace App\Policies;

use App\Enums\Permission;

class PaymentPolicy extends ModulePolicy
{
    protected ?Permission $view = Permission::PaymentsView;

    protected ?Permission $create = Permission::PaymentsManage;

    protected ?Permission $update = Permission::PaymentsManage;
}

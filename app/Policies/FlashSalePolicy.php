<?php

namespace App\Policies;

use App\Enums\Permission;

class FlashSalePolicy extends ModulePolicy
{
    protected ?Permission $view = Permission::FlashSalesView;

    protected ?Permission $create = Permission::FlashSalesManage;

    protected ?Permission $update = Permission::FlashSalesManage;

    protected ?Permission $delete = Permission::FlashSalesManage;
}

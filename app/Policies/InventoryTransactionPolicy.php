<?php

namespace App\Policies;

use App\Enums\Permission;

class InventoryTransactionPolicy extends ModulePolicy
{
    protected ?Permission $view = Permission::InventoryView;

    protected ?Permission $create = Permission::InventoryManage;
}

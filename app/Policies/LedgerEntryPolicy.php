<?php

namespace App\Policies;

use App\Enums\Permission;

class LedgerEntryPolicy extends ModulePolicy
{
    protected ?Permission $view = Permission::FinanceView;

    protected ?Permission $create = Permission::FinanceManage;

    protected ?Permission $update = Permission::FinanceManage;

    protected ?Permission $delete = Permission::FinanceManage;
}

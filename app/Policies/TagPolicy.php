<?php

namespace App\Policies;

use App\Enums\Permission;

class TagPolicy extends ModulePolicy
{
    protected ?Permission $view = Permission::AttributesManage;

    protected ?Permission $create = Permission::AttributesManage;

    protected ?Permission $update = Permission::AttributesManage;

    protected ?Permission $delete = Permission::AttributesManage;
}

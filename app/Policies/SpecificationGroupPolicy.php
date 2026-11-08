<?php

namespace App\Policies;

use App\Enums\Permission;

class SpecificationGroupPolicy extends ModulePolicy
{
    protected ?Permission $view = Permission::SpecificationsManage;

    protected ?Permission $create = Permission::SpecificationsManage;

    protected ?Permission $update = Permission::SpecificationsManage;

    protected ?Permission $delete = Permission::SpecificationsManage;
}

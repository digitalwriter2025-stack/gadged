<?php

namespace App\Policies;

use App\Enums\Permission;

class ServiceHighlightPolicy extends ModulePolicy
{
    protected ?Permission $view = Permission::ContentManage;

    protected ?Permission $create = Permission::ContentManage;

    protected ?Permission $update = Permission::ContentManage;

    protected ?Permission $delete = Permission::ContentManage;
}

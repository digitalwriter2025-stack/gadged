<?php

namespace App\Policies;

use App\Enums\Permission;

class SettingPolicy extends ModulePolicy
{
    protected ?Permission $view = Permission::SettingsManage;

    protected ?Permission $create = Permission::SettingsManage;

    protected ?Permission $update = Permission::SettingsManage;
}

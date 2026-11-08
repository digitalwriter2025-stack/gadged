<?php

namespace App\Policies;

use App\Enums\Permission;

class ReviewPolicy extends ModulePolicy
{
    protected ?Permission $view = Permission::ReviewsView;

    protected ?Permission $update = Permission::ReviewsModerate;

    protected ?Permission $delete = Permission::ReviewsModerate;
}

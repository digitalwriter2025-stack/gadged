<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Order;
use App\Models\User;

class OrderPolicy extends ModulePolicy
{
    protected ?Permission $view = Permission::OrdersView;

    protected ?Permission $create = Permission::OrdersCreate;

    protected ?Permission $update = Permission::OrdersUpdate;

    public function cancel(User $user, Order $order): bool
    {
        return $this->allows($user, Permission::OrdersCancel);
    }

    public function refund(User $user, Order $order): bool
    {
        return $this->allows($user, Permission::OrdersRefund);
    }

    public function managePayment(User $user, Order $order): bool
    {
        return $this->allows($user, Permission::PaymentsManage);
    }
}

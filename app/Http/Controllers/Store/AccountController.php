<?php

namespace App\Http\Controllers\Store;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Support\Localization;
use App\Support\Money;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AccountController extends Controller
{
    /**
     * Number of recent orders listed on the account page.
     */
    private const RECENT_ORDERS = 20;

    /**
     * Show the signed-in customer's profile and recent orders.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $orders = $user->orders()
            ->withCount('items')
            ->latest()
            ->limit(self::RECENT_ORDERS)
            ->get()
            ->map(fn (Order $order): array => [
                'number' => $order->order_number,
                'placed_at' => Localization::date($order->created_at->timezone(config('app.display_timezone')), 'd M Y'),
                'status' => $order->status->getLabel(),
                'tone' => $order->status->getColor(),
                'item_count' => $order->items_count,
                'total' => Money::format($order->grand_total),
            ]);

        return view('store.account', [
            'user' => $user,
            'initials' => $this->initials($user),
            'memberSince' => $user->created_at === null ? null : Localization::date($user->created_at, 'M Y'),
            'stats' => $this->orderStats($user),
            'orders' => $orders,
            'breadcrumbs' => [
                ['label' => __('Home'), 'url' => route('store.home')],
                ['label' => __('My Account'), 'url' => null],
            ],
        ]);
    }

    /**
     * Order counts across the customer's whole history, not just the listed ones.
     *
     * @return array{total: int, active: int, delivered: int}
     */
    private function orderStats(User $user): array
    {
        $countsByStatus = $user->orders()
            ->toBase()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->map(fn (mixed $count): int => (int) $count);

        $activeStatuses = [
            OrderStatus::Pending->value,
            OrderStatus::Confirmed->value,
            OrderStatus::Processing->value,
            OrderStatus::Packed->value,
            OrderStatus::Shipped->value,
        ];

        return [
            'total' => $countsByStatus->sum(),
            'active' => $countsByStatus->only($activeStatuses)->sum(),
            'delivered' => $countsByStatus->get(OrderStatus::Delivered->value, 0),
        ];
    }

    /**
     * Up to two initials for the avatar, e.g. "Super Admin" → "SA".
     */
    private function initials(User $user): string
    {
        return Str::of($user->name)
            ->squish()
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $word): string => mb_strtoupper(mb_substr($word, 0, 1)))
            ->implode('');
    }
}

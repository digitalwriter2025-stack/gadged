<?php

namespace App\Providers;

use App\Models;
use App\Models\User;
use App\Policies\RolePolicy;
use App\Services\CartService;
use App\Services\GoogleTag;
use App\Services\MetaPixel;
use App\Support\Storefront\NavigationMenu;
use App\Support\Storefront\StoreLayout;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * @var array<string, class-string<Model>>
     */
    public const MORPH_MAP = [
        'user' => User::class,
        'address' => Models\Address::class,
        'district' => Models\District::class,
        'shipping_zone' => Models\ShippingZone::class,
        'shipping_method' => Models\ShippingMethod::class,
        'category' => Models\Category::class,
        'brand' => Models\Brand::class,
        'tag' => Models\Tag::class,
        'attribute' => Models\Attribute::class,
        'attribute_value' => Models\AttributeValue::class,
        'specification_group' => Models\SpecificationGroup::class,
        'specification' => Models\Specification::class,
        'specification_option' => Models\SpecificationOption::class,
        'product' => Models\Product::class,
        'product_variant' => Models\ProductVariant::class,
        'product_specification_value' => Models\ProductSpecificationValue::class,
        'inventory_transaction' => Models\InventoryTransaction::class,
        'coupon' => Models\Coupon::class,
        'coupon_usage' => Models\CouponUsage::class,
        'flash_sale' => Models\FlashSale::class,
        'flash_sale_product' => Models\FlashSaleProduct::class,
        'cart' => Models\Cart::class,
        'cart_item' => Models\CartItem::class,
        'wishlist' => Models\Wishlist::class,
        'order' => Models\Order::class,
        'order_item' => Models\OrderItem::class,
        'order_status_history' => Models\OrderStatusHistory::class,
        'order_note' => Models\OrderNote::class,
        'payment' => Models\Payment::class,
        'refund' => Models\Refund::class,
        'review' => Models\Review::class,
        'banner' => Models\Banner::class,
        'offer' => Models\Offer::class,
        'home_section' => Models\HomeSection::class,
        'home_section_item' => Models\HomeSectionItem::class,
        'service_highlight' => Models\ServiceHighlight::class,
        'menu' => Models\Menu::class,
        'menu_item' => Models\MenuItem::class,
        'page' => Models\Page::class,
        'setting' => Models\Setting::class,
        'role' => Role::class,
        'permission' => Permission::class,
        'media' => Models\Media::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $isProduction = $this->app->isProduction();

        // Surface N+1 queries, silently dropped attributes and missing columns during development.
        Model::shouldBeStrict(! $isProduction);

        // Block migrate:fresh, db:wipe and similar commands against production data.
        DB::prohibitDestructiveCommands($isProduction);

        Date::use(CarbonImmutable::class);

        // Locally the app is opened on whichever host `artisan serve` prints (127.0.0.1 vs localhost, :8000 vs :8001).
        // File URLs must share that origin, or the admin upload previews can't fetch them and hang on "Loading".
        if ($this->app->isLocal() && ! $this->app->runningInConsole()) {
            config(['filesystems.disks.public.url' => request()->getSchemeAndHttpHost().'/storage']);
        }

        // Store stable aliases instead of class names in *_type columns.
        Relation::enforceMorphMap(self::MORPH_MAP);

        Gate::policy(Role::class, RolePolicy::class);

        // Super Admin passes every authorization check, except on locked records (the Super Admin
        // account itself and the built-in roles), which always defer to their policy.
        Gate::before(function (User $user, string $ability, array $arguments): ?bool {
            if (! $user->isActive() || ! $user->isSuperAdmin()) {
                return null;
            }

            $record = $arguments[0] ?? null;
            $isLocked = ($record instanceof User && $record->isSuperAdmin())
                || ($record instanceof Role && RolePolicy::isBuiltIn($record));

            return $isLocked ? null : true;
        });

        Password::defaults(fn (): Password => $isProduction
            ? Password::min(8)->letters()->numbers()->uncompromised()
            : Password::min(8));

        View::composer('components.layouts.store', function (ViewContract $view): void {
            $view->with([
                'navigationLinks' => $this->app->make(NavigationMenu::class)->links(),
                'cartCount' => $this->app->make(CartService::class)->count(),
                ...$this->app->make(StoreLayout::class)->data(),
                'metaPixel' => $this->app->make(MetaPixel::class)->page(),
                'googleTag' => $this->app->make(GoogleTag::class)->page(),
            ]);
        });
    }
}

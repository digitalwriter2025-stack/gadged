<?php

use App\Http\Controllers\Store\AccountController;
use App\Http\Controllers\Store\BrandController;
use App\Http\Controllers\Store\CacheManagerController;
use App\Http\Controllers\Store\CartController;
use App\Http\Controllers\Store\CategoryController;
use App\Http\Controllers\Store\CheckoutController;
use App\Http\Controllers\Store\ComboController;
use App\Http\Controllers\Store\HomeController;
use App\Http\Controllers\Store\LocaleController;
use App\Http\Controllers\Store\OfferController;
use App\Http\Controllers\Store\OrderTrackingController;
use App\Http\Controllers\Store\PageController;
use App\Http\Controllers\Store\PcBuilderController;
use App\Http\Controllers\Store\ProductController;
use App\Http\Controllers\Store\ReviewController;
use App\Http\Controllers\Store\SearchController;
use App\Http\Controllers\Store\SearchSuggestionController;
use App\Http\Controllers\Store\StorageAssetController;
use App\Http\Controllers\Store\TrendingController;
use App\Http\Middleware\SetStoreLocale;
use Illuminate\Support\Facades\Route;

// Every storefront page renders in the visitor's language (English / বাংলা).
Route::middleware(SetStoreLocale::class)->group(function (): void {
    Route::post('/language', LocaleController::class)->name('store.locale.update');

    Route::get('/', HomeController::class)->name('store.home');
    Route::get('/category/{category:slug}', CategoryController::class)->name('store.categories.show');
    Route::get('/product/{product:slug}', ProductController::class)->name('store.products.show');
    Route::post('/product/{product:slug}/reviews', ReviewController::class)->middleware(['auth', 'throttle:5,1'])->name('store.reviews.store');
    Route::get('/search', SearchController::class)->name('store.search');
    Route::controller(PcBuilderController::class)->prefix('tool/pc-builder')->name('store.pc-builder.')->group(function (): void {
        Route::get('/', 'show')->name('show');
        Route::delete('/', 'clear')->name('clear');
        Route::get('/choose/{component}', 'choose')->name('choose');
        Route::post('/{component}', 'store')->name('store');
        Route::delete('/{component}', 'destroy')->name('remove');
    });
    Route::get('/search/suggest', SearchSuggestionController::class)->middleware('throttle:60,1')->name('store.search.suggest');
    Route::get('/trending', TrendingController::class)->name('store.trending');
    Route::get('/combos', ComboController::class)->name('store.combos.index');
    Route::get('/brands', BrandController::class)->name('store.brands.index');
    Route::get('/account', AccountController::class)->middleware('auth')->name('store.account');
    Route::get('/campaigns', [OfferController::class, 'index'])->name('store.offers.index');
    Route::get('/campaigns/{offer:slug}', [OfferController::class, 'show'])->name('store.offers.show');
    Route::permanentRedirect('/offers', '/campaigns');
    Route::permanentRedirect('/offers/{slug}', '/campaigns/{slug}');
    Route::permanentRedirect('/mobile-deals', '/campaigns');
    Route::get('/track-order', [OrderTrackingController::class, 'show'])->name('store.orders.track');
    Route::post('/track-order', [OrderTrackingController::class, 'lookup'])->middleware('throttle:10,1')->name('store.orders.lookup');
    Route::get('/page/{page:slug}', PageController::class)->name('store.pages.show');

    Route::controller(CartController::class)->prefix('cart')->name('store.cart.')->group(function (): void {
        Route::post('/', 'store')->name('store');
        Route::patch('/{variant}', 'update')->whereNumber('variant')->name('update');
        Route::delete('/{variant}', 'destroy')->whereNumber('variant')->name('destroy');
    });
    Route::controller(CheckoutController::class)->prefix('checkout')->name('store.checkout.')->group(function (): void {
        Route::get('/', 'show')->name('show');
        Route::post('/', 'store')->middleware('throttle:10,1')->name('store');
        Route::get('/thank-you/{order}', 'thankYou')->name('thank-you');
    });
});

Route::match(['get', 'post'], '/clear-cache', CacheManagerController::class)->name('clear-cache');
Route::match(['get', 'post'], '/clean-cache', CacheManagerController::class)->name('clean-cache');

Route::get('storage/{path}', [StorageAssetController::class, 'show'])
    ->where('path', '.*')
    ->name('storage.asset');

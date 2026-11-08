<?php

use App\Http\Controllers\Store\CacheManagerController;
use App\Http\Controllers\Store\CartController;
use App\Http\Controllers\Store\CategoryController;
use App\Http\Controllers\Store\CheckoutController;
use App\Http\Controllers\Store\HomeController;
use App\Http\Controllers\Store\OfferController;
use App\Http\Controllers\Store\PageController;
use App\Http\Controllers\Store\PcBuilderController;
use App\Http\Controllers\Store\ProductController;
use App\Http\Controllers\Store\SearchController;
use App\Http\Controllers\Store\SearchSuggestionController;
use App\Http\Controllers\Store\StorageAssetController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('store.home');
Route::get('/category/{category:slug}', CategoryController::class)->name('store.categories.show');
Route::get('/product/{product:slug}', ProductController::class)->name('store.products.show');
Route::get('/search', SearchController::class)->name('store.search');
Route::controller(PcBuilderController::class)->prefix('tool/pc-builder')->name('store.pc-builder.')->group(function (): void {
    Route::get('/', 'show')->name('show');
    Route::delete('/', 'clear')->name('clear');
    Route::get('/choose/{component}', 'choose')->name('choose');
    Route::post('/{component}', 'store')->name('store');
    Route::delete('/{component}', 'destroy')->name('remove');
});
Route::get('/search/suggest', SearchSuggestionController::class)->middleware('throttle:60,1')->name('store.search.suggest');
Route::get('/offers', [OfferController::class, 'index'])->name('store.offers.index');
Route::get('/offers/{offer:slug}', [OfferController::class, 'show'])->name('store.offers.show');
Route::get('/mobile-deals', [OfferController::class, 'mobileDeals'])->name('store.offers.mobile');
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

Route::match(['get', 'post'], '/clear-cache', CacheManagerController::class)->name('clear-cache');
Route::match(['get', 'post'], '/clean-cache', CacheManagerController::class)->name('clean-cache');

Route::get('storage/{path}', [StorageAssetController::class, 'show'])
    ->where('path', '.*')
    ->name('storage.asset');

<?php

use Illuminate\Support\Facades\Route;
use Modules\WebsiteV2\Http\Controllers\AccountController;
use Modules\WebsiteV2\Http\Controllers\Admin\AffiliateController;
use Modules\WebsiteV2\Http\Controllers\Admin\BannerController;
use Modules\WebsiteV2\Http\Controllers\Admin\CouponController;
use Modules\WebsiteV2\Http\Controllers\Admin\CustomerController;
use Modules\WebsiteV2\Http\Controllers\Admin\FlashSaleController;
use Modules\WebsiteV2\Http\Controllers\Admin\FooterController;
use Modules\WebsiteV2\Http\Controllers\Admin\HeaderController;
use Modules\WebsiteV2\Http\Controllers\Admin\HomeSettingsController;
use Modules\WebsiteV2\Http\Controllers\AuthController;
use Modules\WebsiteV2\Http\Controllers\CartController;
use Modules\WebsiteV2\Http\Controllers\CheckoutController;
use Modules\WebsiteV2\Http\Controllers\PostController;
use Modules\WebsiteV2\Http\Controllers\ProductController;
use Modules\WebsiteV2\Http\Controllers\WebsiteController;

$prefix = config('website-v2.route_prefix', 'website-v2');

Route::middleware('web')->prefix($prefix)->name('website-v2.')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::get('/login', 'login')->name('login');
        Route::get('/register', 'register')->name('register');
        Route::post('/logout', 'logout')->middleware('auth')->name('logout');
    });

    Route::controller(WebsiteController::class)->group(function () {
        Route::get('/', 'home')->name('home');
        Route::get('/help', 'help')->name('help');
    });

    Route::prefix('product')->name('product.')->controller(ProductController::class)->group(function () {
        Route::get('/', 'index')->name('list');
        Route::get('/{slug}', 'show')->name('detail');
    });

    Route::prefix('blog')->controller(PostController::class)->group(function () {
        Route::get('/', 'index')->name('blog');
        Route::get('/{slug}', 'detail')->name('blog.detail');
    });

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');

    Route::prefix('checkout')->name('checkout.')->controller(CheckoutController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/success', 'success')->name('success');
        Route::get('/momo-callback', 'momoCallback')->name('momo.callback');
    });

    Route::middleware('auth')->prefix('account')->name('account.')->controller(AccountController::class)->group(function () {
        Route::get('/', 'index')->name('dashboard');
        Route::get('/profile', 'profile')->name('profile');
        Route::get('/affiliate', 'affiliate')->name('affiliate');
        Route::get('/orders', 'orders')->name('orders');
        Route::get('/orders/{code}', 'orderDetail')->name('orders.detail');
        Route::get('/wishlist', 'wishlist')->name('wishlist');
    });
});

Route::middleware(['web', 'auth:admin', 'permission:website-v2.view'])
    ->prefix('admin/website-v2')
    ->name('website-v2.admin.')
    ->group(function () {
        Route::get('/affiliate', [AffiliateController::class, 'index'])->name('affiliate.index');
        Route::get('/homepage-settings', [HomeSettingsController::class, 'index'])->name('home.settings');
        Route::get('/header-settings', [HeaderController::class, 'index'])->name('header.settings');
        Route::get('/footer-settings', [FooterController::class, 'index'])->name('footer.settings');
        Route::get('/banners', [BannerController::class, 'index'])->name('banners');
        Route::get('/flash-sales', [FlashSaleController::class, 'index'])->name('flash-sales');

        Route::prefix('coupons')->name('coupons.')->controller(CouponController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->middleware('permission:website-v2.create')->name('create');
            Route::get('/{id}/edit', 'edit')->middleware('permission:website-v2.edit')->name('edit');
        });

        Route::prefix('customers')->name('customers.')->controller(CustomerController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->middleware('permission:website-v2.create')->name('create');
            Route::get('/{id}', 'show')->name('show');
        });
    });

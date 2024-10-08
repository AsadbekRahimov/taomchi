<?php

declare(strict_types=1);

use App\Orchid\Screens\PlatformScreen;
use App\Orchid\Screens\Role\RoleEditScreen;
use App\Orchid\Screens\Role\RoleListScreen;
use App\Orchid\Screens\User\UserEditScreen;
use App\Orchid\Screens\User\UserListScreen;
use App\Orchid\Screens\User\UserProfileScreen;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the need "dashboard" middleware group. Now create something great!
|
*/

// Main
Route::screen('/main', PlatformScreen::class)
    ->name('platform.main');

// Platform > Profile
Route::screen('profile', UserProfileScreen::class)
    ->name('platform.profile')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push(__('Profile'), route('platform.profile'));
    });

// Platform > System > Users
Route::screen('users/{user}/edit', UserEditScreen::class)
    ->name('platform.systems.users.edit')
    ->breadcrumbs(function (Trail $trail, $user) {
        return $trail
            ->parent('platform.systems.users')
            ->push(__('User'), route('platform.systems.users.edit', $user));
    });

// Platform > System > Users > Create
Route::screen('users/create', UserEditScreen::class)
    ->name('platform.systems.users.create')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.systems.users')
            ->push(__('Create'), route('platform.systems.users.create'));
    });

// Platform > System > Users > User
Route::screen('users', UserListScreen::class)
    ->name('platform.systems.users')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push(__('Users'), route('platform.systems.users'));
    });

// Platform > System > Roles > Role
Route::screen('roles/{role}/edit', RoleEditScreen::class)
    ->name('platform.systems.roles.edit')
    ->breadcrumbs(function (Trail $trail, $role) {
        return $trail
            ->parent('platform.systems.roles')
            ->push(__('Role'), route('platform.systems.roles.edit', $role));
    });

// Platform > System > Roles > Create
Route::screen('roles/create', RoleEditScreen::class)
    ->name('platform.systems.roles.create')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.systems.roles')
            ->push(__('Create'), route('platform.systems.roles.create'));
    });

// Platform > System > Roles
Route::screen('roles', RoleListScreen::class)
    ->name('platform.systems.roles')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push(__('Roles'), route('platform.systems.roles'));
    });

// Sotilgan  partiyalar
Route::screen('sell/parties', \App\Orchid\Screens\Sell\SalesPartyScreen::class)
    ->name('platform.sell_parties')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push('Сотилган партиялар');
    });

// Sotilgan  maxsulotlar
Route::screen('sales', \App\Orchid\Screens\Sell\SalesScreen::class)
    ->name('platform.sales')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push('Сотилган махсулотлар');
    });

// Buyurtmalar
Route::screen('orders', \App\Orchid\Screens\Order\OrderListScreen::class)
    ->name('platform.orders')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push('Буюртмалар');
    });

// Telegramdan Buyurtmalar
Route::screen('telegram-orders', \App\Orchid\Screens\Order\TelegramOrderListScreen::class)
    ->name('platform.telegram-orders')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push('Телеграмдан буюртмалар');
    });

// Buyurtmalar
Route::screen('payments', \App\Orchid\Screens\Payment\PaymentListScreen::class)
    ->name('platform.payments')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push('Тўловлар');
    });

// Chiqimlar
Route::screen('expences', \App\Orchid\Screens\Expences\ExpenceListScreen::class)
    ->name('platform.expences')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push('Чиқимлар');
    });

// Qarzdorlar
Route::screen('customer_duties', \App\Orchid\Screens\Duties\CustomerDutiesListScreen::class)
    ->name('platform.customer_duties')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push('Қарздорлар');
    });

// Maxsulotlar
Route::screen('products', \App\Orchid\Screens\Product\ProductListScreen::class)
    ->name('platform.products')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push('Махсулотлар');
    });

// Mijoz haqida malumot
Route::screen('/customer/{customer}', \App\Orchid\Screens\Customer\CustomerInfoScreen::class)
    ->name('platform.customer_info')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push('Мижоз');
    });
//Example screen routes
//Route::screen('example-fields', ExampleFieldsScreen::class)->name('platform.example.fields');
//Route::screen('example-layouts', ExampleLayoutsScreen::class)->name('platform.example.layouts');
//Route::screen('example-charts', ExampleChartsScreen::class)->name('platform.example.charts');
//Route::screen('example-editors', ExampleTextEditorsScreen::class)->name('platform.example.editors');
//Route::screen('example-cards', ExampleCardsScreen::class)->name('platform.example.cards');
//Route::screen('example-advanced', ExampleFieldsAdvancedScreen::class)->name('platform.example.advanced');

//Route::screen('idea', Idea::class, 'platform.screens.idea');

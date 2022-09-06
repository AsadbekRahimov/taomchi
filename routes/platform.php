<?php

declare(strict_types=1);

use App\Orchid\Screens\Examples\ExampleCardsScreen;
use App\Orchid\Screens\Examples\ExampleChartsScreen;
use App\Orchid\Screens\Examples\ExampleFieldsAdvancedScreen;
use App\Orchid\Screens\Examples\ExampleFieldsScreen;
use App\Orchid\Screens\Examples\ExampleLayoutsScreen;
use App\Orchid\Screens\Examples\ExampleScreen;
use App\Orchid\Screens\Examples\ExampleTextEditorsScreen;
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

// Example...
Route::screen('example', ExampleScreen::class)
    ->name('platform.example')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push('Example screen');
    });

// Ombor maxsulotlari
Route::screen('stock/list', \App\Orchid\Screens\Stock\StockListScreen::class)
    ->name('platform.stock_list')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push('Zaxira maxsulotlar');
    });

// Omborga maxsulotlarni kiritish
Route::screen('stock/add_products', \App\Orchid\Screens\Stock\StockAddProductScreen::class)
    ->name('platform.add_products')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.stock_list')
            ->push('Махсулот qo\'shish');
    });


// Таминотчилар
Route::screen('stock/suppliers', \App\Orchid\Screens\Buy\SupplierListScreen::class)
    ->name('platform.suppliers')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push('Таминотчилар');
    });

// Omborga maxsulotlarni sotib olish
Route::screen('stock/buy/{supplier}', \App\Orchid\Screens\Buy\MainBuyScreen::class)
    ->name('platform.buy_products')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push('Махсулот sotib olish');
    });

// Мижозлар
Route::screen('stock/customers', \App\Orchid\Screens\Sell\CustomerListScreen::class)
    ->name('platform.customers')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push('Мижозлар');
    });

// Ombordagi maxsulotlarni sotish
Route::screen('stock/sell/{customer}', \App\Orchid\Screens\Sell\MainSellScreen::class)
    ->name('platform.sell_products')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push('Махсулот sotib olish');
    });

// Sotib olingan partiyalar
Route::screen('buy/parties', \App\Orchid\Screens\Buy\PurchasesPartyScreen::class)
    ->name('platform.buy_parties')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push('Sotib olingan partiyalar');
    });

// Sotilgan  partiyalar
Route::screen('sell/parties', \App\Orchid\Screens\Sell\SalesPartyScreen::class)
    ->name('platform.sell_parties')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push('Sotilgan partiyalar');
    });

// Sotib olingan maxsulotlar
Route::screen('purchases', \App\Orchid\Screens\Buy\PurchasesScreen::class)
    ->name('platform.purchases')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push('Sotib olingan maxsulotlar');
    });

// Sotilgan  maxsulotlar
Route::screen('sales', \App\Orchid\Screens\Sell\SalesScreen::class)
    ->name('platform.sales')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push('Sotilgan maxsulotlar');
    });

// Buyurtmalar
Route::screen('orders', \App\Orchid\Screens\Order\OrderListScreen::class)
    ->name('platform.orders')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push('Buyurtmalar');
    });

// Buyurtmalar
Route::screen('payments', \App\Orchid\Screens\Payment\PaymentListScreen::class)
    ->name('platform.payments')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push('To\'lovlar');
    });

// Chiqimlar
Route::screen('expences', \App\Orchid\Screens\Expences\ExpenceListScreen::class)
    ->name('platform.expences')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push('Chiqimlar');
    });

// Qarzdorlar
Route::screen('customer_duties', \App\Orchid\Screens\Duties\CustomerDutiesListScreen::class)
    ->name('platform.customer_duties')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push('Qarzdorlar');
    });

// Qarzlarim
Route::screen('my_duties', \App\Orchid\Screens\Duties\MyDutiesListScreen::class)
    ->name('platform.my_duties')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push('Qarzlarim');
    });

//Example screen routes
Route::screen('example-fields', ExampleFieldsScreen::class)->name('platform.example.fields');
Route::screen('example-layouts', ExampleLayoutsScreen::class)->name('platform.example.layouts');
Route::screen('example-charts', ExampleChartsScreen::class)->name('platform.example.charts');
Route::screen('example-editors', ExampleTextEditorsScreen::class)->name('platform.example.editors');
Route::screen('example-cards', ExampleCardsScreen::class)->name('platform.example.cards');
Route::screen('example-advanced', ExampleFieldsAdvancedScreen::class)->name('platform.example.advanced');

//Route::screen('idea', Idea::class, 'platform.screens.idea');

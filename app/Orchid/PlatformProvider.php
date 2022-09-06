<?php

declare(strict_types=1);

namespace App\Orchid;

use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;
use Orchid\Support\Color;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * @param Dashboard $dashboard
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);

        // ...
    }

    /**
     * @return Menu[]
     */
    public function registerMainMenu(): array
    {
        return [
            Menu::make('Zaxira maxsulotlar')
                ->icon('database')
                ->route('platform.stock_list')
                ->permission('platform.stock.list')
                ->title('Ombor'),

            Menu::make('Sotish')
                ->icon('handbag')
                ->list([
                    Menu::make('Мижозлар')->icon('people')
                        ->route('platform.customers')->permission('platform.stock.sell'),
                    Menu::make('Sotilgan partiyalar')->icon('call-out')
                        ->route('platform.sell_parties')->permission('platform.stock.sell_parties'),
                    Menu::make('Sotilgan maxsulotlar')->icon('action-redo')
                        ->route('platform.sales')->permission('platform.stock.sales'),
                ]),

            Menu::make('Olish')
                ->icon('basket-loaded')
                ->list([
                    Menu::make('Таминотчилар')->icon('organization')
                        ->route('platform.suppliers')->permission('platform.stock.buy'),
                    Menu::make('Olingan partiyalar')->icon('call-in')
                        ->route('platform.buy_parties')->permission('platform.stock.buy_parties'),
                    Menu::make('Olingan maxsulotlar')->icon('action-undo')
                        ->route('platform.purchases')->permission('platform.stock.purchases'),
                ]),

            Menu::make('Buyurtmalar')
                ->icon('history')
                ->route('platform.orders')
                ->permission('platform.stock.orders')
                ->title('Sotuv'),

            Menu::make('To\'lovlar')
                ->icon('dollar')
                ->route('platform.payments')
                ->permission('platform.stock.payments'),

            Menu::make('Chiqimlar')
                ->icon('calculator')
                ->route('platform.expences')
                ->permission('platform.stock.expences'),

            Menu::make('Qarzdorlar')
                ->icon('book-open')
                ->route('platform.customer_duties')
                ->permission('platform.stock.customer_duties'),

            Menu::make('Qarzlar')
                ->icon('pie-chart')
                ->route('platform.my_duties')
                ->permission('platform.stock.my_duties'),

            Menu::make('Foydalanuvchilar')
                ->icon('user')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title('Tizim'),

            Menu::make('Rollar')
                ->icon('lock')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles'),
        ];
    }

    /**
     * @return Menu[]
     */
    public function registerProfileMenu(): array
    {
        return [
            Menu::make('Profile')
                ->route('platform.profile')
                ->icon('user'),
        ];
    }

    /**
     * @return ItemPermission[]
     */
    public function registerPermissions(): array
    {
        return [
            ItemPermission::group('Tizim (Super Admin uchun)')
                ->addPermission('platform.systems.roles', 'Rollar')
                ->addPermission('platform.systems.users', 'Foydalanuvchilar'),

            ItemPermission::group('Ombor (Filial foydalanuvchilari uchun)')
                ->addPermission('platform.stock.list', 'Zaxira maxsulotlar')
                ->addPermission('platform.stock.add_product', 'Zaxira maxsulotni omborga qo\'shish)')
                ->addPermission('platform.stock.sell', 'Sotish')
                ->addPermission('platform.stock.buy', 'Sotib olish')
                ->addPermission('platform.stock.buy_parties', 'Olingan partiyalar')
                ->addPermission('platform.stock.sell_parties', 'Sotilgan partiyalar')
                ->addPermission('platform.stock.purchases', 'Olingan maxsulotlar')
                ->addPermission('platform.stock.sales', 'Sotilgan maxsulotlar')
                ->addPermission('platform.stock.orders', 'Buyurtmalar')
                ->addPermission('platform.stock.payments', 'To\'lovlar')
                ->addPermission('platform.stock.expences', 'Chiqimlar')
                ->addPermission('platform.stock.customer_duties', 'Qarzdorlar')
                ->addPermission('platform.stock.my_duties', 'Qarzlar')
        ];
    }
}

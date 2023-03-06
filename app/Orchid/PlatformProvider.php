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
            Menu::make('Сотув')
                ->icon('handbag')
                ->list([
                    /*Menu::make('Мижозлар')->icon('people')
                        ->route('platform.customers')->permission('platform.stock.sell'),*/
                    Menu::make('Сотилган партиялар')->icon('call-out')
                        ->route('platform.sell_parties')->permission('platform.stock.sell_parties'),
                    Menu::make('Сотилган махсулотлар')->icon('action-redo')
                        ->route('platform.sales')->permission('platform.stock.sales'),
                ])->permission('platform.stock.sell'),

            Menu::make('Буюртмалар')
                ->icon('history')
                ->route('platform.orders')
                ->permission('platform.stock.orders')
                ->title('Сотув'),

            Menu::make('Телеграм')
                ->icon('paper-plane')
                ->route('platform.telegram-orders')
                ->permission('platform.stock.telegram-orders'),

            Menu::make('Тўловлар')
                ->icon('dollar')
                ->route('platform.payments')
                ->permission('platform.stock.payments'),

            Menu::make('Чиқимлар')
                ->icon('calculator')
                ->route('platform.expences')
                ->permission('platform.stock.expences'),

            Menu::make('Қарздорлар')
                ->icon('book-open')
                ->route('platform.customer_duties')
                ->permission('platform.stock.customer_duties'),

            Menu::make('Фойдаланувчилар')
                ->icon('user')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title('Тизим'),

            Menu::make('Роллар')
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
            ItemPermission::group('Тизим (Супер Aдмин учун)')
                ->addPermission('platform.systems.roles', 'Роллар')
                ->addPermission('platform.systems.users', 'Фойдаланувчилар'),

            ItemPermission::group('Омбор (Филиал фойдаланувчилари учун)')
                ->addPermission('platform.stock.sell', 'Сотиш')
                ->addPermission('platform.stock.sell_parties', 'Сотилган партиялар')
                ->addPermission('platform.stock.sales', 'Сотилган махсулотлар')
                ->addPermission('platform.stock.orders', 'Буюртмалар')
                ->addPermission('platform.stock.telegram-orders', 'Телеграм ботдан буюртмалар')
                ->addPermission('platform.stock.payments', 'Тўловлар')
                ->addPermission('platform.stock.expences', 'Чиқимлар')
                ->addPermission('platform.stock.customer_duties', 'Қарздорлар')
        ];
    }
}

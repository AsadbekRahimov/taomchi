<?php

namespace App\Orchid\Screens\Sell;

use App\Models\Customer;
use App\Orchid\Layouts\Sell\CustomersListTable;
use Orchid\Screen\Screen;

class CustomerListScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'customers' => Customer::query()->orderByDesc('id')->paginate(15),
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Мижозлар рўйҳати';
    }

    public function description(): ?string
    {
        return 'Омборхона мижозлари рўйҳати';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.stock.sell',
        ];
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            CustomersListTable::class,
        ];
    }
}

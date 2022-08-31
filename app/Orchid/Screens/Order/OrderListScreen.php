<?php

namespace App\Orchid\Screens\Order;

use App\Models\Order;
use App\Orchid\Layouts\Order\OrderListTable;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Screen;

class OrderListScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable
    {
        $branch_id = Auth::user()->branch_id ? : 0;
        return [
            'orders' => Order::query()->with(['customer', 'user', 'cards'])->where('branch_id', $branch_id)->orderByDesc('id')->paginate(15),
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Buyurtmalar';
    }

    public function description(): ?string
    {
        return 'Omborga tushgan buyurtmalar ro\'yhati';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.stock.orders',
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
            OrderListTable::class,
        ];
    }
}

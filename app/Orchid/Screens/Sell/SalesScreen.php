<?php

namespace App\Orchid\Screens\Sell;

use App\Models\Sale;
use App\Orchid\Layouts\Sell\SalesTable;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Screen;

class SalesScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable
    {
        $branch_id = Auth::user()->branch_id?: 0;
        return [
            'sales' => Sale::query()->with(['customer', 'product'])
                ->where('branch_id', $branch_id)->orderByDesc('id')->paginate(15),
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Сотилган махсулотлар';
    }

    public function description(): ?string
    {
        return 'Омбордан сотилган махсулотлар';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.stock.sales',
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
            SalesTable::class
        ];
    }
}

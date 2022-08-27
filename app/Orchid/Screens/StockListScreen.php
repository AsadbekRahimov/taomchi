<?php

namespace App\Orchid\Screens;

use App\Models\Stock;
use App\Orchid\Layouts\Stock\StockListTable;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Screen;

class StockListScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'stock' => Stock::query()->with(['product'])->where('branch_id', Auth::user()->branch_id)->defaultSort('updated_at', 'desc')->paginate(15),
        ];
    }



    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Zaxira maxsulotlar';
    }

    public function description(): ?string
    {
        return 'Ombordagi mavjud maxsulotlarinig qoldiq miqdorlari';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.stock.list',
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
            StockListTable::class,
        ];
    }
}

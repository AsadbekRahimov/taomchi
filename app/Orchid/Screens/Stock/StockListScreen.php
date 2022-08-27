<?php

namespace App\Orchid\Screens\Stock;

use App\Models\Stock;
use App\Orchid\Layouts\Stock\QuantityEditLayout;
use App\Orchid\Layouts\Stock\StockListTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

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
            'stock' => Stock::query()->with(['product'])->where('branch_id', Auth::user()->branch_id)->defaultSort('id', 'desc')->paginate(15),
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
        return [
            Link::make('Maxsulot qo\'shish')->route('platform.add_products')->icon('plus')->canSee(Auth::user()->hasAccess('platform.stock.add_product')),
        ];
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
            Layout::modal('asyncEditQuantityModal', QuantityEditLayout::class)
                ->async('addToStock')->applyButton('Saqlash')->closeButton('Yopish'),
        ];
    }


    public function asyncGetStock(Stock $stock)
    {
        return [
            'stock' => $stock,
        ];
    }

    public function saveStock(Request $request, Stock $stock)
    {
        $stock->quantity = (int)$request->quantity;
        $stock->save();
        Alert::success('Maxsulot qoldig\'i muaffaqiyatli yangilandi');
    }
}

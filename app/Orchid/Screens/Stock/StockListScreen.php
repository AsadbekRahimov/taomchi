<?php

namespace App\Orchid\Screens\Stock;

use App\Models\Stock;
use App\Orchid\Layouts\FilterSelections\StockSelection;
use App\Orchid\Layouts\Stock\ColorIndicator;
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
            'stock' => Stock::query()->filters(StockSelection::class)->with(['product.measure'])
                ->where('branch_id', Auth::user()->branch_id)
                ->orderByDesc('id')->paginate(50),
        ];
    }



    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Захира махсулотлар';
    }

    public function description(): ?string
    {
        return 'Омбордаги мавжуд махсулотлариниг қолдиқ миқдорлари';
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
            Link::make('Махсулот қўшиш')->route('platform.add_products')->icon('plus')->canSee(Auth::user()->hasAccess('platform.stock.add_product')),
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
            StockSelection::class,
            ColorIndicator::class,
            StockListTable::class,
            Layout::modal('asyncEditQuantityModal', QuantityEditLayout::class)
                ->async('addToStock')->applyButton('Сақлаш')->closeButton('Ёпиш'),
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
        $quantity = $request->box === '1' ? $stock->product->box * $request->quantity : $request->quantity;
        $stock->quantity = $quantity;
        $stock->save();
        Alert::success('Махсулот қолдиғи муаффақиятли янгиланди');
    }

    public function deleteStock(Request $request)
    {
        Stock::destroy($request->id);
        Alert::success('Омборхона махсулоти муаффақиятли ўчирилди');
    }
}

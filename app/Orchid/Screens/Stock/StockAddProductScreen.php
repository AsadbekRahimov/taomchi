<?php

namespace App\Orchid\Screens\Stock;

use App\Models\Product;
use App\Models\Stock;
use App\Orchid\Layouts\Stock\QuantityEditLayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class StockAddProductScreen extends Screen
{

    public $max_count;
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable
    {
        $stock_ids = Stock::query()->where('branch_id', Auth::user()->branch_id)->pluck('product_id')->toArray();
        $this->max_count = Auth::user()->branch_id ? Product::query()->whereNotIn('id', $stock_ids)->count() : 0;
        return [
            'products' => Product::query()->whereNotIn('id', $stock_ids)->get(),
        ];
    }



    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Zaxira maxsulotlari qo\'shish';
    }

    public function description(): ?string
    {
        return 'Ombordaga mavjud maxsulot turlarini kiritish';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.stock.add_product',
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
            Button::make('Maxsulotlarni biriktirish')->method('addProduct')->icon('plus')->canSee($this->max_count),
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
            Layout::rows([
                Matrix::make('products')
                    ->columns([
                        '' => 'id',
                        'Maxsulot' => 'name',
                    ])->fields([
                        'id' => Input::make('id')->type('number')->hidden(),
                        'name' => Input::make('name')->disabled(),
                    ])->maxRows($this->max_count),
            ]),
        ];
    }

    public function addProduct(Request $request)
    {
        $branch_id = Auth::user()->branch_id;
        $count = $this->addToStock($request->products, $branch_id);
        Cache::forget('stock_' . $branch_id);
        Alert::success($count . ' ta maxsulotlar omborga muaffaqiyatli qo\'shildi');

        return redirect()->route('platform.stock_list');
    }

    private function addToStock($products, $branch_id)
    {
        $count = 0;

        foreach ($products as $product) {
            $id = (int)$product['id'];
            if(!is_null($id)) {
                Stock::addNewItem($id, $branch_id);
                $count++;
            }
        }

        return $count;
    }
}

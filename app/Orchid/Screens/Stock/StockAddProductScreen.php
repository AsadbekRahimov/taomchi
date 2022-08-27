<?php

namespace App\Orchid\Screens\Stock;

use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class StockAddProductScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'products' => Product::query()->limit(10)->get(),
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
            Button::make('Maxsulotlarni biriktirish')->method('addProduct')->icon('plus'),
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
                Matrix::make('products'),
            ]),
        ];
    }

    public function addProduct(Request $request)
    {
        dd($request->all());
    }
}

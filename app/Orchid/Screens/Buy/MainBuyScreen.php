<?php

namespace App\Orchid\Screens\Buy;

use App\Models\Product;
use App\Models\Stock;
use App\Models\Supplier;
use App\Orchid\Layouts\ProductListener;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;

class MainBuyScreen extends Screen
{

    public $supplier;
    /**
     * Query data.
     *
     * @return array
     */
    public function query(Supplier $supplier): iterable
    {
        $this->supplier = $supplier;
        $branch_id = Auth::user()->branch_id ? Auth::user()->branch_id : 0;
        return [
            'products' => Cache::remember('stock_' . $branch_id, 24 * 60 * 60, function () use ($branch_id) {
                return Stock::query()->with(['product'])->where('branch_id', $branch_id)->get();
            }),
        ];
    }



    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Sotib olish | ' . $this->supplier->name;
    }

    public function description(): ?string
    {
        return 'Omborga maxsulotlarni qabul qilish';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.stock.buy',
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
            //ProductListener::class, --> if has many products
            Layout::table('products', [
                TD::make('product_id', 'Maxsulot')->render(function (Stock $stock) {
                    return Link::make($stock->product->name)->href('/admin/crud/view/products/' . $stock->product_id);
                })->cantHide(),
                TD::make('quantity', 'Mavjud miqdori')->render(function (Stock $stock) {
                    return $stock->quantity ;
                })->cantHide(),
            ])->title('Omborxona maxsulotlari'),
        ];
    }

    /*public function asyncProducts(string $product)
    {
        return [
            'products' => Product::query()->where('name', 'LIKE', "%" .  $product . "%")->get(),
            'max_counts' => Product::query()->where('name', 'LIKE', "%" .  $product . "%")->count(),
        ];
    }*/ // if has many products
}

<?php

namespace App\Orchid\Screens\Product;

use App\Models\Product;
use App\Orchid\Layouts\Product\AddProduct;
use App\Orchid\Layouts\Product\ProductInfo;
use App\Orchid\Layouts\Product\ProductsTable;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class ProductListScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'products' => Product::query()->filters()->with(['measure'])->orderByDesc('id')->paginate(15),
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Махсулотлар';
    }

    public function description(): ?string
    {
        return 'Махсулотлар рўйҳати';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.stock.products',
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
            ModalToggle::make('')
                ->icon('plus')
                ->method('addNewProduct')
                ->modal('addNewProductModal')
                ->modalTitle('Янги махсулот қўшиш'),
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
            ProductsTable::class,
            Layout::modal('addNewProductModal', AddProduct::class)
                ->applyButton('Сақлаш')->closeButton('Ёпиш'),
            Layout::modal('asyncGetProductModal', ProductInfo::class)
                ->async('asyncGetProduct')->size(Modal::SIZE_LG)
                ->applyButton('Сақлаш')->closeButton('Ёпиш'),
        ];
    }

    public function asyncGetProduct(Product $product)
    {
        return [
            'name' => $product->name,
            'measure_id' => $product->measure_id,
            'prices' => $product->prices,
        ];
    }

    public function closeTelegram(Product $product)
    {
        $product->update(['for_telegram' => 0]);
        Alert::success('Махсулот телеграм ботдан олинди!');
    }

    public function openTelegram(Product $product)
    {
        $product->update(['for_telegram' => 1]);
        Alert::success('Махсулот телеграм ботга қўшилди');
    }

    public function saveProductInfo(Request $request)
    {
        // TODO: complete save method for product info
    }

    public function addNewProduct(Request $request)
    {
        Product::query()->create(['name' => $request->name, 'measure_id' => $request->measure_id]);
        Alert::success('Янги махсулот қўшилди');
    }

    public function deleteProduct(Product $product)
    {
        if($product->sales()->count() || $product->telegramOrderItems()->count())
        {
            Alert::error('Сотилган махсулотлар ёки буюртмалар мавжудлиги учун бу махсулотни ўчира олмайсиз!');
        }else {
            $product->cards()->delete();
            $product->telegramCards()->delete();
            $product->delete();
        }
    }
}

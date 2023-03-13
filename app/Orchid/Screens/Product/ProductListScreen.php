<?php

namespace App\Orchid\Screens\Product;

use App\Models\Product;
use App\Orchid\Layouts\Product\ProductInfo;
use App\Orchid\Layouts\Product\ProductsTable;
use Illuminate\Http\Request;
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
            ProductsTable::class,
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
            'for_telegram' => $product->for_telegram,
            'telegram_message_id' => $product->telegram_message_id,
            'prices' => $product->prices,
        ];
    }

    public function closeTelegram(Product $product)
    {
        $product->update([
            'for_telegram' => 0
        ]);

        Alert::success('Махсулот телеграм ботдан олинди!');
    }

    public function openTelegram(Product $product)
    {
        if (is_null($product->telegram_message_id)) {
            Alert::error('Телеграм хабар ID киритилмаган!');
        } else {
            $product->update([
                'for_telegram' => 1
            ]);
            Alert::success('Махсулот телеграм ботга қўшилди');
        }
    }

    public function saveProductInfo(Request $request)
    {
        // TODO: complate save method for product info
    }
}

<?php

namespace App\Orchid\Screens\Sell;

use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;

class MainSellScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'suppliers' => Supplier::query()->get(),
        ];
    }



    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Sotib olish';
    }

    public function description(): ?string
    {
        return 'Omborga maxsulotlarni qabul qilish';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.stock.sell',
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
            Link::make('Maxsulot qo\'shish')->route('platform.add_products')->icon('plus')
                ->canSee(Auth::user()->hasAccess('platform.stock.add_product')),
        ];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [];
    }
}

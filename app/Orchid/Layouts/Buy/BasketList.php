<?php

namespace App\Orchid\Layouts\Buy;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class BasketList extends Rows
{
    /**
     * Used to create the title of a group of form elements.
     *
     * @var string|null
     */
    protected $title;

    /**
     * Get the fields elements to be displayed.
     *
     * @return Field[]
     */
    protected function fields(): iterable
    {
        $products = Cache::rememberForever('products', function () {
            return Product::query()->pluck('name', 'id');
        });

        return [
           Matrix::make('baskets')
               ->columns([
                   '' => 'id',
                   'Maxsulot' => 'product_id',
                   'Moqdori (dona)' => 'quantity',
                   'Dona narxi' => 'price',
               ])->fields([
                   'id' => Input::make('quantity')->type('number')->required()->hidden(),
                   'product_id' => Select::make('product_id')->options($products),
                   'quantity' => Input::make('quantity')->type('number')->required(),
                   'price' => Input::make('price')->type('number')->required(),
               ]),
        ];
    }
}

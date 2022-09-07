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
        $products = Cache::get('products');

        return [
           Matrix::make('baskets')
               ->columns([
                   '' => 'id',
                   'Махсулот' => 'product_id',
                   'Моқдори (дона)' => 'quantity',
                   'Дона нархи' => 'price',
               ])->fields([
                   'id' => Input::make('quantity')->type('number')->required()->hidden(),
                   'product_id' => Select::make('product_id')->options($products),
                   'quantity' => Input::make('quantity')->type('number')->required(),
                   'price' => Input::make('price')->type('number')->required(),
               ]),
            Input::make('total_price')->title('Умумий тўлов суммаси')->type('number')
                ->help('Aгар тўлов миқдори умумий суммадан кам болса бу қарз сифатида ёзилади!')->required(),
        ];
    }
}

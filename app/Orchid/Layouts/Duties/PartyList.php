<?php

namespace App\Orchid\Layouts\Duties;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class PartyList extends Rows
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
            Matrix::make('products')
                ->columns([
                    'Махсулот' => 'product_id',
                    'Моқдори (дона)' => 'quantity',
                    'Дона нархи' => 'price',
                ])->fields([
                    'product_id' => Select::make('product_id')->options($products),
                    'quantity' => Input::make('quantity'),
                    'price' => Input::make('price')->type('number')->required(),
                ])->removableRows(false),
            Group::make([
                Input::make('total_price')->title('Умумий миқдори'),
                Input::make('duty')->title('Қарз миқдори'),
            ]),
        ];
    }
}

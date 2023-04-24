<?php

namespace App\Orchid\Layouts\Order;

use App\Services\CacheService;

use Orchid\Screen\Field;
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
        $products = CacheService::ProductsKeyValue();

        return [
            Matrix::make('products')
                ->columns([
                    'Махсулот' => 'product_id',
                    'Моқдори (дона)' => 'count',
                    'Дона нархи' => 'price',
                ])->fields([
                    'product_id' => Select::make('product_id')->options($products),
                    'quantity' => Input::make('quantity')->type('number'),
                    'price' => Input::make('price')->type('number'),
                ])->removableRows(false),
        ];
    }
}

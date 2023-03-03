<?php

namespace App\Orchid\Layouts\Sell;

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
            Matrix::make('sales')
                ->columns([
                    'Махсулот' => 'product_id',
                    'Моқдори (дона)' => 'quantity',
                    'Дона нархи' => 'price',
                ])->fields([
                    'product_id' => Select::make('product_id')->options($products),
                    'quantity' => Input::make('quantity'),
                    'price' => Input::make('price')->type('number')->required(),
                ])->removableRows(false),
        ];
    }
}

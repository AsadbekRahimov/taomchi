<?php

namespace App\Orchid\Layouts\Sell;

use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class SalesTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'sales';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID'),
            TD::make('product_id', 'Maxsulot')->render(function ($model){
                return $model->product->name;
            }),
            TD::make('quantity', 'Miqdori'),
            TD::make('price', 'Sotilgan narxi')->render(function ($model){
                return number_format($model->price);
            }),
            TD::make('customer_id', 'Mijoz')->render(function ($model){
                return $model->customer->name;
            }),
        ];
    }
}

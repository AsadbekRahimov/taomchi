<?php

namespace App\Orchid\Layouts\Buy;

use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class PurchasesTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'purchases';

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
            TD::make('profit', 'Qoladigan foyda')->render(function ($model){
                return number_format($model->profit);
            }),
            TD::make('supplier_id', 'Taminotchi')->render(function ($model){
                return $model->supplier->name;
            }),
        ];
    }
}

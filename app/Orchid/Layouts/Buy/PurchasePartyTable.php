<?php

namespace App\Orchid\Layouts\Buy;

use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class PurchasePartyTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'parties';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID')->cantHide(),
            TD::make('user_id', 'Sotuvchi')->render(function ($model){
                return $model->user->name;
            })->cantHide(),
            TD::make('supplier_id', 'Taminotchi')->render(function ($model) {
                return $model->supplier->name;
            })->cantHide(),
            TD::make('total_price', 'Umumiy summasi')->render(function ($model){
                return number_format($model->purchasesSum());
            }),
            TD::make('total_profit', 'Umumiy foydasi')->render(function ($model){
                return number_format($model->purchases->sum('profit'));
            }),
            TD::make('created_at', 'Kiritilgan sana')->render(function ($model){
                return $model->created_at->toDateTimeString();
            })->cantHide(),
        ];
    }
}

<?php

namespace App\Orchid\Layouts\Buy;

use App\Services\HelperService;
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
            TD::make('product_id', 'Махсулот')->render(function ($model){
                return $model->product->name;
            }),
            TD::make('quantity', 'Миқдори')->render(function ($model){
                return HelperService::getQuantity($model->quantity, $model->product->box);
            }),
            TD::make('price', 'Сотиб олинган нархи')->render(function ($model){
                return number_format($model->price);
            }),
            TD::make('profit', 'Қоладиган фойда')->render(function ($model){
                return number_format($model->profit);
            }),
            TD::make('supplier_id', 'Таминотчи')->render(function ($model){
                return $model->supplier->name;
            }),
        ];
    }
}

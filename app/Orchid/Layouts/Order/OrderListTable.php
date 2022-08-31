<?php

namespace App\Orchid\Layouts\Order;

use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class OrderListTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'orders';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID'),
            TD::make('customer_id', 'Mijoz')->render(function ($model) {
               return $model->customer->name;
            })->cantHide(),
            TD::make('user_id', 'Foydalanuvchi')->render(function ($model) {
                return $model->user->name;
            }),
            TD::make('discount', 'Chegirma'),
            TD::make('total_price', 'Umumiy summasi')->render(function ($model) {
                return $model->cardsSum();
            }),
            TD::make('created_at', 'Sana')->render(function ($model) {
                return $model->created_at->toDateTimeString();
            })->cantHide(),
        ];
    }
}

<?php

namespace App\Orchid\Layouts\Order;

use App\Services\HelperService;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
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
            TD::make('discount', 'Chegirma')->render(function ($model) {
                return number_format($model->discount);
            }),
            TD::make('total_price', 'Umumiy summasi')->render(function ($model) {
                return HelperService::getOrderPrice($model);
            }),
            TD::make('created_at', 'Sana')->render(function ($model) {
                return $model->created_at->toDateTimeString();
            })->cantHide(),
            TD::make('action', 'Amallar')->render(function ($model) {
                return DropDown::make('')->icon('list')->list([
                    Button::make('Bekor qilish')->method('deleteCard')->icon('trash')
                ]);
            })
        ];
    }
}

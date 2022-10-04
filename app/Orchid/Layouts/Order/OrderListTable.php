<?php

namespace App\Orchid\Layouts\Order;

use App\Services\HelperService;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
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
            TD::make('customer_id', 'Мижоз')->render(function ($model) {
                return Link::make($model->customer->name)->route('platform.customer_info', ['customer' => $model->customer_id]);
            })->cantHide(),
            TD::make('user_id', 'Сотувчи')->render(function ($model) {
                return $model->user->name;
            }),
            TD::make('discount', 'Чегирма')->render(function ($model) {
                return number_format($model->discount);
            }),
            TD::make('total_price', 'Умумий суммаси')->render(function ($model) {
                return HelperService::getOrderPrice($model);
            }),
            TD::make('created_at', 'Сана')->render(function ($model) {
                return $model->created_at->toDateTimeString();
            })->cantHide(),
            TD::make('action', 'Aмаллар')->render(function ($model) {
                return DropDown::make('')->icon('list')->list([
                    Link::make('Тўлов чеки')->icon('printer')
                        ->route('printCheck', ['id' => $model->id])->target('blank'),
                    ModalToggle::make('Тўлиқ тўлов қилиш')
                        ->method('fullPayment')
                        ->modal('fullPaymentModal')
                        ->icon('dollar')
                        ->parameters([
                            'id' => $model->id,
                            'customer_id' => $model->customer_id,
                        ])->modalTitle($model->customer->name . ' | Тўлов суммаси: ' . number_format($model->cardsSum() - $model->discount)),
                    ModalToggle::make('Қисман тўлов қилиш')
                        ->method('partPayment')
                        ->modal('partPaymentModal')
                        ->icon('book-open')
                        ->parameters([
                            'id' => $model->id,
                            'customer_id' => $model->customer_id,
                        ])->modalTitle($model->customer->name . ' | Тўлов суммаси: ' . number_format($model->cardsSum() - $model->discount)),
                    ModalToggle::make('Chegirma kiritish')
                        ->method('addDiscount')
                        ->modal('discountModal')
                        ->icon('tag')
                        ->parameters([
                            'id' => $model->id,
                        ])->modalTitle($model->customer->name . ' | Тўлов суммаси: ' . number_format($model->cardsSum() - $model->discount)),
                    Button::make('Буюртмани бекор қилиш')
                        ->method('deleteCard')
                        ->icon('trash')
                        ->parameters([
                            'customer_id' => $model->customer_id,
                        ])->confirm('Сиз ростдан ҳам ушбу буюртмани ўчирмоқчимисиз?')
                ]);
            })
        ];
    }
}

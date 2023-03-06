<?php

namespace App\Orchid\Layouts\TelegramOrder;

use App\Services\HelperService;
use Illuminate\Support\Facades\Auth;
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
        $user = Auth::user();
        $call_center = $user->inRole('call_center') ? 1 : 0;
        $superadmin = Auth::user()->inRole('super_admin') ? 1 : 0;
        $payment = ($user->inRole('courier') or $superadmin) ? 1 : 0;

        return [
            TD::make('id', 'ID'),
            TD::make('customer_id', 'Мижоз')->render(function ($model) {
                return Link::make($model->customer->all_name)->route('platform.customer_info', ['customer' => $model->customer_id]);
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
            TD::make('action', 'Aмаллар')->render(function ($model) use ($call_center, $superadmin, $payment) {
                return DropDown::make('')->icon('list')->list([
                    Link::make('Тўлов чеки')->icon('printer')
                        ->route('printCheck', ['id' => $model->id])->target('blank')->canSee($superadmin || $call_center),
                    Button::make('Қарзга бериш')
                        ->method('duty')
                        ->icon('clock')
                        ->parameters([
                            'id' => $model->id,
                            'customer_id' => $model->customer_id,
                        ])->confirm($model->customer->name . ' - Қарз суммаси: ' . number_format($model->cardsSum() - $model->discount))
                        ->canSee($payment),
                    ModalToggle::make('Тўлиқ тўлов қилиш')
                        ->method('fullPayment')
                        ->modal('fullPaymentModal')
                        ->icon('dollar')
                        ->parameters([
                            'id' => $model->id,
                            'customer_id' => $model->customer_id,
                        ])->modalTitle($model->customer->name . ' | Тўлов суммаси: ' . number_format($model->cardsSum() - $model->discount))
                        ->canSee($payment),
                    ModalToggle::make('Қисман тўлов қилиш')
                        ->method('partPayment')
                        ->modal('partPaymentModal')
                        ->icon('book-open')
                        ->parameters([
                            'id' => $model->id,
                            'customer_id' => $model->customer_id,
                        ])->modalTitle($model->customer->name . ' | Тўлов суммаси: ' . number_format($model->cardsSum() - $model->discount))
                        ->canSee($payment),
                    Button::make('Буюртмани бекор қилиш')
                        ->method('deleteCard')
                        ->icon('trash')
                        ->parameters([
                            'id' => $model->id,
                        ])->confirm('Сиз ростдан ҳам ушбу буюртмани ўчирмоқчимисиз?')->canSee($superadmin),
                ]);
            })->cantHide(),
        ];
    }
}

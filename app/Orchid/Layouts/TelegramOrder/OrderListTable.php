<?php

namespace App\Orchid\Layouts\TelegramOrder;

use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Support\Color;

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
            TD::make('user_id', 'Мижоз')->render(function ($model) {
                if ($model->user->customer_id)
                    return Link::make($model->user->customer->name)->route('platform.customer_info', ['customer' => $model->user->customer_id]);
                else
                    return Button::make($model->user->phone)->type(Color::PRIMARY())->disabled();
            })->cantHide(),
            TD::make('total_price', 'Умумий суммаси')->render(function ($model) {
                return number_format($model->price);
            }),
            TD::make('created_at', 'Сана')->render(function ($model) {
                return $model->created_at->toDateTimeString();
            })->cantHide(),
            TD::make('action', 'Aмаллар')->render(function ($model) use ($call_center, $superadmin, $payment) {
                $is_customer = (bool)$model->user->customer_id;
                $customer_name = $model->user->customer_id ? $model->user->customer->name : $model->user->phone;
                return $is_customer ? DropDown::make('')->icon('list')->list([
                    Link::make('Тўлов чеки')->icon('printer')
                        ->route('printCheck', ['id' => $model->id])->target('blank')->canSee($superadmin || $call_center),
                    Button::make('Қарзга бериш')
                        ->method('duty')
                        ->icon('clock')
                        ->parameters([
                            'id' => $model->id,
                            'customer_id' => $model->customer_id,
                        ])->confirm($customer_name . ' - Қарз суммаси: ' . number_format($model->price))
                        ->canSee($payment),
                    ModalToggle::make('Тўлиқ тўлов қилиш')
                        ->method('fullPayment')
                        ->modal('fullPaymentModal')
                        ->icon('dollar')
                        ->parameters([
                            'id' => $model->id,
                            'customer_id' => $model->customer_id,
                        ])->modalTitle($customer_name . ' | Тўлов суммаси: ' . number_format($model->price))
                        ->canSee($payment),
                    ModalToggle::make('Қисман тўлов қилиш')
                        ->method('partPayment')
                        ->modal('partPaymentModal')
                        ->icon('book-open')
                        ->parameters([
                            'id' => $model->id,
                            'customer_id' => $model->customer_id,
                        ])->modalTitle($customer_name . ' | Тўлов суммаси: ' . number_format($model->price))
                        ->canSee($payment),
                    Button::make('Буюртмани бекор қилиш')
                        ->method('deleteCard')
                        ->icon('trash')
                        ->parameters([
                            'id' => $model->id,
                        ])->confirm('Сиз ростдан ҳам ушбу буюртмани ўчирмоқчимисиз?')->canSee($superadmin),
                ])->canSee($is_customer) : ModalToggle::make('Мижоз')
                    ->icon('plus')->method('saveUser')->modal('addUserModal')
                    ->asyncParameters([
                        'user' => $model->user_id,
                    ])->modalTitle('Янги мижозни базага қўшиш')->type(Color::SUCCESS());
            })->cantHide(),
        ];
    }
}

<?php

namespace App\Orchid\Layouts\TelegramOrder;

use App\Models\TelegramOrder;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
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
        return [
            TD::make('id', 'ID')->render(function ($model) {
                return '#' . $model->id;
            })->cantHide(),
            TD::make('user_id', 'Мижоз')->render(function ($model) {
                if ($model->user->customer_id)
                    return Link::make($model->user->customer->name)->route('platform.customer_info', ['customer' => $model->user->customer_id]);
                else
                    return Button::make($model->user->phone)->type(Color::PRIMARY())->disabled();
            })->cantHide(),
            TD::make('total_price', 'Умумий суммаси')->render(function ($model) {
                return $model->user->username ?
                    Link::make(number_format($model->price))->type(Color::SUCCESS())->target('_blank')
                        ->icon('cursor')->href('https://t.me/' . $model->user->username)
                    : number_format($model->price);
            })->cantHide(),
            TD::make('state', 'Буюртма холати')->render(function ($model) {
                return $model->state == 'send_order' ?
                    Button::make(TelegramOrder::TYPE[$model->state])
                        ->type(Color::WARNING())
                        ->method('acceptOrder')
                        ->icon('check')
                        ->parameters([
                            'id' => $model->id,
                        ])->confirm('Сиз #' . $model->id . ' рақамли буюртмани қабул қилмоқчимисиз?') :
                    Button::make(TelegramOrder::TYPE[$model->state])
                        ->type(Color::SUCCESS())
                        ->icon('check')->disabled();
            })->cantHide(),
            TD::make('place', 'Худуд')->render(function ($model) {
                return $model->place->name;
            }),
            TD::make('address', 'Манзил')->render(function ($model) {
                return $model->address;
            }),
            TD::make('created_at', 'Сана')->render(function ($model) {
                return $model->created_at->toDateTimeString();
            })->cantHide(),
            TD::make('action', 'Aмаллар')->render(function ($model) use ($call_center, $superadmin) {
                $is_customer = (bool)$model->user->customer_id;
                $customer_name = $model->user->customer_id ? $model->user->customer->name : $model->user->phone;
                $accepted = $model->state == 'accepted_order';
                return $is_customer? DropDown::make('')->icon('list')->list([
                    ModalToggle::make('Махсулотлар')
                        ->icon('eye')
                        ->modal('asyncGetProductsModal')
                        ->modalTitle('Буюртма: №' . $model->id . ' | Мижоз: ' . $model->user->phone)
                        ->asyncParameters([
                            'order' => $model->id,
                        ]),
                    Link::make('Тўлов чеки')->icon('printer')
                        ->route('print-tg-order', ['id' => $model->id])->target('blank')->canSee($accepted && ($superadmin || $call_center)),
                    Button::make('Қарзга бериш')
                        ->method('duty')
                        ->icon('clock')
                        ->parameters([
                            'id' => $model->id,
                        ])->confirm($customer_name . ' - Қарз суммаси: ' . number_format($model->price))
                        ->canSee($accepted),
                    ModalToggle::make('Тўлиқ тўлов қилиш')
                        ->method('fullPayment')
                        ->modal('fullPaymentModal')
                        ->icon('dollar')
                        ->parameters([
                            'id' => $model->id,
                        ])->modalTitle($customer_name . ' | Тўлов суммаси: ' . number_format($model->price))
                        ->canSee($accepted),
                    ModalToggle::make('Қисман тўлов қилиш')
                        ->method('partPayment')
                        ->modal('partPaymentModal')
                        ->icon('book-open')
                        ->parameters([
                            'id' => $model->id,
                        ])->modalTitle($customer_name . ' | Тўлов суммаси: ' . number_format($model->price))
                        ->canSee($accepted),
                    ModalToggle::make('Буюртмани бекор қилиш')
                        ->method('deleteOrder')
                        ->modal('deleteOrderModal')
                        ->icon('trash')
                        ->parameters([
                            'id' => $model->id,
                        ]),
                ])->canSee($is_customer) : DropDown::make('')->icon('list')->list([
                    ModalToggle::make('Мижоз')
                        ->icon('plus')->method('saveUser')->modal('addUserModal')
                        ->asyncParameters([
                            'user' => $model->user_id,
                        ])->modalTitle('Янги мижозни базага қўшиш')->type(Color::SUCCESS()),
                    ModalToggle::make('Махсулотлар')
                        ->icon('eye')
                        ->modal('asyncGetProductsModal')
                        ->modalTitle('Буюртма: №' . $model->id . ' | Мижоз: ' . $model->user->phone)
                        ->asyncParameters([
                            'telegramOrder' => $model->id,
                        ]),
                    Link::make('Тўлов чеки')->icon('printer')
                        ->route('print-tg-order', ['id' => $model->id])->target('blank')->canSee($accepted && ($superadmin || $call_center)),
                    ModalToggle::make('Тўлиқ тўлов қилиш')
                        ->method('fullPayment')
                        ->modal('fullPaymentModal')
                        ->icon('dollar')
                        ->parameters([
                            'id' => $model->id,
                        ])->modalTitle($customer_name . ' | Тўлов суммаси: ' . number_format($model->price))
                        ->canSee($accepted),
                    ModalToggle::make('Буюртмани бекор қилиш')
                        ->method('deleteOrder')
                        ->modal('deleteOrderModal')
                        ->icon('trash')
                        ->parameters([
                            'id' => $model->id,
                        ]),
                ]);
            })->cantHide(),
        ];
    }
}

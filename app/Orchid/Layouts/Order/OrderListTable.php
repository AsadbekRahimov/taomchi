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
            TD::make('customer_id', 'Mijoz')->render(function ($model) {
               return $model->customer->name;
            })->cantHide(),
            TD::make('user_id', 'Sotuvchi')->render(function ($model) {
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
                    Link::make('To\'lov cheki')->icon('printer')
                        ->route('printCheck', ['id' => $model->id])->target('blank'),
                    ModalToggle::make('To\'liq to\'lov qilish')
                        ->method('fullPayment')
                        ->modal('fullPaymentModal')
                        ->icon('dollar')
                        ->parameters([
                            'id' => $model->id,
                            'customer_id' => $model->customer_id,
                        ])->modalTitle($model->customer->name . ' | To\'lov summasi: ' . number_format($model->cardsSum() - $model->discount)),
                    ModalToggle::make('Qisman to\'lov qilish')
                        ->method('partPayment')
                        ->modal('partPaymentModal')
                        ->icon('book-open')
                        ->parameters([
                            'id' => $model->id,
                            'customer_id' => $model->customer_id,
                        ])->modalTitle($model->customer->name . ' | To\'lov summasi: ' . number_format($model->cardsSum() - $model->discount)),
                    ModalToggle::make('Chegirma kiritish')
                        ->method('addDiscount')
                        ->modal('discountModal')
                        ->icon('tag')
                        ->parameters([
                            'id' => $model->id,
                        ])->modalTitle($model->customer->name . ' | To\'lov summasi: ' . number_format($model->cardsSum() - $model->discount)),
                    Button::make('Buyurtmani bekor qilish')
                        ->method('deleteCard')
                        ->icon('trash')
                        ->parameters([
                            'customer_id' => $model->customer_id,
                        ])->confirm('Siz rostdan ham ushbu buyurtmani o\'chirmoqchimisiz?')
                ]);
            })
        ];
    }
}

<?php

namespace App\Orchid\Layouts\Payment;

use App\Models\Payment;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Support\Color;

class PaymentListTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'payments';

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
                return $model->customer->name;
            })->cantHide(),
            TD::make('price', 'Миқдори')->render(function ($model) {
                return Link::make(number_format($model->price))->type(Color::SUCCESS());
            })->cantHide(),
            TD::make('type', 'Тўлов тури')->render(function ($model) {
                return Payment::TYPE[$model->type];
            })->cantHide(),
            TD::make('type', 'Qabul qildi')->render(function ($model) {
                return $model->user->name;
            })->cantHide(),
            TD::make('created_at', 'Сана')->render(function ($model) {
                return $model->created_at->toDateTimeString();
            }),
            TD::make('')->render(function ($model){
                return ModalToggle::make('')
                    ->icon('eye')
                    ->modal('asyncGetPartyModal')
                    ->modalTitle('Партия: №' . $model->party_id . ' | Мижоз: ' . $model->customer->name)
                    ->asyncParameters([
                        'purchaseParty' => $model->id,
                    ]);
            })->cantHide(),
        ];
    }
}

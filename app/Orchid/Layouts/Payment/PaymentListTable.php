<?php

namespace App\Orchid\Layouts\Payment;

use App\Models\Payment;
use App\Services\CacheService;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Select;
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
                if ($model->customer_id)
                    return Link::make($model->customer_name)->route('platform.customer_info', ['customer' => $model->customer_id]);
                else
                    return Button::make($model->customer_name)->type(Color::PRIMARY())->disabled();
            })->filter(Select::make('customer_id')->options(CacheService::getCustomers())->empty('', ''))->cantHide(),
            TD::make('price', 'Миқдори')->render(function ($model) {
                return Link::make(number_format($model->price))->type(Color::SUCCESS());
            })->cantHide(),
            TD::make('type', 'Тўлов тури')->render(function ($model) {
                return Payment::TYPE[$model->type];
            })->filter(Select::make('type')->options(Payment::TYPE)->empty('', ''))->cantHide(),
            TD::make('user_id', 'Qabul qildi')->render(function ($model) {
                return $model->user->name;
            })->cantHide(),
            TD::make('created_at', 'Сана')->render(function ($model) {
                return $model->created_at->toDateTimeString();
            }),
            TD::make('')->render(function ($model){
                return ModalToggle::make('')
                    ->icon('eye')
                    ->modal('asyncGetPartyModal')
                    ->modalTitle('Партия: №' . $model->party_id . ' | Мижоз: ' . $model->customer_name)
                    ->asyncParameters([
                        'salesParty' => $model->party_id,
                    ]);
            })->cantHide(),
        ];
    }
}

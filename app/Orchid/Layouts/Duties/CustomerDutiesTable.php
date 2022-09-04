<?php

namespace App\Orchid\Layouts\Duties;

use App\Services\HelperService;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Support\Color;

class CustomerDutiesTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'customer_duties';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID')->cantHide(),
            TD::make('customer_id', 'Mijoz')->render(function ($model) {
               return $model->customer->name;
            })->cantHide(),
            TD::make('customer_id', 'Telefon raqami')->render(function ($model) {
                return Link::make($model->customer->phone)->href('tel:' . HelperService::telephone($model->customer->phone));
            })->cantHide(),
            TD::make('duty', 'Miqdori')->render(function ($model){
               return Link::make(number_format($model->duty))->type(HelperService::getDutyColor($model->updated_at));
            })->cantHide(),
            TD::make('updated_at', 'Sanasi')->render(function ($model){
               return $model->updated_at->toDateTimeString();
            })->cantHide(),
            TD::make('')->render(function ($model){
                return ModalToggle::make('')
                    ->icon('eye')
                    ->modal('asyncGetPartyModal')
                    ->modalTitle('Partiya: №' . $model->party_id . ' | Mijoz: ' . $model->customer->name)
                    ->asyncParameters([
                        'duty' => $model->id,
                    ]);
            })->cantHide(),
            TD::make('action', 'To\'lov')->render(function ($model) {
                return DropDown::make('')->icon('wallet')->list([
                    ModalToggle::make('To\'liq to\'lov qilish')
                        ->method('fullPayment')
                        ->modal('fullPaymentModal')
                        ->icon('dollar')
                        ->parameters([
                            'id' => $model->id,
                        ])->modalTitle($model->customer->name . ' | To\'lov summasi: ' . number_format($model->duty)),
                    ModalToggle::make('Qisman to\'lov qilish')
                        ->method('partPayment')
                        ->modal('partPaymentModal')
                        ->icon('book-open')
                        ->parameters([
                            'id' => $model->id,
                        ])->modalTitle($model->customer->name . ' | To\'lov summasi: ' . number_format($model->duty)),
                ]);
            })->cantHide(),
        ];
    }
}

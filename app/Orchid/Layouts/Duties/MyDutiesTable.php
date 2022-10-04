<?php

namespace App\Orchid\Layouts\Duties;

use App\Services\HelperService;
use Illuminate\Support\Facades\Cache;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class MyDutiesTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'my_duties';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID')->cantHide(),
            TD::make('supplier_id', 'Таминотчи')->render(function ($model) {
                return $model->supplier->name;
            })->filter(Select::make('suppliers_id')->options(Cache::get('suppliers'))->empty('', ''))->cantHide(),
            TD::make('phone', 'Телефон рақами')->render(function ($model) {
                return Link::make($model->supplier->phone)->href('tel:' . HelperService::telephone($model->supplier->phone));
            })->cantHide(),
            TD::make('duty', 'Миқдори')->render(function ($model){
                return Link::make(number_format($model->duty))->type(HelperService::getDutyColor($model->updated_at));
            })->cantHide(),
            TD::make('updated_at', 'Санаси')->render(function ($model){
                return $model->updated_at->toDateTimeString();
            })->cantHide(),
            TD::make('')->render(function ($model){
                return ModalToggle::make('')
                    ->icon('eye')
                    ->modal('asyncGetPartyModal')
                    ->modalTitle('Партия: №' . $model->party_id . ' | Таминотчи: ' . $model->supplier->name)
                    ->asyncParameters([
                        'duty' => $model->id,
                    ]);
            })->cantHide(),
            TD::make('action', 'Тўлов')->render(function ($model) {
                return DropDown::make('')->icon('wallet')->list([
                    ModalToggle::make('Тўлиқ тўлов қилиш')
                        ->method('fullPayment')
                        ->modal('fullPaymentModal')
                        ->icon('dollar')
                        ->parameters([
                            'id' => $model->id,
                        ])->modalTitle($model->supplier->name . ' | Тўлов суммаси: ' . number_format($model->duty)),
                    ModalToggle::make('Қисман тўлов қилиш')
                        ->method('partPayment')
                        ->modal('partPaymentModal')
                        ->icon('book-open')
                        ->parameters([
                            'id' => $model->id,
                        ])->modalTitle($model->supplier->name . ' | Тўлов суммаси: ' . number_format($model->duty)),
                ]);
            })->cantHide()
        ];
    }
}

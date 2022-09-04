<?php

namespace App\Orchid\Layouts\Duties;

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
            TD::make('duty', 'Miqdori')->render(function ($model){
               return Link::make(number_format($model->duty))->type(Color::DANGER());
            })->cantHide(),
            TD::make('created_at', 'Sanasi')->render(function ($model){
               return $model->created_at->toDateTimeString();
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
        ];
    }
}

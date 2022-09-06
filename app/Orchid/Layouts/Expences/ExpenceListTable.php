<?php

namespace App\Orchid\Layouts\Expences;

use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Support\Color;

class ExpenceListTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'expences';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID'),
            TD::make('party_id', 'Таминотчи')->render(function ($model){
                return $model->party->supplier->name;
            }),
            TD::make('price', 'Миқдори')->render(function ($model){
                return Link::make(number_format($model->price))->type(Color::WARNING());
            }),
            TD::make('created_at', 'Сана')->render(function ($model){
                return $model->created_at->toDateTimeString();
            }),
            TD::make('')->render(function ($model){
                return ModalToggle::make('')
                    ->icon('eye')
                    ->modal('asyncGetPartyModal')
                    ->modalTitle('Партия: №' . $model->party_id . ' | Таминотчи: ' . $model->party->supplier->name)
                    ->asyncParameters([
                        'purchaseParty' => $model->id,
                    ]);
            })->cantHide(),
        ];
    }
}

<?php

namespace App\Orchid\Layouts\Expences;

use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Support\Color;

class OtherExpenceListTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'other_expences';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID'),
            TD::make('description', 'Tasnifi'),
            TD::make('price', 'Miqdori')->render(function ($model){
                return Link::make(number_format($model->price))->type(Color::WARNING());
            }),
            TD::make('created_at', 'Sana')->render(function ($model){
                return $model->created_at->toDateTimeString();
            }),
        ];
    }
}

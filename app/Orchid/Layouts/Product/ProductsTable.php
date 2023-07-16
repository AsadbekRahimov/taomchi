<?php

namespace App\Orchid\Layouts\Product;

use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Support\Color;

class ProductsTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'products';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('id'),
            TD::make('name', 'Номи')->cantHide(),
            TD::make('measure_id', 'Ўлчов бирлиги')->render(function (Model $model) {
                return $model->measure->name;
            }),
            TD::make('for_telegram', 'Telegram')->render(function ($model) {
                return $model->for_telegram ?
                    Button::make()->icon('check')
                        ->type(Color::SUCCESS())->method('closeTelegram')->parameters(['id' => $model->id]) :
                    Button::make()->icon('cross')
                    ->type(Color::DANGER())->method('openTelegram')->parameters(['id' => $model->id]);
            }),
            TD::make('telegram_message_id', 'Telegram ID'),
            TD::make('created_at', 'Киритилган сана')
                ->render(function ($model) {
                    return $model->created_at->toDateTimeString();
                })->defaultHidden(),
            TD::make('updated_at', 'Ўзгартирилган сана')
                ->render(function ($model) {
                    return $model->updated_at->toDateTimeString();
                })->defaultHidden(),
            TD::make('')->render(function ($model){
                return Group::make([
                    ModalToggle::make('')
                        ->icon('eye')
                        ->modal('asyncGetProductModal')
                        ->method('saveProductInfo')
                        ->modalTitle($model->name)
                        ->asyncParameters([
                            'id' => $model->id,
                        ]),
                    Button::make('')
                        ->icon('trash')
                        ->parameters([
                            'id' => $model->id
                        ])
                        ->method('deleteProduct')
                        ->confirm('Махсулотни ўчирмоқчимисиз?'),
                ]);
            })->cantHide(),
        ];
    }
}

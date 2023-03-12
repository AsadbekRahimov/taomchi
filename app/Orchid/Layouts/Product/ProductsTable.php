<?php

namespace App\Orchid\Layouts\Product;

use App\Services\CacheService;

use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Select;
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
            TD::make('one_price', 'Нарх'),
            TD::make('for_telegram', 'Telegram')->render(function ($model) {
                return $model->for_telegram ? Button::make()->icon('check')
                    ->type(Color::SUCCESS())->disabled() : Button::make()->icon('cross')
                    ->type(Color::DANGER())->disabled();
            }),
            TD::make('telegram_message_id', 'Телеграм хабар ID'),
            TD::make('created_at', 'Киритилган сана')
                ->render(function ($model) {
                    return $model->created_at->toDateTimeString();
                })->defaultHidden(),
            TD::make('updated_at', 'Ўзгартирилган сана')
                ->render(function ($model) {
                    return $model->updated_at->toDateTimeString();
                })->defaultHidden(),
            TD::make('')->render(function ($model){
                return ModalToggle::make('')
                    ->icon('eye')
                    ->modal('asyncGetProductModal')
                    ->modalTitle($model->name)
                    ->asyncParameters([
                        'id' => $model->id,
                    ]);
            })->cantHide(),
        ];
    }
}

<?php

namespace App\Orchid\Layouts\Product;

use App\Services\CacheService;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class ProductInfo extends Rows
{
    /**
     * Used to create the title of a group of form elements.
     *
     * @var string|null
     */
    protected $title;

    /**
     * Get the fields elements to be displayed.
     *
     * @return Field[]
     */
    protected function fields(): iterable
    {
        $places = CacheService::getPlaces();
        $max_count = $places->count();

        return [

            Group::make([
                Input::make('name')->title('Номи')->required(),
                Select::make('category_id')->title('Махсулот тури')
                    ->options(CacheService::getProductCategories()),
            ]),

            Group::make([
                Select::make('measure_id')->title('Ўлчов бирлиги')
                    ->fromModel(\App\Models\Measure::class, 'name')->required(),
                Input::make('telegram_message_id')->title('Telegram ID')->type('number')
            ]),

            Matrix::make('prices')
                ->columns([
                    'ID' => 'id',
                    'Худуд' => 'place_id',
                    'Нархи' => 'price',
                ])->fields([
                    'id' => Input::make('id')->type('number')->hidden(),
                    'place_id' => Select::make('place_id')->options($places)->disabled(),
                    'price' => Input::make('price')->type('number')->required(),
                ])->removableRows(false)->maxRows($max_count),
        ];
    }
}

<?php

namespace App\Orchid\Layouts\TelegramOrder;

use App\Models\Payment;
use App\Services\CacheService;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class addUserModal extends Rows
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
        return [
            Group::make([
                Input::make('name')->title('Исм')->required(),
                Input::make('address')->title('Манзили')->required(),
                Select::make('place_id')->title('Худуди')->options(CacheService::getPlaces())->required(),
            ]),
            Group::make([
                Input::make('phone')->title('Телефон рақами 1')->mask('(99) 999-99-99')->required(),
                Input::make('telephone')->title('Телефон рақами 2')->mask('(99) 999-99-99'),
            ]),
        ];
    }
}

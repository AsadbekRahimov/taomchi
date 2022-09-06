<?php

namespace App\Orchid\Layouts\Stock;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class QuantityEditLayout extends Rows
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
                CheckBox::make('box')->title('Қадоқ')->sendTrueOrFalse()->value('true'),
                Input::make('quantity')->title('Моқдори')->type('number')->required(),
            ]),
        ];
    }
}

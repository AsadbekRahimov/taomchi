<?php

namespace App\Orchid\Layouts\Main;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class ExpenceModal extends Rows
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
            Input::make('price')->title('Миқдори')->type('number')->required(),
            TextArea::make('description')->rows(5)->title('Таснифи')->type('text')->required(),
        ];
    }
}

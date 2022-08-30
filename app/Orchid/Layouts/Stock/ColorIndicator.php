<?php

namespace App\Orchid\Layouts\Stock;

use Orchid\Screen\Actions\Link;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Color;

class ColorIndicator extends Rows
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
                Link::make()->type(Color::SUCCESS())->title('Maxsulot mavjud')->vertical(),
                Link::make()->type(Color::WARNING())->title('Kam qolgan')->vertical(),
                Link::make()->type(Color::DANGER())->title('Tugagan')->vertical(),
            ]),
        ];
    }
}

<?php

namespace App\Orchid\Layouts\TelegramOrder;

use App\Models\Payment;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class partPaymentModal extends Rows
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
                Input::make('price')->title('Тўлов суммаси')->type('number')->required(),
                Select::make('type')->title('Тўлов тури')->options(Payment::TYPE),
            ]),
        ];
    }
}

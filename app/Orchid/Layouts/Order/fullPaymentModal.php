<?php

namespace App\Orchid\Layouts\Order;

use App\Models\Payment;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class fullPaymentModal extends Rows
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
            Select::make('type')->title('To\'lov turi')->options(Payment::TYPE),
        ];
    }
}

<?php

namespace App\Orchid\Layouts;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ProductListener extends Listener
{
    /**
     * List of field names for which values will be listened.
     *
     * @var string[]
     */
    protected $targets = [
        'product'
    ];

    /**
     * What screen method should be called
     * as a source for an asynchronous request.
     *
     * The name of the method must
     * begin with the prefix "async"
     *
     * @var string
     */
    protected $asyncMethod = 'asyncProducts';

    /**
     * @return Layout[]
     */
    protected function layouts(): iterable
    {
        return [
            Layout::rows([
                Input::make('product')->title('Махсулот номини киритинг')->horizontal(),
                Matrix::make('products')
                    ->columns([
                        '' => 'id',
                        'Махсулот' => 'name',
                    ])->fields([
                        'id' => Input::make('id')->type('number')->hidden(),
                        'name' => Input::make('name')->disabled(),
                    ])->canSee($this->query->has('products')),
            ]),
        ];
    }
}

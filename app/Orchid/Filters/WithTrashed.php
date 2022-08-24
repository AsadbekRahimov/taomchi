<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\CheckBox;

class WithTrashed extends Filter
{
    /**
     * @var array
     */
    public $parameters = [
        'withTrashed',
    ];

    /**
     * @return string
     */
    public function name(): string
    {
        return 'O`chirilganlar';
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function run(Builder $builder): Builder
    {
        return $builder->onlyTrashed();
    }

    /**
     * @return Field[]
     */
    public function display(): array
    {
        return [
            CheckBox::make('withTrashed')
                ->placeholder(__('O`chirilganlarni ko`rsatish')),
        ];
    }
}

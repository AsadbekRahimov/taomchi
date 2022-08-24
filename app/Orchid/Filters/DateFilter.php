<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\DateTimer;

class DateFilter extends Filter
{
    /**
     * The displayable name of the filter.
     *
     * @return string
     */
    public function name(): string
    {
        return 'Belgilangan vaqt oraligi';
    }

    /**
     * The array of matched parameters.
     *
     * @return array|null
     */
    public function parameters(): ?array
    {
        return [
            'begin',
            'end',
        ];
    }

    /**
     * Apply to a given Eloquent query builder.
     *
     * @param Builder $builder
     *
     * @return Builder
     */
    public function run(Builder $builder): Builder
    {
        return $builder->whereBetween('created_at', [
            $this->request->get('begin') . ' 00:00:00',
            $this->request->get('end') . ' 23:59:59',
        ]);
    }

    /**
     * Get the display fields.
     *
     * @return Field[]
     */
    public function display(): iterable
    {
        return [
            DateTimer::make('begin')->required()->title('Boshlanish vaqti'),
            DateTimer::make('end')->required()->title('Tugash vaqti'),
        ];
    }
}

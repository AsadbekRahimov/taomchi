<?php

namespace App\Orchid\Layouts\FilterSelections;

use App\Orchid\Filters\DateFilter;
use Orchid\Filters\Filter;
use Orchid\Screen\Layouts\Selection;

class StatisticSelection extends Selection
{
    /**
     * @return Filter[]
     */
    public function filters(): iterable
    {
        return [
            DateFilter::class,
        ];
    }
}

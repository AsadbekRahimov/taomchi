<?php

namespace App\Orchid\Layouts\FilterSelections;

use App\Orchid\Filters\QuantityFilter;
use Orchid\Filters\Filter;
use Orchid\Screen\Layouts\Selection;

class StockSelection extends Selection
{
    /**
     * @return Filter[]
     */
    public function filters(): iterable
    {
        return [
            QuantityFilter::class,
        ];
    }
}

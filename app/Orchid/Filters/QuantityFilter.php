<?php

namespace App\Orchid\Filters;

use App\Models\Stock;
use App\Orchid\Resources\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;

class QuantityFilter extends Filter
{
    /**
     * The displayable name of the filter.
     *
     * @return string
     */
    public function name(): string
    {
        return 'Қолдиқ миқдор';
    }

    /**
     * The array of matched parameters.
     *
     * @return array|null
     */
    public function parameters(): ?array
    {
        return [
            'type'
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
        $stocks = Stock::query()->with(['product'])->where('branch_id', Auth::user()->branch_id)->get();
        $ids = [];
        if ($this->request->get('type') == 'Махсулот мавжуд')
        {
            foreach ($stocks as $stock)
            {
                if ($stock->quantity > $stock->product->min)
                {
                    $ids[] = $stock->id;
                }
            }
            return  $builder->whereIn('id', $ids);
        } elseif ($this->request->get('type') == 'Кам миқдорда')
        {
            foreach ($stocks as $stock)
            {
                if ($stock->quantity < $stock->product->min && $stock->quantity > 0)
                {
                    $ids[] = $stock->id;
                }
            }
            return  $builder->whereIn('id', $ids);
        }

        return $builder->where('quantity', '<=', 0);
    }

    /**
     * Get the display fields.
     *
     * @return Field[]
     */
    public function display(): iterable
    {
        return [
            Select::make('type')->title('Қолдиқ миқдор')->options(Stock::TYPE)->required(),
        ];
    }
}

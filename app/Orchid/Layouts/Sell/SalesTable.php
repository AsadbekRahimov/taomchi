<?php

namespace App\Orchid\Layouts\Sell;

use App\Services\CacheService;
use App\Services\HelperService;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Support\Color;

class SalesTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'sales';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID'),
            TD::make('product_id', 'Махсулот')->render(function ($model){
                return $model->product->name;
            })->filter(Select::make('product_id')->options(CacheService::ProductsKeyValue())->empty('', '')),
            TD::make('quantity', 'Миқдори')->render(function ($model){
                return $model->quantity;
            }),
            TD::make('price', 'Сотилган нархи')->render(function ($model){
                return number_format($model->price);
            }),
            TD::make('customer_id', 'Мижоз')->render(function ($model) {
                if ($model->customer_id)
                    return Link::make($model->customer_name)->route('platform.customer_info', ['customer' => $model->customer_id]);
                else
                    return Button::make($model->customer_name)->type(Color::PRIMARY())->disabled();
            })->filter(Select::make('customer_id')->options(CacheService::getCustomers())->empty('', '')),
        ];
    }
}

<?php

namespace App\Orchid\Layouts\Sell;

use App\Services\HelperService;
use Illuminate\Support\Facades\Cache;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class CustomersListTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'customers';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('id', 'Исм')->render(function ($model) {
                return Link::make($model->name)->route('platform.sell_products', ['customer' => $model->id]);
            })->filter(Select::make('id')->options(Cache::get('customers'))->empty('', ''))->cantHide(),
            TD::make('phone', 'Телефон рақам')->render(function ($model) {
                return Link::make($model->phone)->href('tel:' . HelperService::telephone($model->phone));
            })->cantHide(),
            TD::make('address', 'Манзили'),
        ];
    }
}

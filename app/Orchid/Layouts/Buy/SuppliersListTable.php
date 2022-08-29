<?php

namespace App\Orchid\Layouts\Buy;

use App\Models\Supplier;
use App\Services\HelperService;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class SuppliersListTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'suppliers';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('name', 'Ismi')->render(function (Supplier $supplier) {
                return Link::make($supplier->name)->route('platform.buy_products', ['supplier' => $supplier->id]);
            })->cantHide(),
            TD::make('phone', 'Telefon raqami')->render(function (Supplier $supplier) {
                return Link::make($supplier->phone)->href('tel:' . HelperService::telephone($supplier->phone));
            })->cantHide(),
        ];
    }
}

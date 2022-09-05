<?php

namespace App\Orchid\Screens\Buy;

use App\Models\Supplier;
use App\Orchid\Layouts\Buy\SuppliersListTable;
use Orchid\Screen\Screen;

class SupplierListScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'suppliers' => Supplier::query()->orderByDesc('id')->paginate(15),
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Таминотчилар';
    }

    public function description(): ?string
    {
        return 'Омборга махулот етказиб берувчи тамионотчилар рўйҳати';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.stock.buy',
        ];
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            SuppliersListTable::class,
        ];
    }
}

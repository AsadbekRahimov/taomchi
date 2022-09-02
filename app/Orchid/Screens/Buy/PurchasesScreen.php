<?php

namespace App\Orchid\Screens\Buy;

use App\Models\Purchase;
use App\Orchid\Layouts\Buy\PurchasesTable;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Screen;

class PurchasesScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable
    {
        $branch_id = Auth::user()->branch_id?: 0;
        return [
            'purchases' => Purchase::query()->with(['supplier', 'product'])
                ->where('branch_id', $branch_id)->orderByDesc('id')->paginate(15),
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Sotib olingan maxsulotlar';
    }

    public function description(): ?string
    {
        return 'Omborga sotib olingan maxsulotlar';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.stock.purchases',
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
            PurchasesTable::class,
        ];
    }
}

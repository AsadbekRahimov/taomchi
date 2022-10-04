<?php

namespace App\Orchid\Screens\Supplier;

use App\Models\Supplier;
use Orchid\Screen\Screen;

class SupplierInfoScreen extends Screen
{
    public $supplier;
    /**
     * Query data.
     *
     * @return array
     */
    public function query(Supplier $supplier): iterable
    {
        $this->supplier = $supplier;
        return [

        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Таминотчи: ' . $this->supplier->name;
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
        return [];
    }
}

<?php

namespace App\Orchid\Screens\Supplier;

use App\Models\Duty;
use App\Models\Expence;
use App\Models\Purchase;
use App\Models\PurchaseParty;
use App\Models\Supplier;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;

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
            'statistic' => [
                'buy' => $this->getAllBuyAmount($supplier->id),
                'expences' => $this->getAllExpenceAmount($supplier->id),
                'debt' => $this->getAllDebtAmount($supplier->id),
            ],
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
        return [
            Link::make($this->supplier->phone)->icon('call-out')->type(Color::SUCCESS())->href('tel://' . $this->supplier->phone),
        ];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::metrics([
                'Сотиб олган' => 'statistic.buy',
                'Тўлаб берилган' => 'statistic.expences',
                'Қарз' => 'statistic.debt',
            ])
        ];
    }

    private function getAllBuyAmount($id)
    {
        $amount = 0;
        foreach(Purchase::select(['price', 'quantity'])->where('supplier_id', $id)->get()->toArray() as $item)
        {
            $amount += $item['quantity'] * $item['price'];
        }
        return number_format($amount);
    }

    private function getAllExpenceAmount($id)
    {
        $parties = PurchaseParty::query()->where('supplier_id', $id)->pluck('id')->toArray();
        return number_format(Expence::query()->whereIn('party_id', $parties)->sum('price'));
    }

    private function getAllDebtAmount($id)
    {
        return number_format(Duty::query()->where('supplier_id', $id)->sum('duty'));
    }
}

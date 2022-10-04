<?php

namespace App\Orchid\Screens\Supplier;

use App\Models\Duty;
use App\Models\Expence;
use App\Models\Purchase;
use App\Models\PurchaseParty;
use App\Models\Supplier;
use App\Orchid\Layouts\Expences\ExpenceListTable;
use App\Orchid\Layouts\Payment\PartyList;
use App\Orchid\Layouts\Payment\PaymentListTable;
use App\Orchid\Layouts\Sell\SalePartyTable;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Modal;
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

            'expences' => Expence::query()->with(['party.supplier'])->whereHas('party', function (Builder $query) use ($supplier) {
                $query->where('supplier_id', $supplier->id);
            })->orderByDesc('id')->paginate(15),
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
            ]),

            Layout::tabs([
                'Чиқимлар' => ExpenceListTable::class,
                //'Сотиб олинган партиялар' => SalePartyTable::class,
            ]),

            Layout::modal('asyncGetPartyModal', PartyList::class)
                ->async('asyncGetParty')->size(Modal::SIZE_LG)
                ->withoutApplyButton(true)->closeButton('Ёпиш'),
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

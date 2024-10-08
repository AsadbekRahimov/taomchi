<?php

namespace App\Orchid\Screens\Customer;

use App\Models\Customer;
use App\Models\Duty;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\SalesParty;
use App\Orchid\Layouts\Payment\PartyList;
use App\Orchid\Layouts\Payment\PaymentListTable;
use App\Orchid\Layouts\Sell\SalePartyTable;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;

class CustomerInfoScreen extends Screen
{
    public $customer;
    /**
     * Query data.
     *
     * @return array
     */
    public function query(Customer $customer): iterable
    {
        $this->customer = $customer;
        return [
            'statistic' => [
               'sell' => $this->getAllSellAmount($customer->id),
               'payment' => $this->getAllPaymentAmount($customer->id),
               'debt' => $this->getAllDebtAmount($customer->id),
            ],

            'payments' => Payment::query()->where('customer_id', $customer->id)->filters()->with(['customer', 'user'])->orderByDesc('id')->paginate(15),
            'parties' => SalesParty::query()->filters()->where('customer_id', $customer->id)->with(['customer', 'user', 'sales', 'payments', 'duties'])->orderByDesc('id')->paginate(15),
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Мижоз: ' . $this->customer->name;
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make($this->customer->phone)->icon('call-out')->type(Color::SUCCESS())->href('tel://' . $this->customer->phone),
            Link::make($this->customer->telephone)->icon('call-out')->type(Color::INFO())->href('tel://' . $this->customer->telephone)->canSee(!is_null($this->customer->telephone)),
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
                'Сотиб олган' => 'statistic.sell',
                'Тўлов' => 'statistic.payment',
                'Қарз' => 'statistic.debt',
            ]),

            Layout::tabs([
                'Тўловлар' => PaymentListTable::class,
                'Сотилган партиялар' => SalePartyTable::class,
            ]),

            Layout::modal('asyncGetPartyModal', PartyList::class)
                ->async('asyncGetParty')->size(Modal::SIZE_LG)
                ->withoutApplyButton(true)->closeButton('Ёпиш'),
        ];
    }

    private function getAllSellAmount($id)
    {
        $amount = 0;
        foreach(Sale::select(['price', 'quantity'])->where('customer_id', $id)->get()->toArray() as $item)
        {
            $amount += $item['quantity'] * $item['price'];
        }
        $amount -= SalesParty::query()->where('customer_id', $id)->sum('discount');
        return number_format($amount);
    }

    private function getAllPaymentAmount($id)
    {
        return number_format(Payment::query()->where('customer_id', $id)->sum('price'));
    }

    private function getAllDebtAmount($id)
    {
        return number_format(Duty::query()->where('customer_id', $id)->sum('duty'));
    }

    public function asyncGetParty(SalesParty $salesParty)
    {
        return [
            'sales' => $salesParty->sales,
        ];
    }
}

<?php

namespace App\Orchid\Screens\Payment;

use App\Models\Payment;
use App\Models\PurchaseParty;
use App\Orchid\Layouts\Payment\PartyList;
use App\Orchid\Layouts\Payment\PaymentListTable;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class PaymentListScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable
    {
        $branch_id = Auth::user()->branch_id ?: 0;
        return [
            'payments' => Payment::query()->with(['customer'])
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
        return 'To\'lovlar';
    }

    public function description(): ?string
    {
        return 'Qabul qilingan barcha to\'lovlar';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.stock.payments',
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
            PaymentListTable::class,
            Layout::modal('asyncGetPartyModal', PartyList::class)
                ->async('asyncGetParty')->size(Modal::SIZE_LG)
                ->withoutApplyButton(true)->closeButton('Yopish'),
        ];
    }

    public function asyncGetParty(Payment $payment)
    {
        return [
            'sales' => $payment->sales,
        ];
    }
}

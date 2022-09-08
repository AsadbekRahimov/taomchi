<?php

namespace App\Orchid\Screens\Payment;

use App\Models\Payment;
use App\Models\PurchaseParty;
use App\Orchid\Layouts\Payment\PartyList;
use App\Orchid\Layouts\Payment\PaymentListTable;
use App\Orchid\Layouts\Report\ByDateRangeModal;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\ModalToggle;
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
        return 'Тўловлар';
    }

    public function description(): ?string
    {
        return 'Қабул қилинган барча тўловлар';
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
        return [
            ModalToggle::make('')
                ->icon('save-alt')
                ->method('report')
                ->modal('reportModal'),
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
            PaymentListTable::class,
            Layout::modal('asyncGetPartyModal', PartyList::class)
                ->async('asyncGetParty')->size(Modal::SIZE_LG)
                ->withoutApplyButton(true)->closeButton('Ёпиш'),
            Layout::modal('reportModal', ByDateRangeModal::class)
                ->applyButton('Юклаш')->closeButton('Ёпиш')->title('Сотилган махсулотлар')->rawClick(),
        ];
    }

    public function asyncGetParty(Payment $payment)
    {
        return [
            'sales' => $payment->sales,
        ];
    }

    public function report(Request $request)
    {
        return ReportService::paymentReport($request->date);
    }
}

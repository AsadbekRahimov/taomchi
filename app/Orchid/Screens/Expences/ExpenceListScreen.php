<?php

namespace App\Orchid\Screens\Expences;

use App\Models\Expence;
use App\Orchid\Layouts\Expences\OtherExpenceListTable;
use App\Orchid\Layouts\Expences\PartyList;
use App\Orchid\Layouts\Report\ByDateRangeModal;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class ExpenceListScreen extends Screen
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
            'expences' => Expence::query()
                ->where('branch_id', $branch_id)
                ->orderByDesc('id')->paginate(15),
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Чиқимлар';
    }

    public function description(): ?string
    {
        return 'Aмалга оширилган чиқимлар рўйҳати';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.stock.expences',
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
                ->modal('reportModal')
                ->modalTitle('Чиқимлар'),
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
            OtherExpenceListTable::class,
            Layout::modal('asyncGetPartyModal', PartyList::class)
                ->async('asyncGetParty')->size(Modal::SIZE_LG)
                ->withoutApplyButton(true)->closeButton('Ёпиш'),
            Layout::modal('reportModal', ByDateRangeModal::class)
                ->applyButton('Юклаш')->closeButton('Ёпиш')->title('Сотилган махсулотлар')->rawClick(),
        ];
    }

    public function asyncGetParty(PurchaseParty $purchaseParty)
    {
        return [
            'purchases' => $purchaseParty->purchases,
        ];
    }

    public function report(Request $request)
    {
        return ReportService::expenceReport($request->date);
    }
}

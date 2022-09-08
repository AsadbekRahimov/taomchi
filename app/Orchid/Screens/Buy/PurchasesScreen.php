<?php

namespace App\Orchid\Screens\Buy;

use App\Models\Purchase;
use App\Orchid\Layouts\Buy\PurchasesTable;
use App\Orchid\Layouts\Report\ByDateRangeModal;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

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
        return 'Сотиб олинган махсулотлар';
    }

    public function description(): ?string
    {
        return 'Омборга сотиб олинган махсулотлар';
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
        return [
            ModalToggle::make('')
                ->icon('save-alt')
                ->method('report')
                ->modal('reportModal')
                ->modalTitle('Сотиб олинган махсулотлар'),
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
            PurchasesTable::class,
            Layout::modal('reportModal', ByDateRangeModal::class)
                ->applyButton('Юклаш')->closeButton('Ёпиш')->title('Сотилган махсулотлар')->rawClick(),
        ];
    }

    public function report(Request $request)
    {
        return ReportService::buyReport($request->date);
    }
}

<?php

namespace App\Orchid\Screens\Sell;

use App\Models\Sale;
use App\Orchid\Layouts\Report\ByDateRangeModal;
use App\Orchid\Layouts\Sell\SalesTable;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class SalesScreen extends Screen
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
            'sales' => Sale::query()->filters()->with(['customer', 'telegram', 'product'])
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
        return 'Сотилган махсулотлар';
    }

    public function description(): ?string
    {
        return 'Омбордан сотилган махсулотлар';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.stock.sales',
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
                ->modalTitle('Сотилган махсулотлар'),
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
            SalesTable::class,
            Layout::modal('reportModal', ByDateRangeModal::class)
                ->applyButton('Юклаш')->closeButton('Ёпиш')->title('Сотилган махсулотлар')->rawClick(),
        ];
    }

    public function report(Request $request)
    {
        return ReportService::sellReport($request->date);
    }
}

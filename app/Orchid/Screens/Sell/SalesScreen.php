<?php

namespace App\Orchid\Screens\Sell;

use App\Models\Sale;
use App\Orchid\Layouts\Report\ByDateRangeModal;
use App\Orchid\Layouts\Sell\SalesTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
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
            'sales' => Sale::query()->with(['customer', 'product'])
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
            SalesTable::class,
            Layout::modal('reportModal', ByDateRangeModal::class)->applyButton('Юклаш')->closeButton('Ёпиш')->title('Сотилган махсулотлар'),
        ];
    }

    public function report(Request $request)
    {
        $begin = $request->date['start'] . ' 00:00:00';
        $end = $request->date['end'] . ' 23:59:59';

        $result = DB::select('SELECT C.name, P.name, S.quantity, S.price FROM sales S JOIN customers C ON C.id = S.customer_id JOIN products P ON S.product_id = P.id');
        dd($result);
    }
}

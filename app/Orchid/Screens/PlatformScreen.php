<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\Duty;
use App\Models\Expence;
use App\Models\Payment;
use App\Models\Sale;
use App\Orchid\Layouts\Charts\CourierChart;
use App\Orchid\Layouts\Charts\DutyChart;
use App\Orchid\Layouts\Charts\PaymentChart;
use App\Orchid\Layouts\Charts\SellChart;
use App\Orchid\Layouts\FilterSelections\StatisticSelection;
use App\Orchid\Layouts\Main\ExpenceModal;
use App\Services\ChartService;
use App\Services\HelperService;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class PlatformScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public $sell_price;
    public $real_price;
    public $expenses;
    public $day_profit;
    public $for_suppliers;

    public function query(): iterable
    {
        $this->sell_price = HelperService::statTotalPrice(Sale::query()->whereDate('updated_at', Carbon::today())->get(), 'price');
        $this->real_price = HelperService::statTotalPrice(Sale::query()->with('product')->whereDate('updated_at', Carbon::today())->get(), 'real_price');
        $this->expenses = (int)Expence::query()->whereDate('updated_at', Carbon::today())->whereNull('party_id')->sum('price');
        $this->day_profit = $this->sell_price - $this->real_price - $this->expenses;
        $this->for_suppliers = (int)Expence::query()->whereDate('updated_at', Carbon::today())->whereNotNull('party_id')->sum('price');
        if (request()->has('date')) {
            $date = \request()->get('date');
            $begin = $date['start'] . ' 00:00:00';
            $end = $date['end'] . ' 23:59:59';
        } else {
            $begin = date('Y-m-d') . ' 00:00:00';
            $end = date('Y-m-d') . ' 23:59:59';
        }

        return [
            'statistic' => [
                'all' => [
                    'products' => Cache::get('products')->count(),
                    'customers' => Cache::get('customers')->count(),
                    'suppliers' => Cache::get('suppliers')->count(),
                ],
                'day' => [
                    'sell_price' => number_format($this->sell_price),
                    'real_price' => number_format($this->real_price),
                    'payments' => number_format((int)Payment::query()->whereDate('updated_at', Carbon::today())->sum('price')),
                    'duties' => number_format((int)Duty::query()->whereDate('updated_at', Carbon::today())->whereNotNull('customer_id')->sum('duty')),
                    'supplier_payments' => number_format((int)Expence::query()->whereDate('updated_at', Carbon::today())
                        ->whereNotNull('party_id')->sum('price')),
                    'expenses' => number_format($this->expenses),
                ],
            ],
            'payments' => [ChartService::paymentChart($begin, $end)],
            'duties' => [ChartService::dutiesChart()],
            'sell_products' => [ChartService::SellChart($begin, $end)],
            'courier' => [ChartService::CourierChart($begin, $end)],
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'E-do\'kon - WMS';
    }

    /**
     * Display header description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Елетрон обморхона автоматлаштириш тизими';
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make( 'Бугунги фойда: ' . number_format($this->day_profit))->type(Color::SUCCESS())->canSee($this->day_profit > 0),
            Link::make( 'Бугунги зарар: ' . number_format(-1 *$this->day_profit) . ' сўм')->type(Color::DANGER())->canSee($this->day_profit < 0),
            Link::make( 'Таминотчиларга тўлов: ' . number_format($this->for_suppliers))->type(Color::WARNING())->canSee($this->for_suppliers > 0),
            ModalToggle::make('Чиқим')
                ->icon('calculator')
                ->modal('addExpenceModal')
                ->modalTitle('Чиқим киритиш')
                ->method('addExpence')
                ->canSee(Auth::user()->hasAccess('platform.stock.expences')),
            Button::make('')
                ->icon('save-alt')
                ->method('report')->rawClick(),
        ];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::metrics([
                'Махсулотлар' => 'statistic.all.products',
                'Мижозлар' => 'statistic.all.customers',
                'Таминотчилар' => 'statistic.all.suppliers',
            ]),
            Layout::metrics([
                'Сотилган нарх' => 'statistic.day.sell_price',
                'Тан нархи' => 'statistic.day.real_price',
                'Чиқимлар' => 'statistic.day.expenses',
            ])->title('Бугунги савдо'),
            Layout::metrics([
                'Тўловлар' => 'statistic.day.payments',
                'Қарздорлик' => 'statistic.day.duties',
                'Махсулот учун тўловлар' => 'statistic.day.supplier_payments',
            ]),
            StatisticSelection::class,
            Layout::tabs([
                'Тўлов' => PaymentChart::class,
                'Қарздорлик' => DutyChart::class,
                'Сотилган махсулот' => SellChart::class,
                'Сотувчи' => CourierChart::class,
            ]),
            Layout::modal('addExpenceModal', [ExpenceModal::class])
                ->applyButton('Киритиш')->closeButton('Ёпиш'),
        ];
    }

    public function addExpence(Request $request)
    {
        Expence::otherExpence($request->price, $request->description);
        Alert::success('Чиқим муаффақиятли киритилди');
    }

    public function report(Request $request)
    {
        $date = [
           'start' => date('Y-m-d'),
           'end' => date('Y-m-d')
        ];

        return ReportService::allReport($date, 'download');
    }
}

<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\Customer;
use App\Models\Duty;
use App\Models\Expence;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Supplier;
use App\Orchid\Layouts\Charts\CourierChart;
use App\Orchid\Layouts\Charts\DutyChart;
use App\Orchid\Layouts\Charts\PaymentChart;
use App\Orchid\Layouts\Charts\SellChart;
use App\Orchid\Layouts\Main\ExpenceModal;
use App\Services\ChartService;
use App\Services\HelperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
    public function query(): iterable
    {
        $this->sell_price = HelperService::statTotalPrice(Sale::query()->whereDay('updated_at', date('d'))->pluck('price', 'quantity')->toArray());
        $this->real_price = HelperService::statTotalPrice(Sale::query()->with('product')->whereDay('updated_at', date('d'))->get()->pluck('product.real_price', 'quantity')->toArray());
        $this->expenses = (int)Expence::query()->whereDay('updated_at', date('d'))->whereNull('party_id')->sum('price');
        $this->day_profit = $this->sell_price - $this->real_price - $this->expenses;
        return [
            'statistic' => [
                'all' => [
                    'products' => Cache::rememberForever('products', function () {
                        return \App\Models\Product::query()->pluck('name', 'id');
                    })->count(),
                    'customers' => Cache::rememberForever('customers', function () {
                        return \App\Models\Customer::query()->pluck('name', 'id');
                    })->count(),
                    'suppliers' => Cache::rememberForever('suppliers', function () {
                        return \App\Models\Supplier::query()->pluck('name', 'id');
                    })->count(),
                ],
                'day' => [
                    'sell_price' => number_format($this->sell_price),
                    'real_price' => number_format($this->real_price),
                    'payments' => number_format((int)Payment::query()->whereDay('updated_at', date('d'))->sum('price')),
                    'duties' => number_format((int)Duty::query()->whereDay('updated_at', date('d'))->sum('duty')),
                    'supplier_payments' => number_format((int)Expence::query()->whereDay('updated_at', date('d'))
                        ->whereNotNull('party_id')->sum('price')),
                    'expenses' => number_format($this->expenses),
                ],
            ],
            'payments' => [ (request()->has('begin')) ? ChartService::paymentChart($begin, $end) : ChartService::paymentChart()],
            'duties' => [ (request()->has('begin')) ? ChartService::dutiesChart($begin, $end) : ChartService::dutiesChart()],
            'sell_products' => [ (request()->has('begin')) ? ChartService::SellChart($begin, $end) : ChartService::SellChart()],
            'courier' => [ (request()->has('begin')) ? ChartService::CourierChart($begin, $end) : ChartService::CourierChart()],
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
            Link::make( 'Бугунги фойда: ' . number_format($this->day_profit))->type(Color::SUCCESS())->canSee($this->day_profit >= 0),
            Link::make( 'Бугунги зарар: ' . number_format(-1 *$this->day_profit) . ' сўм')->type(Color::DANGER())->canSee($this->day_profit < 0),
            ModalToggle::make('Чиқим')
                ->icon('calculator')
                ->modal('addExpenceModal')
                ->modalTitle('Чиқим киритиш')
                ->method('addExpence')
                ->canSee(Auth::user()->hasAccess('platform.stock.expences')),
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
}

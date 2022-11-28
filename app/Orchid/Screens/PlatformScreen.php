<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\Card;
use App\Models\Customer;
use App\Models\Duty;
use App\Models\Expence;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Place;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SalesParty;
use App\Orchid\Layouts\Charts\CourierChart;
use App\Orchid\Layouts\Charts\DutyChart;
use App\Orchid\Layouts\Charts\PaymentChart;
use App\Orchid\Layouts\Charts\SellChart;
use App\Orchid\Layouts\CustomerListener;
use App\Orchid\Layouts\FilterSelections\StatisticSelection;
use App\Orchid\Layouts\Main\ExpenceModal;
use App\Services\ChartService;
use App\Services\HelperService;
use App\Services\ReportService;
use App\Services\SendMessageService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Fields\Select;
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
    public $discounts;
    public $expenses;

    public $superadmin;
    public $call_center;
    public $courier;

    public function query(): iterable
    {
        $this->superadmin = Auth::user()->inRole('super_admin') ? 1 : 0;
        $this->call_center = Auth::user()->inRole('call_center') ? 1 : 0;
        $this->courier = Auth::user()->inRole('courier') ? 1 : 0;

        if ($this->superadmin)
        {
            $this->sell_price = HelperService::statTotalPrice(Sale::query()->whereDate('updated_at', Carbon::today())->get());
            $this->expenses = (int)Expence::query()->whereDate('updated_at', Carbon::today())->sum('price');
            $this->discounts = (int)SalesParty::query()->whereDate('updated_at', Carbon::today())->sum('discount');

            if (request()->has('date')) {
                $date = \request()->get('date');
                $begin = $date['start'] . ' 00:00:00';
                $end = $date['end'] . ' 23:59:59';
            } else {
                $begin = date('Y-m-d') . ' 00:00:00';
                $end = date('Y-m-d') . ' 23:59:59';
            }

            if (!Cache::has('places')) {
                Cache::rememberForever('places', function () {
                    return Place::query()->pluck('name', 'id');
                });
            }

            return [
                'statistic' => [
                    'all' => [
                        'products' => (!Cache::has('products')) ? Cache::rememberForever('products', function () {
                            return \App\Models\Product::query()->pluck('name', 'id');
                        })->count() : Cache::get('products')->count(),
                        'customers' => (!Cache::has('customers')) ? Cache::rememberForever('customers', function () {
                            return \App\Models\Customer::query()->pluck('name', 'id');
                        })->count() : Cache::get('customers')->count(),
                    ],
                    'day' => [
                        'sell_price' => number_format($this->sell_price),
                        'payments' => number_format((int)Payment::query()->whereDate('updated_at', Carbon::today())->sum('price')),
                        'duties' => number_format((int)Duty::query()->whereDate('updated_at', Carbon::today())->whereNotNull('customer_id')->sum('duty')),
                        'expenses' => number_format($this->expenses),
                        'discounts' => number_format($this->discounts),
                    ],
                ],
                'payments' => [ChartService::paymentChart($begin, $end)],
                'duties' => [ChartService::dutiesChart()],
                'sell_products' => [ChartService::SellChart($begin, $end)],
                'courier' => [ChartService::CourierChart($begin, $end)],
            ];
        } elseif ($this->call_center) {
            return [];
        } elseif ($this->courier) {
            return [];
        } else {
            return [];
        }
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Taomchi - CRM';
    }

    /**
     * Display header description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return  ' Злектрон савдони автоматлаштириш тизими';
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
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
        if ($this->superadmin) {
            return [
                Layout::metrics([
                    'Махсулотлар' => 'statistic.all.products',
                    'Мижозлар' => 'statistic.all.customers',
                ]),
                Layout::metrics([
                    'Сотилган махсулотлар нархи' => 'statistic.day.sell_price',
                    'Чегирмалар' => 'statistic.day.discounts',
                    'Тўловлар' => 'statistic.day.payments',
                ])->title('Бугунги савдо'),
                Layout::metrics([
                    'Чиқимлар' => 'statistic.day.expenses',
                    'Қарздорлик' => 'statistic.day.duties',
                ]),
                StatisticSelection::class,
                Layout::tabs([
                    'Тўлов' => PaymentChart::class,
                    'Қарздорлик' => DutyChart::class,
                    'Сотилган махсулот' => SellChart::class,
                    'Курерлар' => CourierChart::class,
                ]),
                Layout::modal('addExpenceModal', [ExpenceModal::class])
                    ->applyButton('Киритиш')->closeButton('Ёпиш'),
            ];
        }elseif($this->call_center) {
            return [
                Layout::rows([
                    Select::make('customer_id')->title('Мижоз')->options(Cache::get('customers')),
                    Matrix::make('products')
                        ->columns([
                            'Махсулот' => 'id',
                            'Сони' => 'count',
                            'Нархи' => 'price'
                        ])->fields([
                            'id' => Select::make('id')->options(Cache::get('products'))->required(),
                            'count' => Input::make('count')->type('number')->required(),
                            'price' => Select::make('price')
                                ->options(['one' => 'Чакана нарх', 'discount' => 'Чегирма нарх'])->required()
                        ]),
                    Button::make('Сақлаш')->method('saveOrder')->type(Color::INFO()),
                ]),
            ];
        }elseif($this->courier) {
            return [];
        }else {
            return [];
        }
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

    public function saveOrder(Request $request)
    {
        //dd($request->all());
        if (!$request->has('products')) {
            Alert::error('Камида 1 та махсулот танланиши керак!');
        } else {
            $order = Order::createOrder($request->customer_id);
            $cards = Card::createOrderCards($request);
            SendMessageService::sendOrder($order, $cards);
            Alert::success('Буюртма муаффақиятли яратилди');
            return redirect()->route('platform.orders');
        }
    }
}

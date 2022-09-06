<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\Duty;
use App\Models\Expence;
use App\Models\Payment;
use App\Models\Sale;
use App\Orchid\Layouts\Main\ExpenceModal;
use App\Services\HelperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
               'sell_price' => number_format($this->sell_price),
               'real_price' => number_format($this->real_price),
               'payments' => number_format((int)Payment::query()->whereDay('updated_at', date('d'))->sum('price')),
               'duties' => number_format((int)Duty::query()->whereDay('updated_at', date('d'))->sum('duty')),
               'supplier_payments' => number_format((int)Expence::query()->whereDay('updated_at', date('d'))->whereNotNull('party_id')
                    ->sum('price')),
                'expenses' => number_format($this->expenses),
            ],
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
                'Сотилган нарх' => 'statistic.sell_price',
                'Тан нархи' => 'statistic.real_price',
                'Чиқимлар' => 'statistic.expenses',

            ]),
            Layout::metrics([
                'Тўловлар' => 'statistic.payments',
                'Қарзорлик' => 'statistic.duties',
                'Махсулот учун тўловлар' => 'statistic.supplier_payments',
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

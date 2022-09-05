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
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class PlatformScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'statistic' => [
               'sell_price' => number_format(HelperService::statTotalPrice(Sale::query()->whereDay('updated_at', date('d'))->pluck('price', 'quantity')->toArray())),
               'real_price' => number_format(HelperService::statTotalPrice(Sale::query()->with('product')->whereDay('updated_at', date('d'))->get()->pluck('product.real_price', 'quantity')->toArray())),
               'payments' => number_format((int)Payment::query()->whereDay('updated_at', date('d'))->sum('price')),
               'duties' => number_format((int)Duty::query()->whereDay('updated_at', date('d'))->sum('duty')),
               'expences' => number_format((int)Expence::query()->whereDay('updated_at', date('d'))
                   ->sum('price')),
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
        return 'Eletron obmorxona avtomatlashtirish tizimi';
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Chiqim')
                ->icon('calculator')
                ->modal('addExpenceModal')
                ->modalTitle('Chiqim kiritish')
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
                'Sotilgan narx' => 'statistic.sell_price',
                'Tan narxi' => 'statistic.real_price',
                'To\'lovlar' => 'statistic.payments',
                'Qarzorlik' => 'statistic.duties',
                'Chiqimlar' => 'statistic.expences',
            ]),
            Layout::modal('addExpenceModal', [ExpenceModal::class])
                ->applyButton('Kiritish')->closeButton('Yopish'),
        ];
    }

    public function addExpence(Request $request)
    {
        Expence::otherExpence($request->price, $request->description);
        Alert::success('Chiqim muaffaqiyatli kiritildi');
    }
}

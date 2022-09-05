<?php

namespace App\Orchid\Screens\Duties;

use App\Models\Duty;
use App\Models\Payment;
use App\Orchid\Layouts\Duties\CustomerDutiesTable;
use App\Orchid\Layouts\Duties\PartyList;
use App\Orchid\Layouts\Order\fullPaymentModal;
use App\Orchid\Layouts\Order\partPaymentModal;
use App\Services\HelperService;
use Illuminate\Http\Request;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class CustomerDutiesListScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'customer_duties' => Duty::query()->with(['customer'])
                ->whereNotNull('customer_id')->orderByDesc('id')->paginate(15),
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Мижозлар қарзи';
    }

    public function description(): ?string
    {
        return 'Омборхона мижозлариниг қарзлари';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.stock.customer_duties',
        ];
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            CustomerDutiesTable::class,
            Layout::modal('asyncGetPartyModal', PartyList::class)
                ->async('asyncGetParty')->size(Modal::SIZE_LG)
                ->withoutApplyButton(true)->closeButton('Ёпиш'),
            Layout::modal('fullPaymentModal', [fullPaymentModal::class])
                ->applyButton('To\'lash')->closeButton('Ёпиш'),
            Layout::modal('partPaymentModal', [partPaymentModal::class])
                ->applyButton('To\'lash')->closeButton('Ёпиш'),
        ];
    }

    public function asyncGetParty(Duty $duty)
    {
        $products = $duty->sales->sales;
        $total = HelperService::getTotalPrice($products);
        return [
            'products' => $products,
            'total_price' => $total,
            'duty' => $duty->duty,
        ];
    }

    public function fullPayment(Request $request)
    {
        $duty = Duty::find($request->id);
        Payment::addFullDutyPayment($duty, $request->type);
        $duty->delete();
        Alert::success('Қарз муаффақиятли тўланди');
    }

    public function partPayment(Request $request)
    {
        $duty = Duty::find($request->id);
        $price = (int) $request->price;
        if ($price >= $duty->duty)
        {
            Alert::error('Толанаётган пул миқдори қарз миқдоридан кам бўлиши керак!');
        } else {
            Payment::addPartDutyPayment($price, $duty, $request->type);
            $duty->duty -= $price;
            $duty->save();
            Alert::success('Қарзнинг бир миқдори муаффақиятли тўланди');
        }
    }
}

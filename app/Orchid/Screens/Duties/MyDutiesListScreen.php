<?php

namespace App\Orchid\Screens\Duties;

use App\Models\Duty;
use App\Models\Expence;
use App\Orchid\Layouts\Duties\MyDutiesTable;
use App\Orchid\Layouts\Duties\PartyList;
use App\Orchid\Layouts\Order\fullPaymentModal;
use App\Orchid\Layouts\Order\partPaymentModal;
use App\Services\HelperService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class MyDutiesListScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'my_duties' => Duty::query()->with(['supplier'])->orderByDesc('id')
                ->whereNotNull('supplier_id')->paginate(15),
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Таминотчилардан қарз';
    }

    public function description(): ?string
    {
        return 'Омборга таминотчиларидан олган махсулот учун толанмаган қарзлар';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.stock.my_duties',
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
            Button::make('')
                ->icon('save-alt')
                ->method('report')->rawClick(),
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
            MyDutiesTable::class,
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
        $products = $duty->purchases->purchases;
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
        Expence::addFullDutyPayment($duty);
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
            Expence::addPartDutyPayment($price, $duty);
            $duty->duty -= $price;
            $duty->save();
            Alert::success('Қарзнинг бир миқдори муаффақиятли тўланди');
        }
    }

    public function report()
    {
        return ReportService::dutiesReport('supplier');
    }
}

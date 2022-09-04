<?php

namespace App\Orchid\Screens\Buy;

use App\Models\PurchaseParty;
use App\Models\Sale;
use App\Orchid\Layouts\Buy\PartyList;
use App\Orchid\Layouts\Buy\PurchasePartyTable;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class PurchasesPartyScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable
    {
        $branch_id = Auth::user()->branch_id ?: 0;
        return [
            'parties' => PurchaseParty::query()
                ->with(['supplier', 'user', 'purchases', 'expences', 'duties'])
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
        return 'Sotib olingan partiyalar';
    }

    public function description(): ?string
    {
        return 'Omborga sotib olingan maxsulot partiyalari';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.stock.buy_parties',
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
            PurchasePartyTable::class,
            Layout::modal('asyncGetPartyModal', PartyList::class)
                ->async('asyncGetParty')->size(Modal::SIZE_LG)
                ->withoutApplyButton(true)->closeButton('Yopish'),
        ];
    }

    public function asyncGetParty(PurchaseParty $purchaseParty)
    {
        return [
           'purchases' => $purchaseParty->purchases,
        ];
    }
}

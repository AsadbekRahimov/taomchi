<?php

namespace App\Orchid\Screens\Sell;

use App\Models\SalesParty;
use App\Orchid\Layouts\Sell\PartyList;
use App\Orchid\Layouts\Sell\SalePartyTable;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class SalesPartyScreen extends Screen
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
            'parties' => SalesParty::query()->filters()->with(['customer', 'telegram', 'user', 'sales', 'payments', 'duties'])
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
        return 'Сотилган партиялар';
    }

    public function description(): ?string
    {
        return 'Омбордан сотилган махсулот партиялари';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.stock.sell_parties',
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
            SalePartyTable::class,
            Layout::modal('asyncGetPartyModal', PartyList::class)
                ->async('asyncGetParty')->size(Modal::SIZE_LG)
                ->withoutApplyButton(true)->closeButton('Ёпиш'),
        ];
    }

    public function asyncGetParty(SalesParty $salesParty)
    {
        return [
            'sales' => $salesParty->sales,
        ];
    }
}

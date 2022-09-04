<?php

namespace App\Orchid\Screens\Expences;

use App\Models\Expence;
use App\Orchid\Layouts\Expences\ExpenceListTable;
use App\Orchid\Layouts\Expences\OtherExpenceListTable;
use App\Orchid\Layouts\Expences\PartyList;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class ExpenceListScreen extends Screen
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
            'expences' => Expence::query()->with(['party.supplier'])
                ->where('branch_id', $branch_id)->whereNull('description')
                ->orderByDesc('id')->paginate(15),
            'other_expences' => Expence::query()->with(['party.supplier'])
                ->where('branch_id', $branch_id)->whereNull('party_id')
                ->orderByDesc('id')->paginate(15),
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Chiqimlar';
    }

    public function description(): ?string
    {
        return 'Amalga oshirilgan chiqimlar ro\'yhati';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.stock.expences',
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
            Layout::tabs([
                'Taminotchilarga to\'langan to\'lovlar' => ExpenceListTable::class,
                'Boshqa chiqimlar' => OtherExpenceListTable::class,
            ]),
            Layout::modal('asyncGetPartyModal', PartyList::class)
                ->async('asyncGetParty')->size(Modal::SIZE_LG)
                ->withoutApplyButton(true)->closeButton('Yopish'),
        ];
    }

    public function asyncGetParty(Expence $expence)
    {
        return [
            'purchases' => $expence->party->purchases,
        ];
    }
}

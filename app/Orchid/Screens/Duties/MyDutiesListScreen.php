<?php

namespace App\Orchid\Screens\Duties;

use App\Models\Duty;
use App\Orchid\Layouts\Duties\MyDutiesTable;
use App\Orchid\Layouts\Duties\PartyList;
use App\Services\HelperService;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
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
        return 'Taminotchilardan qarz';
    }

    public function description(): ?string
    {
        return 'Omborga taminotchilaridan olgan maxsulot uchun tolanmagan qarzlar';
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
            MyDutiesTable::class,
            Layout::modal('asyncGetPartyModal', PartyList::class)
                ->async('asyncGetParty')->size(Modal::SIZE_LG)
                ->withoutApplyButton(true)->closeButton('Yopish'),
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
}

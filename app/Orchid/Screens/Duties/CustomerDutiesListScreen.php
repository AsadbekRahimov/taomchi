<?php

namespace App\Orchid\Screens\Duties;

use App\Models\Duty;
use App\Orchid\Layouts\Duties\CustomerDutiesTable;
use App\Orchid\Layouts\Duties\PartyList;
use App\Services\HelperService;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
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
        return 'Mijozlar qarzi';
    }

    public function description(): ?string
    {
        return 'Omborxona mijozlarinig qarzlari';
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
                ->withoutApplyButton(true)->closeButton('Yopish'),
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
}

<?php

namespace App\Orchid\Screens\Order;


use App\Models\Customer;
use App\Models\TelegramOrder;
use App\Models\TelegramUser;
use App\Orchid\Layouts\TelegramOrder\addUserModal;
use App\Orchid\Layouts\TelegramOrder\fullPaymentModal;
use App\Orchid\Layouts\TelegramOrder\OrderListTable;
use App\Orchid\Layouts\TelegramOrder\partPaymentModal;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class TelegramOrderListScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'orders' => TelegramOrder::query()->with(['user.customer', 'products'])
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
        return 'Телеграмдан буюртмалар';
    }

    public function description(): ?string
    {
        return 'Телеграм ботга келиб тушган буюртмалар';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.stock.telegram-orders',
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
            OrderListTable::class,
            Layout::modal('addUserModal', [addUserModal::class])->async('asyncGetUser')
                ->applyButton('қўшиш')->closeButton('Ёпиш')->size(Modal::SIZE_LG),
            Layout::modal('fullPaymentModal', [fullPaymentModal::class])
                ->applyButton('Тўлаш')->closeButton('Ёпиш'),
            Layout::modal('partPaymentModal', [partPaymentModal::class])
                ->applyButton('Тўлаш')->closeButton('Ёпиш'),
        ];
    }

    public function asyncGetUser(TelegramUser $user)
    {
        return [
            'name' => $user->name,
            'phone' => $user->phone,
        ];
    }

    public function saveUser(TelegramUser $user, Request $request)
    {
        $customer = Customer::query()->create([
            'name' => $request->name,
            'phone' => $request->phone,
            'telephone' => $request->telephone,
            'address' => $request->address,
            'place_id' => $request->place_id
        ]);

        $user->update([
           'customer_id' => $customer->id
        ]);

        Cache::forget('customers');
        CacheService::getCustomers();
        Alert::success('Янги мижоз қўшилди');
    }

    public function acceptOrder(TelegramOrder $order)
    {
        $order->update([
            'state' => 'accepted_order',
        ]);

        Alert::success('Буюртма муффақиятли қабул қилинди.');
    }

    /*public function duty(Request $request)
    {
        $order = Order::query()->find($request->id);
        $party = SalesParty::createParty($request->customer_id, $order->discount);
        Duty::duty($party->id, $order);
        Sale::createSales($party->id, $request->id, $party->branch_id);
        $this->deleteCard($request);
        Alert::success('Махсулотлар муаффақиятли сотилди');
    }*/

    /*public function fullPayment(Request $request)
    {
        $order = Order::query()->find($request->id);
        $party = SalesParty::createParty($request->customer_id, $order->discount);
        Payment::addPayment($party->id, $order, $request->type);
        Sale::createSales($party->id, $request->id, $party->branch_id);
        $this->deleteCard($request);
        Alert::success('Махсулотлар муаффақиятли сотилди');
    }*/

    /*public function partPayment(Request $request)
    {
        $order = Order::query()->find($request->id);
        if ($request->price >= ($order->cardsSum() - $order->discount))
        {
            Alert::error('Қисман тўлаш учун тўлов суммаси махсулот суммасидан кам болиши керак!');
        } else {
            $party = SalesParty::createParty($request->customer_id, $order->discount);
            Payment::addPartPayment($party->id, $order, $request->type, $request->price);
            Duty::paymentDuty($party->id, $order, $request->price);
            Sale::createSales($party->id, $request->id, $party->branch_id);
            $this->deleteCard($request);
            Alert::success('Махсулотлар муаффақиятли сотилди');
        }
    }*/

    /*public function deleteCard(Request $request)
    {
        Card::query()->where('order_id', $request->id)->delete();
        Order::query()->where('id', $request->id)->delete();
        Alert::success('Муаффақиятли тозаланди');
    }*/
}

<?php

namespace App\Orchid\Screens\Order;


use App\Models\Customer;
use App\Models\Duty;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\SalesParty;
use App\Models\TelegramOrder;
use App\Models\TelegramUser;
use App\Orchid\Layouts\TelegramOrder\addUserModal;
use App\Orchid\Layouts\TelegramOrder\fullPaymentModal;
use App\Orchid\Layouts\TelegramOrder\OrderListTable;
use App\Orchid\Layouts\TelegramOrder\partPaymentModal;
use App\Services\BotUserNotify;
use App\Services\CacheService;
use Illuminate\Http\Request;
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
            'orders' => TelegramOrder::query()->with(['user.customer', 'user.place', 'products'])
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
            'place_id' => $user->place_id,
            'address' => $user->address,
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

        BotUserNotify::acceptOrder($order->user->telegram_id, $order->id);
        Alert::success('Буюртма муффақиятли қабул қилинди.');
    }

    public function deleteOrder(TelegramOrder $order)
    {
        $order_id = $order->id;
        $this->deleteOrderWithItem($order);
        BotUserNotify::deleteOrder($order->user->telegram_id, $order_id);
        Alert::success('Буюртма муффақиятли бекор қилинди');
    }

    public function duty(TelegramOrder $order)
    {
        $party = SalesParty::createParty($order->user->customer_id, 0);
        Duty::tgUserDuty($party->id, $order);
        Sale::createTgSales($party->id, $order->id, $party->branch_id, $order->user->customer_id);
        $this->deleteOrderWithItem($order);
        Alert::success('Махсулотлар муаффақиятли сотилди');
    }

    public function fullPayment(TelegramOrder $order, Request $request)
    {
        $party = SalesParty::createParty($order->user->customer_id, 0);
        Payment::addTgOrderPayment($party->id, $order, $request->type);
        Sale::createTgSales($party->id, $order->id, $party->branch_id, $order->user->customer_id);
        $this->deleteOrderWithItem($order);
        Alert::success('Махсулотлар муаффақиятли сотилди');
    }

    public function partPayment(TelegramOrder $order, Request $request)
    {
        if ($request->price >= ($order->cardsSum()))
        {
            Alert::error('Қисман тўлаш учун тўлов суммаси махсулот суммасидан кам болиши керак!');
        } else {
            $party = SalesParty::createParty($order->user->customer_id, 0);
            Payment::addTgOrderPartPayment($party->id, $order, $request->type, $request->price);
            Duty::tgUserPaymentDuty($party->id, $order, $request->price);
            Sale::createTgSales($party->id, $order->id, $party->branch_id, $order->user->customer_id);
            $this->deleteOrderWithItem($order);
            Alert::success('Махсулотлар муаффақиятли сотилди');
        }
    }

    private function deleteOrderWithItem(TelegramOrder $order)
    {
        $order->products()->delete();
        $order->delete();
    }
}

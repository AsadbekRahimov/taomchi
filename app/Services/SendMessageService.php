<?php

namespace App\Services;

use App\Models\Expence;
use App\Models\Order;
use App\Models\PurchaseParty;
use App\Models\Sale;
use App\Models\TelegramOrder;
use App\Models\TelegramOrderItem;
use App\Models\TelegramUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SendMessageService
{
    public static function sendOrder(Order $order, $cards)
    {
        $user = Auth::user()->name;
        $message = 'Сотувчи: #' . Str::slug($user, '_') . "\r\n"
            . 'Мижоз: #' . Str::slug($order->customer->name) . "\r\n";

        if ($cards->count())
        {
            $message .= "\r\n" . 'Махсулотлар: '. "\r\n" . '—————————————————' . "\r\n\r\n";
            foreach ($cards as $card) {
                $message .=  'Махсулот: ' . $card->product->name . "\r\n"
                         . 'Микдори: ' . HelperService::getQuantity($card->quantity) . "\r\n"
                         . 'Суммаси: ' . number_format($card->price) . ' | ' . number_format($card->price * $card->quantity) . "\r\n\r\n";
            }
            $message .= '—————————————————' . "\r\n" . 'Умумий суммаси: ' . number_format($order->cardsSum()) . "\r\n"
                     . 'Сана: ' . $order->created_at->toDateTimeString();
        }

        TelegramNotify::sendMessage($message, 'order');
    }

    public static function sendReport()
    {
        $sell_price = HelperService::statTotalPrice(Sale::query()->whereDate('updated_at', Carbon::today())->get());
        $expenses = (int)Expence::query()->whereDate('updated_at', Carbon::today())->sum('price');
        $day_profit = $sell_price - $expenses;

        $message = 'Сотилган махсулотлар нархи : ' . number_format($sell_price) . "\r\n"
            . 'Чиқимлар : ' . number_format($expenses) . "\r\n";

        if ($day_profit >= 0)
            $message .= 'Бугунги тушум: ' . number_format($day_profit);
        else
            $message .= 'Бугунги зарар: ' . number_format($day_profit);
        TelegramNotify::sendReport($message);
    }

    public static function sendTelegramOrder($order_id)
    {
        $order = TelegramOrder::query()->find($order_id);
        $order_items = TelegramOrderItem::query()->where('order_id', $order->id)->get();
        $user = TelegramUser::query()->find($order->user_id);

        $message = 'Буюртма: #' . $order->id . "\r\nТелефон: " . $order->user->phone;
        if(!is_null($order->user->username)) $message .= 'Telegram: @' . $order->user->username . "\r\n";
        $message .= "\r\nМанзил: " . $user->place->name . "\r\n" . $user->address . "\r\n\r\n";

        if (!$order_items->isEmpty())
        {
            $total_price = 0;
            $message .= 'Махсулотлар: '. "\r\n" . '—————————————————' . "\r\n\r\n";
            foreach ($order_items as $item) {
                $item_price = $item->price * $item->count;
                $total_price += $item_price;
                $message .=  'Махсулот: ' . $item->product->name . "\r\n"
                    . 'Микдори: ' . $item->count . "\r\n"
                    . 'Суммаси: ' . number_format($item->price) . ' | ' . number_format($item_price) . "\r\n\r\n";
            }
            $message .= '—————————————————' . "\r\n" . 'Умумий суммаси: ' . number_format($total_price) . "\r\n"
                . 'Сана: ' . $order->created_at->toDateTimeString();
        }

        TelegramNotify::sendMessage($message, 'tg_order');
    }

    public static function deleteOrder($order_id)
    {
        $message = '#' . $order_id . ' рақамли буюртма бекор қилинди.';
        TelegramNotify::sendMessage($message, 'tg_order');
    }
}

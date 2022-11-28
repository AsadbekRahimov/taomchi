<?php

namespace App\Services;

use App\Models\Expence;
use App\Models\Order;
use App\Models\PurchaseParty;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SendMessageService
{
    public static function sendOrder(Order $order, $cards)
    {
        $user = Auth::user()->name;
        $message = 'Сотувчи: #' . Str::slug($user, '_') . "\r\n"
            . 'Мижоз: #' . $order->customer->name . "\r\n";

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

        TelegramNotify::sendMessage($message);
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
}

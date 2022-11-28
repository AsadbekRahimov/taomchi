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

        TelegramNotify::sendMessage($message, 'sale');

    }


    public static function sendReport()
    {
        $sell_price = HelperService::statTotalPrice(Sale::query()->whereDate('updated_at', Carbon::today())->get(), 'price');
        $real_price = HelperService::statTotalPrice(Sale::query()->with('product')->whereDate('updated_at', Carbon::today())->get(), 'real_price');
        $expenses = (int)Expence::query()->whereDate('updated_at', Carbon::today())->whereNull('party_id')->sum('price');
        $day_profit = $sell_price - $real_price - $expenses;

        $message = 'Сотилган нарх : ' . number_format($sell_price) . "\r\n"
            . 'Тан нархи : ' . number_format($real_price) . "\r\n"
            . 'Чиқимлар : ' . number_format($expenses) . "\r\n";

        if ($day_profit >= 0)
            $message .= 'Бугунги фойда: ' . number_format($day_profit);
        else
            $message .= 'Бугунги зарар: ' . number_format($day_profit);
        TelegramNotify::sendReport($message);
    }

    public static function stockQuantity(\App\Models\Stock $stock, $type)
    {
        if ($type === 'nothing')
            $caption = 'maxsulot_qolmadi';
        elseif ($type === 'less')
            $caption = 'maxsulot_kam_miqdorda';

        $message = 'Махсулот: ' . $stock->product->name . "\r\n"
            . 'Мавжуд микдори: ' . HelperService::getQuantity($stock->quantity, $stock->product->box) . "\r\n"
            . 'Минимал чегараси: ' . HelperService::getQuantity($stock->product->min, $stock->product->box) . "\r\n";
        TelegramNotify::sendMessage($message, 'all', $caption);
    }
}

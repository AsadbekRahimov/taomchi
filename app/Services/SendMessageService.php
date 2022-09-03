<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SendMessageService
{

    public static function sendOrder(Order $order, \Illuminate\Database\Eloquent\Collection|array $cards)
    {
        $user = Auth::user()->name;
        $message = 'Сотувчи: #' . Str::slug($user, '_') . "\r\n" . 'Буюртма: #O_' . $order->id . "\r\n"
            . 'Мижоз: #' . $order->customer->name . "\r\n";

        if ($cards->count())
        {
            $message .= "\r\n" . 'Махсулотлар: '. "\r\n" . '—————————————————' . "\r\n\r\n";
            foreach ($cards as $card) {
                $message .=  'Махсулот: ' . $card->product->name . "\r\n"
                         . 'Микдори: ' . HelperService::getQuantity($card->quantity, $card->product->box) . "\r\n"
                         . 'Суммаси: ' . number_format($card->price) . ' | ' . number_format($card->price * $card->quantity) . "\r\n\r\n";
            }
            $message .= '—————————————————' . "\r\n" . 'Умумий суммаси: ' . number_format($order->cardsSum()) . "\r\n"
                     . 'Сана: ' . $order->created_at->toDateTimeString();
        }

        TelegramNotify::sendMessage($message, 'sale');

    }

    public static function stockQuantity(\App\Models\Stock $stock, $type)
    {
        if ($type == 'nothing')
            $caption = 'maxsulot_qolmadi';
        elseif ($type == 'less')
            $caption = 'maxsulot_kam_miqdorda';

        $message = 'Махсулот: ' . $stock->product->name . "\r\n"
            . 'Мавжуд микдори: ' . HelperService::getQuantity($stock->quantity, $stock->product->box) . "\r\n"
            . 'Минимал чегараси: ' . HelperService::getQuantity($stock->product->min, $stock->product->box) . "\r\n";
        TelegramNotify::sendMessage($message, 'all', $caption);
    }
}

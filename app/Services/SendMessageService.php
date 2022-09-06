<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PurchaseParty;
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
                         . 'Микдори: ' . HelperService::getQuantity($card->quantity, $card->product->box) . "\r\n"
                         . 'Суммаси: ' . number_format($card->price) . ' | ' . number_format($card->price * $card->quantity) . "\r\n\r\n";
            }
            $message .= '—————————————————' . "\r\n" . 'Умумий суммаси: ' . number_format($order->cardsSum()) . "\r\n"
                     . 'Сана: ' . $order->created_at->toDateTimeString();
        }

        TelegramNotify::sendMessage($message, 'sale');

    }

    public static function sendPurchase(PurchaseParty $purchaseParty, $expence, $duty)
    {
        $profit = 0;
        $user = Auth::user()->name;
        $message = 'Сотувчи: #' . Str::slug($user, '_') . "\r\n"
            . 'Таминотчи: #' . $purchaseParty->supplier->name . "\r\n";

        $message .= "\r\n" . 'Махсулотлар: '. "\r\n" . '—————————————————' . "\r\n\r\n";
        foreach ($purchaseParty->purchases as $purchases)
        {
            $product_profit = ($purchases->product->more_price - $purchases->price) * $purchases->quantity;
            $profit += $product_profit;
            $message .=  'Махсулот: ' . $purchases->product->name . "\r\n"
                    . 'Микдори: ' . HelperService::getQuantity($purchases->quantity, $purchases->product->box) . "\r\n"
                    . 'Суммаси: ' . number_format($purchases->price) . ' | ' . number_format($purchases->price * $purchases->quantity) . "\r\n"
                    . 'Фойдаси: ' . number_format($product_profit) . "\r\n\r\n";
        }
        $message .= '—————————————————' . "\r\n" . 'Умумий суммаси: ' . number_format($purchaseParty->purchasesSum()) . "\r\n"
                . 'Умумий фойдаси: ' . number_format($profit) . "\r\n"
                . 'Туланди: ' . number_format($expence) . "\r\n"
                . 'Карз булди: ' . number_format($duty) . "\r\n"
                . 'Сана: ' . $purchaseParty->created_at->toDateTimeString();

        TelegramNotify::sendMessage($message, 'order');

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

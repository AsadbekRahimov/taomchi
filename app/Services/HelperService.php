<?php

namespace App\Services;

use App\Models\Order;
use Orchid\Support\Color;

class HelperService
{
    public static function telephone($number) {
        return '+998' . str_replace(['(', ')', '-', ' '], '', $number);
    }

    public static function getStockColor(\App\Models\Stock $stock)
    {
        if ($stock->quantity <= 0)
            return Color::DANGER();
        elseif ($stock->quantity > 0 && $stock->quantity < $stock->product->min)
            return Color::WARNING();
        else
            return Color::SUCCESS();
    }

    public static function getStockQuantity(\App\Models\Stock $stock)
    {
        if ($stock->quantity == 0)
            return 'Mavjud emas';
        elseif ($stock->quantity < 0)
            return $stock->quantity;
        elseif ($stock->quantity > 0 && $stock->product->box == 1)
            return $stock->quantity;
        else
            return round($stock->quantity / $stock->product->box) . ' (' . $stock->quantity . ')';
    }

    public static function getOrderPrice(Order $order)
    {
        if ($order->discount)
            return number_format($order->cardsSum() - $order->discount) . ' (<strike>' . number_format($order->cardsSum()) . '</strike>)';
        else
            return number_format($order->cardsSum());
    }
}

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
            return floor($stock->quantity / $stock->product->box) . ' (' . $stock->quantity . ')';
    }

    public static function getOrderPrice(Order $order)
    {
        if ($order->discount)
            return number_format($order->cardsSum() - $order->discount) . ' (<strike>' . number_format($order->cardsSum()) . '</strike>)';
        else
            return number_format($order->cardsSum());
    }

    public static function getQuantity($quantity, $box)
    {
        if ($box == 1)
            return $quantity;
        else
            return floor($quantity / $box) . ' (' . $quantity . ')';
    }

    public static function getTotalPrice($products)
    {
        $sum = 0;
        foreach ($products as $product)
            $sum += $product->quantity * $product->price;
        return $sum;
    }
}

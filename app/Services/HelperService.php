<?php

namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;
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

    public static function getOrderPrice(Order $order)
    {
        if ($order->discount)
            return number_format($order->cardsSum() - $order->discount) . ' (<strike>' . number_format($order->cardsSum()) . '</strike>)';
        else
            return number_format($order->cardsSum());
    }

    public static function getQuantity($quantity)
    {
        return $quantity;
    }

    public static function getTotalPrice($products)
    {
        $sum = 0;
        foreach ($products as $product)
            $sum += $product->quantity * $product->price;
        return $sum;
    }

    public static function getDutyColor($updated_at)
    {
        $current_date = Carbon::now()->toDateTimeString();

        //NUMBER DAYS BETWEEN TWO DATES CALCULATOR
        $start_date = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $updated_at);
        $end_date = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $current_date);
        $days = $start_date->diffInDays($end_date);

        if ($days <= 5)
            return Color::WARNING();
        elseif ($days > 5)
            return Color::DANGER();
    }

    public static function statTotalPrice($sales)
    {
        $sum = 0;
        foreach ($sales as $sale)
        {
            $sum += $sale->price * $sale->quantity;
        }
        return $sum;
    }
}

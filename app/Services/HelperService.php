<?php

namespace App\Services;

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
}

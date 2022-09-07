<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use OpenSpout\Writer\Common\Creator\Style\StyleBuilder;
use Rap2hpoutre\FastExcel\FastExcel;

class ReportService
{

    public static function sellReport(array $date)
    {
        $begin = $date['start'] . ' 00:00:00';
        $end = $date['end'] . ' 23:59:59';

        $customers = Cache::get('customers');
        $products = Cache::get('products');

        $sales = DB::select("SELECT customer_id, product_id, quantity, price FROM sales where created_at BETWEEN '" . $begin . "' AND '" .  $end . "'");

        $result = collect();
        foreach($sales as $sale)
        {
            $result->push([
                'Мижоз' => $customers[$sale->customer_id],
                'Махсулот' => $products[$sale->product_id],
                'Микдори' => $sale->quantity,
                'Сотилган нарх' => $sale->price,
            ]);
        }

        return (new FastExcel($result))
            ->headerStyle((new StyleBuilder())->setFontBold()->build())
            ->rowsStyle((new StyleBuilder())
                ->setFontSize(12)
                ->setBackgroundColor("EDEDED")
                ->build())
            ->download('Cотилган-' . $date['start'] . '_' . $date['end'] .'.xlsx');
    }

    public static function buyReport(array $date)
    {
        $begin = $date['start'] . ' 00:00:00';
        $end = $date['end'] . ' 23:59:59';

        $suppliers = Cache::get('suppliers');
        $products = Cache::get('products');

        $purchases = DB::select("SELECT supplier_id, product_id, quantity, price, profit FROM purchases where created_at BETWEEN '" . $begin . "' AND '" .  $end . "'");

        $result = collect();

        foreach($purchases as $purchase)
        {
            $result->push([
                'Таминотчи' => $suppliers[$purchase->supplier_id],
                'Махсулот' => $products[$purchase->product_id],
                'Микдори' => $purchase->quantity,
                'Сотилган нарх' => $purchase->price,
                'Умумий фойда' => $purchase->profit,
            ]);
        }

        return (new FastExcel($result))
            ->headerStyle((new StyleBuilder())->setFontBold()->build())
            ->rowsStyle((new StyleBuilder())
                ->setFontSize(12)
                ->setBackgroundColor("EDEDED")
                ->build())
            ->download('Cотиб_олинган-' . $date['start'] . '_' . $date['end'] .'.xlsx');
    }
}

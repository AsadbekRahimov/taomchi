<?php

namespace App\Services;

use App\Models\Payment;
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

        return self::generateExcel($result, $date, 'Cотилган');
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

        return self::generateExcel($result, $date, 'Cотиб_олинган');
    }

    public static function paymentReport(array $date)
    {
        $begin = $date['start'] . ' 00:00:00';
        $end = $date['end'] . ' 23:59:59';

        $customers = Cache::get('customers');

        $payments = DB::select("SELECT customer_id, price, type FROM payments where created_at BETWEEN '" . $begin . "' AND '" .  $end . "'");

        $result = collect();

        foreach($payments as $payment)
        {
            $result->push([
                'Мижоз' => $customers[$payment->customer_id],
                'Тўлов суммаси' => $payment->price,
                'Тўлов тури' => Payment::TYPE[$payment->type],
            ]);
        }

        return self::generateExcel($result, $date, 'Тўловлар');
    }

    private static function generateExcel(\Illuminate\Support\Collection $result, array $date, string $title)
    {
        return (new FastExcel($result))
            ->headerStyle((new StyleBuilder())->setFontBold()->build())
            ->rowsStyle((new StyleBuilder())
                ->setFontSize(12)
                ->setBackgroundColor("EDEDED")
                ->build())
            ->download($title . '-' . $date['start'] . '_' . $date['end'] .'.xlsx');
    }
}

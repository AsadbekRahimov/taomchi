<?php

namespace App\Services;

use App\Models\Expence;
use App\Models\Payment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use OpenSpout\Writer\Common\Creator\Style\StyleBuilder;
use Rap2hpoutre\FastExcel\FastExcel;
use Rap2hpoutre\FastExcel\SheetCollection;

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

        return self::generateExcel($result, $date, 'Sotilgan');
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

        return self::generateExcel($result, $date, 'Sotib-olingan');
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

        return self::generateExcel($result, $date, 'Tolovlar');
    }


    public static function expenceReport(array $date)
    {
        $begin = $date['start'] . ' 00:00:00';
        $end = $date['end'] . ' 23:59:59';

        $suppliers = Cache::get('suppliers');

        $expences = Expence::query()->with(['party'])->whereBetween('created_at', [$begin, $end])->get();


        $result['Boshqa'] = collect();
        $result['Taminotchilarga'] = collect();

        foreach($expences as $consumption)
        {
            if (!$consumption->party_id) {
                $result['Boshqa']->push([
                    'Миқдори' => $consumption->price,
                    'Таснифи' => $consumption->description,
                ]);
            } else {
                $result['Taminotchilarga']->push([
                    'Миқдори' => $consumption->price,
                    'Таминотчи' => $suppliers[$consumption->party->supplier_id],
                ]);
            }
        }

        $results = new SheetCollection($result);

        return self::generateExcel($results, $date, 'Chiqimlar');
    }


    public static function dutiesReport($type)
    {
        $suppliers = Cache::get('suppliers');
        $customers = Cache::get('customers');

        $result = collect();
        $title = '';
        if ($type == 'customer')
        {
            $duties = DB::select("SELECT customer_id, duty, created_at FROM duties where customer_id is not null");
            $title = 'Qarzdorlar';
            foreach($duties as $duty)
            {
                $result->push([
                    'Мижоз' => $customers[$duty->customer_id],
                    'Қарз' => $duty->duty,
                    'Сана' => $duty->created_at,
                ]);
            }
        } elseif ($type == 'supplier') {
            $duties = DB::select("SELECT supplier_id, duty, created_at FROM duties where supplier_id is not null");
            $title = 'Qarzlarim';
            foreach($duties as $duty)
            {
                $result->push([
                    'Таминотчи' => $suppliers[$duty->supplier_id],
                    'Қарз' => $duty->duty,
                    'Сана' => $duty->created_at,
                ]);
            }
        }

        return self::generateExcel($result, null, $title);
    }

    private static function generateExcel($result, $date, $title)
    {
        $interval = is_null($date) ? '' : '-' . $date['start'] . '_' . $date['end'];
        return (new FastExcel($result))
            ->headerStyle((new StyleBuilder())->setFontBold()->build())
            ->rowsStyle((new StyleBuilder())
                ->setFontSize(12)
                ->setBackgroundColor("EDEDED")
                ->build())
            ->download($title . $interval .'.xlsx');
    }
}

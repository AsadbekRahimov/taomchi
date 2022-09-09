<?php

namespace App\Services;

use App\Models\Expence;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use OpenSpout\Writer\Common\Creator\Style\StyleBuilder;
use Rap2hpoutre\FastExcel\FastExcel;
use Rap2hpoutre\FastExcel\SheetCollection;

class ReportService
{

    public static function courierReport(array $date, $for_sheet_collection = null)
    {
        $begin = $date['start'] . ' 00:00:00';
        $end = $date['end'] . ' 23:59:59';

        $payments = Payment::select('user_id', DB::raw('sum(price) as sum'))
            ->when(is_null($begin) && is_null($end), function ($query) {
                $query->whereDate('created_at', Carbon::today());
            })->when(!is_null($begin) && !is_null($end), function ($query) use ($begin, $end) {
                $query->whereBetween('created_at', [$begin, $end]);
            })->groupBy('user_id')->get()->toArray();

        $users = User::query()->pluck('name', 'id')->toArray();

        $result = collect();

        foreach($payments as $payment)
        {
            $result->push([
                'Сотувчи' => $users[$payment['user_id']],
                'Суммаси' => $payment['sum'],
            ]);
        }

        return is_null($for_sheet_collection) ? self::generateExcel($result, $date, 'Sotuvchilar') : $result;
    }

    public static function sellReport(array $date, $for_sheet_collection = null)
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

        return is_null($for_sheet_collection) ? self::generateExcel($result, $date, 'Sotilgan') : $result;
    }

    public static function buyReport(array $date, $for_sheet_collection = null)
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

        return is_null($for_sheet_collection) ? self::generateExcel($result, $date, 'Sotib-olingan') : $result;
    }

    public static function paymentReport(array $date, $for_sheet_collection = null)
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

        return is_null($for_sheet_collection) ? self::generateExcel($result, $date, 'Tolovlar') : $result;
    }


    public static function expenceReport(array $date, $for_sheet_collection = null)
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
        return is_null($for_sheet_collection) ? self::generateExcel($results, $date, 'Chiqimlar') : $result;
    }


    public static function dutiesReport($type, $for_sheet_collection = null)
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

        return is_null($for_sheet_collection) ? self::generateExcel($result, null, $title) : $result;
    }


    public static function allReport($date, $type)
    {
        $results = new SheetCollection([
            'Sotuvchilar' => self::courierReport($date, 'yes'),
            'Sotilgan' => self::sellReport($date, 'yes'),
            'Sotib-olingan' => self::buyReport($date, 'yes'),
            'To\'lovlar' => self::paymentReport($date, 'yes'),
            'Chiqimlar' => self::expenceReport($date, 'yes')['Boshqa'],
            'Taminotchilarga to\'lovlar' => self::expenceReport($date, 'yes')['Taminotchilarga'],
            'Qarzdorlar' => self::dutiesReport('customer', 'yes'),
            'Qarzlarim' => self::dutiesReport('supplier', 'yes'),
        ]);

        if ($type == 'download')
            return self::generateExcel($results, $date, 'Kunlik_umumiy_xisobot');
        elseif ($type == 'store')
            return self::storeGeneratedExcel($results);
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

    private static function storeGeneratedExcel($result)
    {
        return (new FastExcel($result))
            ->headerStyle((new StyleBuilder())->setFontBold()->build())
            ->rowsStyle((new StyleBuilder())
                ->setFontSize(12)
                ->setBackgroundColor("EDEDED")
                ->build())
            ->export('storage/app/report.xlsx');
    }
}

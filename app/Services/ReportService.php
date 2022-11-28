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

        return is_null($for_sheet_collection) ? self::generateExcel($result, $date, 'Курерлар') : $result;
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

        return is_null($for_sheet_collection) ? self::generateExcel($result, $date, 'Сотилган') : $result;
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

        return is_null($for_sheet_collection) ? self::generateExcel($result, $date, 'Тўловлар') : $result;
    }


    public static function expenceReport(array $date, $for_sheet_collection = null)
    {
        $begin = $date['start'] . ' 00:00:00';
        $end = $date['end'] . ' 23:59:59';

        $expences = Expence::query()->whereBetween('created_at', [$begin, $end])->get();


        $result = collect();

        foreach($expences as $consumption)
        {
            $result->push([
                 'Миқдори' => $consumption->price,
                 'Таснифи' => $consumption->description,
            ]);
        }

        return is_null($for_sheet_collection) ? self::generateExcel($result, $date, 'Чиқимлар') : $result;
    }


    public static function dutiesReport($for_sheet_collection = null)
    {
        $customers = Cache::get('customers');

        $result = collect();
        $duties = DB::select("SELECT customer_id, duty, created_at FROM duties where customer_id is not null");
        foreach($duties as $duty)
        {
            $result->push([
                'Мижоз' => $customers[$duty->customer_id],
                'Қарз' => $duty->duty,
                'Сана' => $duty->created_at,
            ]);
        }

        return is_null($for_sheet_collection) ? self::generateExcel($result, null, 'Қарздорлар') : $result;
    }


    public static function allReport($date, $type)
    {
        $results = new SheetCollection([
            'Курерлар' => self::courierReport($date, 'yes'),
            'Сотилган' => self::sellReport($date, 'yes'),
            'Тўловлар' => self::paymentReport($date, 'yes'),
            'Чиқимлар' => self::expenceReport($date, 'yes'),
            'Қарздорлар' => self::dutiesReport('yes'),
        ]);

        if ($type == 'download')
            return self::generateExcel($results, $date, 'Умумий_хисобот');
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

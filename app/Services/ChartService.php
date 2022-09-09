<?php

namespace App\Services;

use App\Models\Duty;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\SalesParty;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ChartService
{
    public static function paymentChart($begin = null, $end = null)
    {
        $payments = Payment::select('type', DB::raw('sum(price) as sum'))
            ->when(Auth::user()->branch_id, function ($query){
                return $query->where('branch_id', Auth::user()->branch_id);
            })->when(is_null($begin) && is_null($end), function ($query) {
                $query->whereDate('created_at', Carbon::today());
            })->when(!is_null($begin) && !is_null($end), function ($query) use ($begin, $end) {
                $query->whereBetween('created_at', [$begin, $end]);
            })->groupBy('type')->get()->toArray();

        $result =[
            'values' => [],
            'labels' => [],
        ];

        foreach ($payments as $payment) {
            $result['values'][] = $payment['sum'];
            $result['labels'][] = Payment::TYPE[$payment['type']];
        }
        return $result;
    }

    public static function dutiesChart()
    {
        $customers = Cache::get('customers');

        $duties = Duty::select('customer_id', DB::raw('sum(duty) as sum'))
            ->whereNotNull('customer_id')
            ->when(Auth::user()->branch_id, function ($query){
                return $query->where('branch_id', Auth::user()->branch_id);
            })->groupBy('customer_id')->get()->toArray();

        $result =[
            'values' => [],
            'labels' => [],
        ];

        foreach ($duties as $duty) {
            $result['values'][] = $duty['sum'];
            $result['labels'][] = $customers[$duty['customer_id']];
        }
        return $result;
    }

    public static function SellChart($begin = null, $end = null)
    {
        $product_names = Cache::get('products');

        $products = Sale::select('product_id', 'quantity', 'price')
            ->when(Auth::user()->branch_id, function ($query){
                return $query->where('branch_id', Auth::user()->branch_id);
            })->when(is_null($begin) && is_null($end), function ($query) {
                $query->whereDate('created_at', Carbon::today());
            })->when(!is_null($begin) && !is_null($end), function ($query) use ($begin, $end) {
                $query->whereBetween('created_at', [$begin, $end]);
            })->get()->toArray();

        $sell[] = array();

        foreach ($products as $product) {
            if (!array_key_exists($product['product_id'], $sell)) $sell[$product['product_id']] = 0;
            $sell[$product['product_id']] += $product['quantity'] * $product['price'];
        }

        $result =[
            'values' => [],
            'labels' => [],
        ];

        foreach ($sell as $key => $value) {
            if ($key != 0)
            {
                $result['values'][] = $value;
                $result['labels'][] = $product_names[$key];
            }
        }
        return $result;
    }

    public static function CourierChart($begin = null, $end = null)
    {
        $payments = Payment::select('user_id', DB::raw('sum(price) as sum'))
            ->when(Auth::user()->branch_id, function ($query){
                return $query->where('branch_id', Auth::user()->branch_id);
            })->when(is_null($begin) && is_null($end), function ($query) {
                $query->whereDate('created_at', Carbon::today());
            })->when(!is_null($begin) && !is_null($end), function ($query) use ($begin, $end) {
                $query->whereBetween('created_at', [$begin, $end]);
            })->groupBy('user_id')->get()->toArray();

        $users = User::query()->pluck('name', 'id')->toArray();

        $result = [
            'values' => [],
            'labels' => [],
        ];

        foreach ($payments as $payment) {
            $result['values'][] = $payment['sum'];
            $result['labels'][] = $users[$payment['user_id']];
        }

        return $result;
    }
}

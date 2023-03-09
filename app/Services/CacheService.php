<?php

namespace App\Services;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CacheService
{

    public static function ProductsKeyValue()
    {
        return Cache::rememberForever('product_key_value', function () {
            return \App\Models\Product::query()->pluck('name', 'id');
        });
    }

    public static function getProducts()
    {
        return Cache::rememberForever('products', function () {
            return \App\Models\Product::query()->get();
        });
    }


    public static function getTgProducts()
    {
        return Cache::rememberForever('tg_products', function () {
            return \App\Models\Product::query()->where('for_telegram', 1)->get();
        });
    }

    public static function getCustomers()
    {
        return Cache::rememberForever('customers', function () {
            $result = array();
            $data = DB::select("select c.id as id, CONCAT(c.name, ' | ', pl.name, ' ', c.address) as address
                from customers c join places pl on c.place_id = pl.id");

            foreach ($data as $item)
            {
                $result[$item->id] = $item->address;
            }
            return collect($result);
        });
    }


    public static function getPlaces()
    {
        return Cache::rememberForever('places', function () {
            return \App\Models\Place::query()->pluck('name', 'id');
        });
    }
}

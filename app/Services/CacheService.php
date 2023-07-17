<?php

namespace App\Services;
use App\Models\Customer;
use App\Models\ProductPrices;
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

    public static function getPlaceProducts($place_id)
    {
        return Cache::rememberForever('place_products_' . $place_id, function () use ($place_id) {
            return ProductPrices::query()->with('product')->whereHas('product', function ($query) {
                $query->where('for_telegram', 1);
            })->where('place_id', $place_id)->get();
        });
    }

    public static function getCustomers()
    {
        return Cache::rememberForever('customers', function () {
            $data = Customer::with('place')->select('id', 'name', 'address', 'place_id')->get();
            return  $data->mapWithKeys(function ($item) {
                return [$item->id => $item->name . ' | ' . $item->place->name . ' ' . $item->address];
            });
        });
    }


    public static function getPlaces()
    {
        return Cache::rememberForever('places', function () {
            return \App\Models\Place::query()->pluck('name', 'id');
        });
    }

    public static function getProductCategories()
    {
        return Cache::rememberForever('productCategories', function () {
            return \App\Models\ProductCategory::query()->pluck('name', 'id');
        });
    }
}

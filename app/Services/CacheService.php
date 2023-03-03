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
}

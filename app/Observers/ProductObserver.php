<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\CacheService;
use Illuminate\Support\Facades\Cache;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     *
     * @param  \App\Models\Product  $product
     * @return void
     */
    public function created(Product $product)
    {
        Cache::forget('product_key_value');
        Cache::forget('products');
        Cache::forget('tg_products');
        CacheService::ProductsKeyValue();
        CacheService::getProducts();
        CacheService::getTgProducts();
    }

    /**
     * Handle the Product "updated" event.
     *
     * @param  \App\Models\Product  $product
     * @return void
     */
    public function updated(Product $product)
    {
        Cache::forget('product_key_value');
        Cache::forget('products');
        Cache::forget('tg_products');
        CacheService::ProductsKeyValue();
        CacheService::getProducts();
        CacheService::getTgProducts();
    }

    /**
     * Handle the Product "deleted" event.
     *
     * @param  \App\Models\Product  $product
     * @return void
     */
    public function deleted(Product $product)
    {
        if ($product->isDirty('for_telegram')) {
            Cache::forget('tg_products');
            CacheService::getTgProducts();
        }
    }
}

<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\ProductPrices;
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
        $this->createPlacePrices($product->id);
        $this->updateCache();
    }

    /**
     * Handle the Product "updated" event.
     *
     * @param  \App\Models\Product  $product
     * @return void
     */
    public function updated(Product $product)
    {
        $this->updateCache();
    }

    /**
     * Handle the Product "deleted" event.
     *
     * @param  \App\Models\Product  $product
     * @return void
     */
    public function deleted(Product $product)
    {
        ProductPrices::query()->where('product_id', $product->id)->delete();
        $this->updateCache();
    }

    private function updateCache(): void
    {
        Cache::forget('product_key_value');
        Cache::forget('products');
        CacheService::ProductsKeyValue();
        CacheService::getProducts();
    }

    private function createPlacePrices($product_id): void
    {
        $insertData = CacheService::getPlaces()->keys()->map(function ($key) use ($product_id) {
            return [
                'product_id' => $product_id,
                'place_id' => $key,
                'price' => 1000,
            ];
        })->toArray();

        ProductPrices::query()->insert($insertData);
    }
}

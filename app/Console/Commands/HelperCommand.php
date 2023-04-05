<?php

namespace App\Console\Commands;

use App\Models\ProductPrices;
use App\Services\CacheService;
use Illuminate\Console\Command;

class HelperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'help:me';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Helper command for some fiches';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $products = CacheService::getProducts();

        foreach ($products as $key => $product) {
            $insertData = CacheService::getPlaces()->keys()->map(function ($key) use ($product) {
                return [
                    'product_id' => $product->id,
                    'place_id' => $key,
                    'price' => $product->one_price,
                ];
            })->toArray();
            ProductPrices::query()->insert($insertData);
            $this->info($key+1 . ') Product: ' . $product->name . ' inserted');
        }
    }
}

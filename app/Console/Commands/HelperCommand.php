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
        $insertData = CacheService::getPlaces()->keys()->map(function ($key) {
            return [
                'product_id' => rand(1, 30),
                'place_id' => $key
            ];
        })->toArray();

        dd($insertData);
    }
}

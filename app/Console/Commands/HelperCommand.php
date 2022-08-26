<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $products = DB::connection('other')->table('products')->get();
        foreach ($products as $product)
        {
            $this->info($product->name);
            Product::query()->create([
                'name' => $product->name,
                'measure_id' => rand(1, 4),
                'box' => $product->box,
                'min' => $product->min_quantity,
                'one_price' => $product->one_price,
                'more_price' => $product->more_price,
            ]);
        }
        return 0;
    }
}

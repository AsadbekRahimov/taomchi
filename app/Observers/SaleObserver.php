<?php

namespace App\Observers;

use App\Models\Sale;
use App\Models\Stock;
use App\Services\SendMessageService;

class SaleObserver
{
    /**
     * Handle the Sale "created" event.
     *
     * @param  \App\Models\Sale  $sale
     * @return void
     */
    public function created(Sale $sale)
    {
        $stock = Stock::query()
            ->where('branch_id', $sale->branch_id)
            ->where('product_id', $sale->product_id)
            ->first();

        $stock->quantity -= $sale->quantity;
        $stock->save();
    }
}

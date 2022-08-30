<?php

namespace App\Observers;

use App\Models\Purchase;
use App\Models\Stock;

class PurchaseObserver
{
    /**
     * Handle the Purchase "created" event.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return void
     */
    public function created(Purchase $purchase)
    {
        $stock = Stock::query()
            ->where('branch_id', $purchase->branch_id)
            ->where('product_id', $purchase->product_id)
            ->first();

        $stock->quantity += $purchase->quantity;
        $stock->save();
    }
}

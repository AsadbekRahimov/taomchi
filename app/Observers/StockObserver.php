<?php

namespace App\Observers;

use App\Models\Stock;
use App\Services\SendMessageService;

class StockObserver
{
    /**
     * Handle the Stock "updated" event.
     *
     * @param  \App\Models\Stock  $stock
     * @return void
     */
    public function updated(Stock $stock)
    {
        if ($stock->quantity < $stock->product->min and $stock->quantity > 0)
            SendMessageService::stockQuantity($stock, 'less');
        elseif($stock->quantity <= 0)
            SendMessageService::stockQuantity($stock, 'nothing');
    }
}

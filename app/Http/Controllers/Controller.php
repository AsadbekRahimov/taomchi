<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function orderCheck($id) {
        $order = \App\Models\Order::query()->with(['cards.product.measure', 'customer'])->find($id);
        return view('print.printCheck', compact('order'));
    }

    public function tgOrderCheck($id) {
        $order = \App\Models\TelegramOrder::query()->with(['products.product.measure', 'user.customer.duties'])->find($id);
        if ($order->user->customer_id)
            return view('print.printTgCustomerCheck', compact('order'));
        else
            return view('print.printTgCheck', compact('order'));
    }
}

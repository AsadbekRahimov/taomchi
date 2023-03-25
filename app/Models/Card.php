<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'product_id',
        'quantity',
        'price',
        'order_id'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public static function createOrder($customer_id)
    {
        self::query()->where('customer_id', $customer_id)->update([
            'ordered' => 1,
        ]);

        return self::query()->where('customer_id', $customer_id)->get();
    }

    public static function createOrderCards(\Illuminate\Http\Request $request, $order_id)
    {
        $place = Customer::query()->find($request->customer_id)->place_id;
        $prices = ProductPrices::query()->select(['product_id', 'price'])->where('place_id', $place)
            ->whereIn('product_id', array_column($request->products, 'id'))
            ->get()->mapWithKeys(function ($price) {
                return [$price['product_id'] => ['price' => $price['price']]];
            })->toArray();

        foreach ($request->products as $item)
        {
            self::query()->create([
                'customer_id' => $request->customer_id,
                'product_id' => (int)$item['id'],
                'quantity' => (int)$item['count'],
                'price' => $prices[(int)$item['id']]['price'],
                'order_id' => $order_id,
            ]);
        }

        return self::query()->where('order_id', $order_id)->get();
    }
}

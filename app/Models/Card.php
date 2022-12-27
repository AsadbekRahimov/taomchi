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

    public static function addToCard(\Illuminate\Http\Request $request)
    {
        $product = Product::query()->find((int)$request->id);
        return self::query()->create([
            'customer_id' => $request->customer_id,
            'product_id' => $request->id,
            'quantity' => $request->quantity,
            'price' => $product->{ $request->price . '_price' },
        ]);
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
        foreach ($request->products as $item)
        {
            $product = Product::query()->find((int)$item['id']);
            self::query()->create([
                'customer_id' => $request->customer_id,
                'product_id' => $product->id,
                'quantity' => (int)$item['count'],
                'price' => $product->{ $item['price'] . '_price' },
                'order_id' => $order_id,
            ]);
        }

        return self::query()->where('order_id', $order_id)->get();
    }
}

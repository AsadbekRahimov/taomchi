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
        'ordered'
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
            'quantity' => $request->box === '1' ? $request->quantity * $request->box_count : $request->quantity,
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
}

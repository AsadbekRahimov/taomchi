<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Basket extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'product_id',
        'quantity',
        'price',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }


    public static function addToBasket(\Illuminate\Http\Request $request)
    {
        return self::query()->create([
            'supplier_id' => $request->supplier_id,
            'product_id' => $request->id,
            'quantity' => $request->box === '1' ? $request->quantity * $request->box_count : $request->quantity,
            'price' => $request->price
        ]);
    }

    public static function getTotalPrice(mixed $id)
    {
        $price = 0;
        foreach (self::query()->where('supplier_id', $id)->get() as $basket)
        {
            $price += $basket->quantity * $basket->price;
        }
        return $price;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'product_id',
        'quantity',
        'price',
        'profit',
        'branch_id',
        'party_id',
    ];

    public function supplier()
    {
        return $this->belongsTo(Customer::class, 'supplier_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Customer::class, 'product_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Customer::class, 'branch_id', 'id');
    }

    public function party()
    {
        return $this->belongsTo(PurchaseParty::class, 'party_id', 'id');
    }

    public static function createPurchases(PurchaseParty $party, array $basket)
    {
        //dd($basket);
        foreach ($basket as $item)
        {
            $product = Product::query()->find((int)$item['product_id']);
            self::query()->create([
                'supplier_id' => $party->supplier_id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'profit' => self::profit($item['price'], $item['quantity'], $product->more_price),
                'branch_id' => $party->branch_id,
                'party_id' => $party->id,
            ]);
        }
    }


    private static function profit($price, $quantity, $more_price)
    {
        if ($price > $more_price) {
            return ($price - $more_price) * $quantity;
        } else {
            return 0;
        }
    }
}

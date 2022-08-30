<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
       'customer_id',
       'product_id',
       'quantity',
       'price',
       'branch_id',
       'party_id'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
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
        return $this->belongsTo(SalesParty::class, 'party_id', 'id');
    }


    public static function createSales(SalesParty$party, array $cards)
    {
        foreach ($cards as $item)
        {
            $product = Product::query()->find((int)$item['product_id']);
            self::query()->create([
                'customer_id' => $party->customer_id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'branch_id' => $party->branch_id,
                'party_id' => $party->id,
            ]);
        }
    }
}

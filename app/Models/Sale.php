<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Sale extends Model
{
    use HasFactory;
    use AsSource, Filterable, Attachable;

    protected $fillable = [
       'customer_id',
       'product_id',
       'quantity',
       'price',
       'branch_id',
       'party_id'
    ];

    protected $allowedFilters = [
        'customer_id',
        'product_id',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function party()
    {
        return $this->belongsTo(SalesParty::class, 'party_id', 'id');
    }


    public static function createSales($party_id, $order_id, $branch_id)
    {
        $cards = Card::query()->where('order_id', $order_id)->get();
        foreach ($cards as $item)
        {
            self::query()->create([
                'customer_id' => $item->customer_id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'branch_id' => $branch_id,
                'party_id' => $party_id,
            ]);
        }
    }

    public function getTotalAttribute($value)
    {
        return $this->price * $this->quantity;
    }
}

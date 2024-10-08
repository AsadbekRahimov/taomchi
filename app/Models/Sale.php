<?php

namespace App\Models;

use App\Traits\HasCustomer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Sale extends Model
{
    use HasFactory, HasCustomer;
    use AsSource, Filterable, Attachable;

    protected $fillable = [
       'customer_id',
       'product_id',
       'quantity',
       'price',
       'branch_id',
       'party_id',
       'telegram_user_id'
    ];

    protected $allowedFilters = [
        'customer_id',
        'product_id',
    ];

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

    public static function createTgSales($party_id, $order_id, $branch_id, $customer_id, $telegram_user_id)
    {
        $cards = TelegramOrderItem::query()->where('order_id', $order_id)->get();
        foreach ($cards as $item)
        {
            self::query()->create([
                'customer_id' => $customer_id,
                'product_id' => $item->product_id,
                'quantity' => $item->count,
                'price' => $item->price,
                'branch_id' => $branch_id,
                'party_id' => $party_id,
                'telegram_user_id' => $telegram_user_id
            ]);
        }
    }

    public function getTotalAttribute($value)
    {
        return $this->price * $this->quantity;
    }
}

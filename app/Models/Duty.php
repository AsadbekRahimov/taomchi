<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Duty extends Model
{
    use HasFactory;
    use AsSource, Filterable, Attachable;

    protected $fillable = [
       'customer_id',
       'duty',
       'branch_id',
       'party_id',
       'for_today',
    ];

    protected $allowedFilters = [
        'customer_id',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Customer::class, 'branch_id', 'id');
    }

    public function sales()
    {
        return $this->belongsTo(SalesParty::class, 'party_id', 'id');
    }


    public static function paymentDuty($party_id, Order $order, $price)
    {
        return self::query()->create([
            'customer_id' => $order->customer_id,
            'duty' => $order->cardsSum() - $order->discount - $price,
            'branch_id' => $order->branch_id,
            'party_id' => $party_id,
            'for_today' => 0,
        ]);
    }

    public static function duty($party_id, Order $order)
    {
        return self::query()->create([
            'customer_id' => $order->customer_id,
            'duty' => $order->cardsSum() - $order->discount,
            'branch_id' => $order->branch_id,
            'party_id' => $party_id,
            'for_today' => 1,
        ]);
    }

    public static function tgUserDuty($party_id, TelegramOrder $order)
    {
        return self::query()->create([
            'customer_id' => $order->user->customer_id,
            'duty' => $order->cardsSum(),
            'branch_id' => 1,
            'party_id' => $party_id,
            'for_today' => 1,
        ]);
    }


}

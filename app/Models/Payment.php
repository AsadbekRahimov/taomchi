<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Payment extends Model
{
    use HasFactory;
    use AsSource, Filterable, Attachable;

    protected $fillable = [
        'customer_id',
        'price',
        'type',
        'branch_id',
        'party_id'
    ];

    public  const TYPE = [
        1 => 'Naqt',
        2 => 'Plastik karta',
        3 => 'Cick/Payme/...',
        4 => 'Bank o\'tkazma',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function party()
    {
        return $this->belongsTo(SalesParty::class, 'party_id', 'id');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'party_id', 'id');
    }

    public static function addPayment($party_id, Order $order, $type)
    {
        return self::query()->create([
            'customer_id' => $order->customer_id,
            'price' => ($order->cardsSum() - $order->discount),
            'type' => $type,
            'branch_id' => $order->branch_id,
            'party_id' => $party_id,
        ]);
    }

    public static function addPartPayment($party_id, Order $order, $type, $price)
    {
        if ($price != '0')
        {
            return self::query()->create([
                'customer_id' => $order->customer_id,
                'price' => $price,
                'type' => $type,
                'branch_id' => $order->branch_id,
                'party_id' => $party_id,
            ]);
        }
    }

    public static function addFullDutyPayment($duty, $type)
    {
        return self::query()->create([
            'customer_id' => $duty->customer_id,
            'price' => $duty->duty,
            'type' => $type,
            'branch_id' => $duty->branch_id,
            'party_id' => $duty->party_id,
        ]);
    }

    public static function addPartDutyPayment($price, $duty, $type)
    {
        return self::query()->create([
            'customer_id' => $duty->customer_id,
            'price' => $price,
            'type' => $type,
            'branch_id' => $duty->branch_id,
            'party_id' => $duty->party_id,
        ]);
    }

}

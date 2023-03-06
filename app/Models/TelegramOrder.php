<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'price',
        'state',
    ];

    public const TYPE = [
        'send_order' => 'Буюртма юборилган',
        'accepted_order' => 'Буюртма қабул қилинган',
    ];

    public function products()
    {
        return $this->hasMany(TelegramOrderItem::class, 'order_id', 'id');
    }
}

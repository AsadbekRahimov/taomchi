<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class TelegramOrder extends Model
{
    use HasFactory;
    use AsSource, Filterable, Attachable;

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

    public function user()
    {
        return $this->belongsTo(TelegramUser::class, 'user_id', 'id');
    }
}

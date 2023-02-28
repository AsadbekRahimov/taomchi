<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramUserCard extends Model
{
    use HasFactory;
    protected $fillable = [
       'telegram_user_id',
       'product_id',
       'count'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}

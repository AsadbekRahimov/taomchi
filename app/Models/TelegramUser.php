<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'telegram_id',
        'phone',
        'customer_id',
    ];

    public static function createNewUser($chat_id, $number)
    {
        self::query()->create([
            'telegram_id' => $chat_id,
            'phone' => '(' . substr($number, 0, 2) . ') ' . substr($number, 2, 3) . '-' .
                substr($number, 5, 2) . '-' . substr($number, 7, 2),
        ]);
    }
}

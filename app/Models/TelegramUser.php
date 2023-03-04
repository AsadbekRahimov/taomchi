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
        'name',
        'username',
        'customer_id',
    ];

    public static function createNewUser($chat_id, $from, $number)
    {
        $first_name = $from->has('first_name') ? $from->first_name : '';
        $last_name = $from->has('last_name') ? $from->last_name : '';
        $username = $from->has('username') ? $from->username : null;
        self::query()->create([
            'telegram_id' => $chat_id,
            'phone' => '(' . substr($number, 0, 2) . ') ' . substr($number, 2, 3) . '-' .
                substr($number, 5, 2) . '-' . substr($number, 7, 2),
            'name' => $first_name . ' ' . $last_name,
            'username' => $username,
        ]);
    }
}

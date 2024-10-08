<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Place extends Model
{
    use HasFactory;
    use AsSource, Filterable, Attachable;

    protected $fillable = [
        'name',
        'telegram_message_id'
    ];

    public function customers()
    {
        return $this->hasMany(Customer::class, 'place_id', 'id');
    }

    public function telegramUsers()
    {
        return $this->hasMany(TelegramUser::class, 'place_id', 'id');
    }
}

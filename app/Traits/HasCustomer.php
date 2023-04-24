<?php

namespace App\Traits;

use App\Models\Customer;
use App\Models\TelegramUser;

trait HasCustomer
{
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function telegram()
    {
        return $this->belongsTo(TelegramUser::class, 'telegram_user_id', 'id');
    }


    public function getCustomerNameAttribute($value)
    {
        return $this->customer_id ? $this->customer->name : $this->telegram->phone;
    }
}

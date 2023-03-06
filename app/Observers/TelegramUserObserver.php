<?php

namespace App\Observers;

use App\Models\Customer;
use App\Models\TelegramUser;
use App\Services\TelegramNotify;

class TelegramUserObserver
{
    /**
     * Handle the TelegramUser "created" event.
     *
     * @param  \App\Models\TelegramUser  $telegramUser
     * @return void
     */
    public function created(TelegramUser $telegramUser)
    {
        $client = Customer::query()->where('phone', $telegramUser->phone)->first();
        if ($client) {
            $telegramUser->update([
                'customer_id' => $client->id
            ]);
            TelegramNotify::registerClient($telegramUser, 'old_client');
        } else {
            TelegramNotify::registerClient($telegramUser, 'new_client');
        }
    }
}

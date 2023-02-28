<?php

namespace App\Http\Controllers;

use App\Models\TelegramUser;
use Illuminate\Http\Request;
use MongoDB\Driver\Session;
use Psy\Util\Str;
use Telegram\Bot\Api;


class TelegramBotController extends Controller
{
    public function setWebhook()
    {
        $telegram = new Api(config('telegram.bots.taomchi_bot.token'));
        $response = $telegram->setWebhook([
            'url' => 'https://iceboy.agro.uz/telegram/bot/webhook'
        ]);
        return $response;
    }

    public function webhook()
    {
        $telegram = new Api(config('telegram.bots.taomchi_bot.token'));
        $updates = $telegram->getWebhookUpdate();

        $message = $updates->getMessage();

        $csrf_token = \Illuminate\Support\Str::random(32);
        \Illuminate\Support\Facades\Session::put('_token', $csrf_token);


        if ($message && $message->getText() == '/start') {
            $telegram->sendMessage([
                'chat_id' => $message->getChat()->getId(),
                'text' => 'Hello Taomchi User',
                'csrf_token' => $csrf_token,
            ]);
        }
    }

    public function register(Request $request)
    {
        $telegram = new Api(config('telegram.bots.taomchi_bot.token'));

        // get User from request
        $telegram_id = $request->input('telegram_id');
        $name = $request->input('name');

        // check registration user
        $user = TelegramUser::query()->where('telegram_id', $telegram_id)->first();
        if ($user){
            $telegram->sendMessage([
                'chat_id' => $user->telegram_id,
                'text' => 'Siz royhatdan otib bolgansiz',
            ]);
        } else {
            // register user if not registered
            $user = TelegramUser::query()->create([
                'telegram_id' => $telegram_id,
                'name' => $name,
            ]);

            $telegram->sendMessage([
                'chat_id' => $user->telegram_id,
                'text' => 'Siz muaffaqiyatli royhatdan otdingiz',
            ]);
        }

    }

    public function login(Request $request)
    {
        $telegram = new Api(config('telegram.bots.taomchi_bot.token'));
        $telegram_id = $request->input('telegram_id');
        // check user for authenticated
        $user = TelegramUser::query()->where('telegram_id', $telegram_id)->first();

        if (!$user) {
            $telegram->sendMessage([
                'chat_id' => $user->telegram_id,
                'text' => 'Siz royhatdan otmagansiz!',
            ]);
        } else {
            //auth()->login($user);
            $telegram->sendMessage([
                'chat_id' => $user->telegram_id,
                'text' => 'Muaffaqiyatli tizimga kirdingiz!'
            ]);
        }
    }

}

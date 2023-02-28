<?php

namespace App\Http\Controllers;

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
            'url' => 'https://9dd4-92-63-204-23.in.ngrok.io/telegram/bot/webhook'
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


}

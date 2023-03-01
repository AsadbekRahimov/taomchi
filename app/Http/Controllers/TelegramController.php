<?php

namespace App\Http\Controllers;

use App\Commands\CancelCommand;
use App\Commands\CartCommand;
use App\Commands\CheckoutCommand;
use App\Commands\MenuCommand;
use App\Commands\PhoneCommand;
use App\Commands\StartCommand;
use App\Models\Product;
use App\Models\TelegramOrder;
use App\Models\TelegramOrderItem;
use App\Models\TelegramUser;
use App\Models\TelegramUserCard;
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\CallbackQuery;


class TelegramController extends Controller
{

    public function run()
    {
        $telegram = new Api('6019873449:AAFRex1zM2BltwZOigWq8aMOAKL5qUwFDHk');

        $commands = [
            StartCommand::class,
            PhoneCommand::class,
            CancelCommand::class,
            MenuCommand::class,
            CartCommand::class,
            CheckoutCommand::class,
        ];

        foreach ($commands as $command)
        {
            $telegram->addCommand(new $command);
        }

        /*$telegram->on('callback_query', function (CallbackQuery $callbackQuery) use ($telegram) {
            $telegram->getCommandBus()->handler($callbackQuery);
        });*/

        $telegram->commandsHandler(true);
    }

    public function setWebHook()
    {
        $telegram = new Api('6019873449:AAFRex1zM2BltwZOigWq8aMOAKL5qUwFDHk');

        $response = $telegram->setWebhook([
            'url' => 'https://iceboy.agro.uz/bot'
        ]);

        return $response;
    }

}

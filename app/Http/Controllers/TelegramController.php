<?php

namespace App\Http\Controllers;

use App\Commands\CancelCommand;
use App\Commands\CartCommand;
use App\Commands\CheckoutCommand;
use App\Commands\MenuCommand;
use App\Commands\PhoneCommand;
use App\Commands\StartCommand;
use Telegram\Bot\Api;


class TelegramController extends Controller
{

    public function run()
    {
        $telegram = new Api('6019873449:AAFRex1zM2BltwZOigWq8aMOAKL5qUwFDHk');

        $this->saveContact($telegram);

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
            'url' => 'https://14ee-185-139-137-124.eu.ngrok.io/bot'
        ]);

        return $response;
    }

    private function saveContact($telegram)
    {
        $message = $telegram->getWebhookUpdate()->getMessage();
        $contact = $message->getContact();
        if (!empty($contact))
        {
            $number = $contact->getPhoneNumber();
            $telegram->sendMessage([
                'chat_id' => $message->getChat()->getId(),
                'text' => $number,
            ]);
        }
    }

}

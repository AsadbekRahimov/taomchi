<?php

namespace App\Http\Controllers;

use App\Commands\CancelCommand;
use App\Commands\CartCommand;
use App\Commands\CheckoutCommand;
use App\Commands\MenuCommand;
use App\Commands\PhoneCommand;
use App\Commands\StartCommand;
use App\Models\TelegramUser;
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

    private function saveContact($telegram)
    {
        $message = $telegram->getWebhookUpdate()->getMessage();
        $chat_id = $message->getChat()->getId();
        $contact = $message->getContact();

        if (!empty($contact))
        {
            $number = $contact->getPhoneNumber();
            if (strlen($number) != 13)
            {
                $telegram->sendMessage([
                    'chat_id' => $chat_id,
                    'text' => 'Сизнинг телефон рақамингиз текширувдан ўтмади!',
                ]);
            } else {
                TelegramUser::query()->create([
                    'telegram_id' => $chat_id,
                    'phone' => $number,
                ]);
                $telegram->sendMessage([
                    'chat_id' => $chat_id,
                    'text' => 'Сиз муаффақиятли рўйҳатдан ўтдингиз.',
                ]);
            }
        }
    }

}

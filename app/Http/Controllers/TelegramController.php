<?php

namespace App\Http\Controllers;

use App\Commands\CancelCommand;
use App\Commands\CartCommand;
use App\Commands\CheckoutCommand;
use App\Commands\MenuCommand;
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
            $user = TelegramUser::query()->where('telegram_id', $chat_id)->first();
            if ($user) {
                $telegram->sendMessage(['chat_id' => $chat_id, 'text' => 'Сиз телефон рақаминигизни киритиб бўлганисиз!']);
            }elseif (strlen($number) != 13)
            {
                $telegram->sendMessage(['chat_id' => $chat_id, 'text' => 'Сизнинг телефон рақамингиз текширувдан ўтмади!']);
            } else {
                TelegramUser::createNewUser($chat_id, substr($number, 4));
                $telegram->sendMessage(['chat_id' => $chat_id, 'text' => 'Сиз муаффақиятли рўйҳатдан ўтдингиз.']);
            }
        }

    }

}

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

        $this->proccessCallbackData($telegram);
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
            'url' => 'https://f971-185-139-137-1.eu.ngrok.io/bot'
        ]);

        return $response;
    }

    private function saveContact($telegram)
    {
        $message = $telegram->getWebhookUpdate()->getMessage();
        if ($message->has('contact'))
        {
            $number = $message->contact->phone_number;
            $chat_id = $message->contact->user_id;

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

    private function proccessCallbackData(Api $telegram)
    {
        if($telegram->getWebhookUpdate()->has('callback_query')) {

            $callBackData = $telegram->getWebhookUpdate()->callbackQuery->data;

            $countKeyboard = $this->getCountKeyboard($callBackData);

            $telegram->sendMessage([
                'chat_id' => $telegram->getWebhookUpdate()->getMessage()->getChat()->getId(),
                'text' => $telegram->getWebhookUpdate()->callbackQuery->data,
                'reply_markup' => json_encode([
                    'inline_keyboard' => $countKeyboard,
                ]),
            ]);
        }
    }

    private function getCountKeyboard($callBackData)
    {
        return  [
            [
                [
                    'text' => '1',
                    'callback_data' => 'add_1_' . $callBackData
                ],
                [
                    'text' => '2',
                    'callback_data' => 'add_2_' . $callBackData
                ],
                [
                    'text' => '3',
                    'callback_data' => 'add_3_' .$callBackData
                ],
            ],
            [
                [
                    'text' => '4',
                    'callback_data' => 'add_4_' . $callBackData
                ],
                [
                    'text' => '5',
                    'callback_data' => 'add_5_'. $callBackData
                ],
                [
                    'text' => '6',
                    'callback_data' => 'add_6_' . $callBackData
                ],
            ]
        ];
    }

}

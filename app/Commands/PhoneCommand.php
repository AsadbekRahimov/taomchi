<?php

namespace App\Commands;

use App\Models\TelegramUser;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class PhoneCommand extends Command
{
    protected $name = 'phone';

    protected $description = 'Телефон рақамингизни киритинг';

    public function handle()
    {
        $message = $this->getUpdate()->getMessage();
        $chat_id = $message->getChat()->getId();

        $user = TelegramUser::query()->where('telegram_id', $chat_id)->first();
        if ($user) {
            $this->telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => 'Сиз телефон рақаминигизни киритиб бўлганисиз!'
            ]);
            return;
        }

        $this->replyWithMessage([
           'text' => 'Телефон рақамингизни киритинг.',
           'reply_markup' => json_encode([
               'keyboard' => [
                    [
                        [
                            'text' => 'Телефон рақамни юбориш',
                            'request_contact' => true,
                        ],
                    ],
                    [
                        [
                            'text' => 'Бекор қилиш',
                        ],
                    ],
               ],
               'resize_keyboard' => true,
               'one_time_keyboard' => true,
           ]),
        ]);

        $update = $this->getUpdate();
        $contact = $update->getMessage()->getContact();
        $aa = json_encode($contact);
        $this->telegram->sendMessage([
            'chat_id' => $chat_id,
            'text' => $aa,
        ]);

        if (!empty($contact)) {
            $phone_number = $contact->getPhoneNumber();

            // сохраняем номер телефона в базе данных
            // ...

            $this->telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => 'Сиз муаффақиятли ройҳатдан ўтдингиз'
            ]);
        }
    }
}

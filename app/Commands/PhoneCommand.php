<?php

namespace App\Commands;

use App\Models\TelegramUser;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class PhoneCommand extends Command
{
    protected $name = 'phone';

    protected $description = 'Телефон рақамингизни киритинг';

    public function handle($arguments)
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();

        $user = TelegramUser::query()->where('telegram_id', $chat_id)->first();
        if ($user) {
            $this->telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => 'Сиз телефон рақаминигизни киритиб бўлганисиз!'
            ]);
            return;
        }

        $this->telegram->sendMessage([
           'chat_id' => $chat_id,
           'text' => 'Телефон рақамингизни киритинг.',
           'reply' => json_encode([
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
    }
}

<?php

namespace App\Commands;

use App\Models\TelegramUser;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class StartCommand extends Command
{
    protected $name = 'start';

    protected $description = 'Мулоқотни бошлаш';

    public function handle()
    {
        $message = $this->getUpdate()->getMessage();
        $chat_id = $message->getChat()->getId();

        $user = TelegramUser::query()->where('telegram_id', $chat_id)->first();

        $this->replyWithChatAction(['action' => Actions::TYPING]);

        if (!$user) {
            $this->replyContactNumber();
        } else {
            $this->telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => 'Таомчига хуш келибсиз.'
            ]);
        }
    }

    private function replyContactNumber()
    {
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
    }
}

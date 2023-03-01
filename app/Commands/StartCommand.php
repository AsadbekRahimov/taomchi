<?php

namespace App\Commands;

use App\Models\TelegramUser;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class StartCommand extends Command
{
    protected $name = 'start';

    protected $description = 'Мулоқотни бошлаш';

    public function handle($arguments)
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();

        $user = TelegramUser::query()->where('telegram_id', $chat_id)->first();
        if (!$user) {
            $this->telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => 'Aввал телефон рақамингизни киритишингиз керак!'
            ]);
        } else {
            $this->telegram->sendMessage([
                'chat_id', $chat_id,
                'text' => 'Таомчига хуш келибсиз.'
            ]);
        }

        $this->replyWithChatAction(['action' => Actions::TYPING]);
    }
}

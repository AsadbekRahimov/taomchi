<?php

namespace App\Commands;

use App\Models\TelegramUser;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class CancelCommand extends Command
{
    protected $name = 'cancel';

    protected $description = 'Жорий амални бекор қилиш';

    public function handle()
    {
        $this->telegram->sendMessage([
            'chat_id' => $this->getUpdate()->getMessage()->getChat()->getId(),
            'text' => 'Aмал бекор қилинди!'
        ]);

        $this->replyWithChatAction(['action' => Actions::TYPING]);
    }
}

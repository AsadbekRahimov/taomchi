<?php

namespace App\Commands;

use App\Models\Product;
use App\Models\TelegramUser;
use App\Models\TelegramUserCard;
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Objects\CallbackQuery;

class CartCommand extends Command
{
    protected $name = 'cart';

    protected $description = 'Саватчани кўрсатиш';

    public function handle()
    {
        $message = $this->getUpdate()->getMessage();
        $chat_id = $message->getChat()->getId();

        $user = TelegramUser::query()->where('telegram_id', $chat_id)->first();

        $this->replyWithChatAction(['action' => Actions::TYPING]);

        if (!$user) {
            $this->replyContactNumber();
            return;
        }

        $carts = TelegramUserCard::query()->with(['product'])
            ->where('telegram_user_id', $user->id)->get();

        if ($carts->isEmpty()) {
            $this->telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => 'Саватчада махсулотлар мавжуд эмас!',
            ]);
            return;
        }

        $message = "Саватчадаги махсулотлар:  \n";
        foreach ($carts as $cart) {
            $message .= $cart->product->name . ' (' . number_format($cart->product->one_price) .  ')' . ' x ' .
                $cart->count . ' = ' . number_format($cart->product->one_price * $cart->count);
        }

        $this->telegram->sendMessage([
            'chat_id' => $chat_id,
            'text' => $message,
        ]);
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

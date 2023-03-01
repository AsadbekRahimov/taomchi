<?php

namespace App\Commands;

use App\Models\Product;
use App\Models\TelegramUser;
use App\Models\TelegramUserCard;
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Objects\CallbackQuery;

class MenuCommand extends Command
{
    protected $name = 'menu';

    protected $description = 'Махсулотлар рўйҳатини корсатиш';

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
            return;
        }

        $text = 'Махсулотни танланг: ';
        $keyboard = [];
        $products = Cache::rememberForever('products', function () {
            return \App\Models\Product::query()->get();
        });

        foreach ($products as $product) {
            $keyboard[] = [
                [
                    'text' => $product->name . ' - ' . number_format($product->one_price) . ' сўм',
                    'callback_data' => 'product_' . $product->id,
                ],
            ];
        }

        $this->telegram->sendMessage([
            'chat_id' => $chat_id,
            'text' => $text,
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard,
            ]),
        ]);

        $this->replyWithChatAction(['action' => Actions::TYPING]);
    }

    /**
     * Handle callback queries.
     *
     * @param CallbackQuery $callbackQuery
     */
    public function callbackQueryHandler(CallbackQuery $callbackQuery)
    {
        $data = $callbackQuery->getData();

        if (str_starts_with($data, 'product_')) {
            $product_id = substr($data, 8);
            $chat_id = $this->getUpdate()->getCallbackQuery()->getMessage()->getChat()->getId();
            $user = TelegramUser::query()->where('telegram_id', $chat_id)->find();

            $cart = TelegramUserCard::query()->where('telegram_user_id', $user->id)
                ->where('product_id', $product_id)->first();

            if (!$cart) {
                TelegramUserCard::query()->create([
                    'telegram_user_id' => $user->id,
                    'product_id' => $product_id,
                    'count' => 1
                ]);
            } else {
                $cart->increment('count');
            }
        }
    }
}

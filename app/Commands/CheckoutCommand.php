<?php

namespace App\Commands;

use App\Models\Product;
use App\Models\TelegramOrder;
use App\Models\TelegramOrderItem;
use App\Models\TelegramUser;
use App\Models\TelegramUserCard;
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Objects\CallbackQuery;

class CheckoutCommand extends Command
{
    protected $name = 'cart';

    protected $description = 'Саватчани кўрсатиш';

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
            $this->telegram->triggerCommand('phone');
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

        $total_price = 0;
        foreach ($carts as $cart) {
            $total_price += $cart->product->one_price * $cart->count;
        }

        $order = TelegramOrder::query()->create([
            'user_id' => $user->id,
            'price' => $total_price
        ]);

        foreach ($carts as $cart)
        {
            TelegramOrderItem::query()->create([
                'order_id' => $order->id,
                'product_id' => $cart->product_id,
                'count' => $cart->count,
                'price' => $cart->product->one_price,
            ]);

            $cart->delete();
        }

        $this->telegram->sendMessage([
            'chat_id' => $chat_id,
            'text' => 'Буюртма расмийлаштирилди!'
        ]);
    }
}

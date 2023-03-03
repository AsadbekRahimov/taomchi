<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\TelegramOrder;
use App\Models\TelegramOrderItem;
use App\Models\TelegramUser;
use App\Models\TelegramUserCard;

use Telegram\Bot\Api;


class TelegramBotController extends Controller
{
    protected $telegram;

    public function setWebhook()
    {
        $telegram = new Api(config('telegram.bots.taomchi_bot.token'));
        $response = $telegram->setWebhook([
            'url' => 'https://46f2-185-139-137-124.eu.ngrok.io/telegram/bot/webhook'
        ]);
        return $response;
    }

    public function webhook()
    {
        $this->telegram = new Api(config('telegram.bots.taomchi_bot.token'));
        $updates = $this->telegram->getWebhookUpdate();

        $message = $updates->getMessage();

        $csrf_token = \Illuminate\Support\Str::random(32);
        \Illuminate\Support\Facades\Session::put('_token', $csrf_token);


        if ($message && $message->getText() == '/start') {
            $this->telegram->sendMessage([
                'chat_id' => $message->getChat()->getId(),
                'text' => 'Hello Taomchi User',
                //'csrf_token' => $csrf_token,
            ]);
        }
    }

    /*public function __construct(Api $telegram)
    {
        $this->telegram = $telegram;
    }

    public function webhook()
    {
        $this->telegram->setWebhook([
            'url' => route('telegram.webhook')
        ]);
    }

    public function handle()
    {
        $updates = $this->telegram->getWebhookUpdate();

        foreach ($updates as $update)
        {
            $message = $update->getMessage();
            $chat_id = $update->getChat()->getId();

            switch ($message->getText()) {
                case '/start':
                    $this->startCommand($chat_id);
                    break;
                case '/menu':
                    $this->menuCommand($chat_id);
                    break;
                case '/cart':
                    $this->cartCommand($chat_id);
                    break;
                default:
                    $this->unknownCommand($chat_id);
                    break;
            }
        }

    }*/

    private function startCommand($chat_id)
    {
        return $this->telegram->sendMessage([
            'chat_id' => $chat_id,
            'text' => 'Salom!',
        ]);
    }

    private function menuCommand($chat_id)
    {
    }

    private function cartCommand($chat_id)
    {
    }

    private function unknownCommand($chat_id)
    {
        return $this->telegram->sendMessage([
            'chat_id' => $chat_id,
            'text' => 'Бундай амал мавжуд емас!',
        ]);
    }

    protected function savePhoneNUmber($chat_id, $phone_number)
    {
        $user = TelegramUser::query()->where('phone', $phone_number)->first();

        if (!$user){
            TelegramUser::query()->create([
                'telegram_id' => $chat_id,
                'phone' => $phone_number
            ]);
        }

        $this->telegram->sendMessage([
           'chat_id' => $chat_id,
           'text' => 'Рахмат. Энди сиз буюртма беришингиз мумкин!'
        ]);
    }

    protected function listProducts($chat_id)
    {
        $products = \App\Models\Product::query()->get();

        if ($products->count() > 0) {
            $text = "Махсулотлар рўйҳати: \n";
            foreach($products as $product) {
                $text .= $product->id . ': ' . $product->name . ' - ' . $product->one_price;
            }

            $this->telegram->sendMessage([
               'chat_id' => $chat_id,
               'text' => $text,
            ]);
        } else {
            $this->telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => 'Дўконда махсулотлар мавжуд емас!',
            ]);
        }
    }

    protected function addToCard($chat_id, $product_id)
    {
        $user = TelegramUser::query()->where('telegram_id', $chat_id)->first();
        if (!$user) {
            $this->telegram->sendMessage([
               'chat_id' => $chat_id,
               'text' => 'Aввал телефон рақамингизни киритишингиз керак!'
            ]);
            return;
        }

        $product = Product::query()->find($product_id);

        if (!$product) {
            $this->telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => 'Махсулот топилмади',
            ]);
            return;
        }

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

        $this->telegram->sendMessage([
            'telegram_id' => $chat_id,
            'text' => $product->name . ' - саватга сақланди!'
        ]);
    }

    protected function showCard($chat_id)
    {
        $user = TelegramUser::query()->where('telegram_id', $chat_id)->first();
        if (!$user) {
            $this->telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => 'Aввал телефон рақамингизни киритишингиз керак!'
            ]);
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

    protected function checkout($chat_id)
    {
        $user = TelegramUser::query()->where('telegram_id', $chat_id)->first();

        if (!$user) {
            $this->telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => 'Aввал телефон рақамингизни киритишингиз керак!'
            ]);
            return;
        }

        $carts = TelegramUserCard::query()->with(['product'])->where('telegram_user_id', $user->id)->get();

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

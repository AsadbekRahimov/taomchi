<?php

namespace App\Services;

use App\Models\TelegramUser;
use Carbon\Carbon;
use Telegram\Bot\Api;

class BotUserNotify
{

    public static function acceptOrder($telegram_user_id, $order_id) {
        $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
        return $telegram->sendMessage([
            'chat_id' => $telegram_user_id,
            'text' => 'Сизнинг #' . $order_id . ' рақамли буюртмангиз тасдиқланди. Буюртма етказиб бериш учун тайёрланмоқда.',
        ]);
    }

    public static function deleteOrder($telegram_user_id,  $order_id, $reason)
    {
        $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
        $text = 'Сизнинг #' . $order_id . ' рақамли буюртмангиз админ томонидан бекор қилинди. ';
        if (!is_null($reason)) $text .= "\nБекор қилиш сабаби: " . $reason . "\n";
        $text .= "Мурожат учун телефон раками: \n+998917070907 +998770150907";
        return $telegram->sendMessage([
            'chat_id' => $telegram_user_id,
            'text' => $text,
        ]);
    }
}

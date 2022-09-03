<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class TelegramNotify
{
    public const CHAT_TYPE = [
        'all' => '1678836162',
        'sale' => '1587673814',
        'order' => '1784671982',
    ];

    public static function sendMessage($text, $chat_type, $caption = null)
    {
        $chat_id = self::CHAT_TYPE[$chat_type];
        $url = "https://api.telegram.org/bot5092164055:AAERH5aY3eVnfZucYrK-z63af-2MI5o2IQ8/sendMessage?chat_id=-100" . $chat_id;

        $message = $text . "\r\n";

        if (!is_null(Auth::user()->branch_id))
            $message .= '#' . Str::slug(Auth::user()->branch->name, '_');

        if (!is_null($caption))
            $message .= "\r\n" . '#' . $caption;

        $post_fields = [
            'chat_id' => '-100' . $chat_id,
            'text' => $message,
            'parse_mode' => "HTML",
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type:multipart/form-data"
        ));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        $output = curl_exec($ch);
        return $output;
    }
}

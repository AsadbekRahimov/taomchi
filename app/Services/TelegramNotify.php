<?php

namespace App\Services;

use Carbon\Carbon;

class TelegramNotify
{
    public const CHAT_TYPE = [
        'all' => '1868717701',
        'order' => '1821427006',
        'tg_order' => '1803552911',
    ];

    public static function sendMessage($text, $caption = null)
    {
        $chat_id = self::CHAT_TYPE['order'];
        $url = "https://api.telegram.org/bot5092164055:AAERH5aY3eVnfZucYrK-z63af-2MI5o2IQ8/sendMessage?chat_id=-100" . $chat_id;

        $message = $text . "\r\n";

        if (!is_null($caption))
            $message .= "\r\n" . '#' . $caption;

        $post_fields = [
            'chat_id' => '-100' . $chat_id,
            'text' => $message,
            'parse_mode' => "HTML",
        ];

        return self::send($url, $post_fields);
    }

    public static function sendReport($message)
    {
        $url = "https://api.telegram.org/bot5092164055:AAERH5aY3eVnfZucYrK-z63af-2MI5o2IQ8/sendDocument?chat_id=-100" . self::CHAT_TYPE['all'];

        $post_fields = [
            'chat_id' => '-100' . self::CHAT_TYPE['all'],
            'document' => new \CURLFile(storage_path('app/report.xlsx'), 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'Kunlik-xisobot_'. Carbon::now()->format('Y-m-d').'.xlsx'),
            'caption' => $message . "\r\n" . '#Хисобот ' . date('Y-m-d') . " холатига",
            'parse_mode' => "HTML",
        ];

        return self::send($url, $post_fields);
    }

    private static function send(string $url, array $post_fields)
    {
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

    public static function newClient($number)
    {
        $url = "https://api.telegram.org/bot5092164055:AAERH5aY3eVnfZucYrK-z63af-2MI5o2IQ8/sendMessage?chat_id=-100" . self::CHAT_TYPE['tg_order'];
        $message = 'Янги мижоз ботга уланди!' . "\r\n" . 'Телефон раками: ' . $number;

        $post_fields = [
            'chat_id' => '-100' . self::CHAT_TYPE['tg_order'],
            'text' => $message,
            'parse_mode' => "HTML",
        ];

        return self::send($url, $post_fields);
    }

    public static function registerClient($client)
    {
        $url = "https://api.telegram.org/bot5092164055:AAERH5aY3eVnfZucYrK-z63af-2MI5o2IQ8/sendMessage?chat_id=-100" . self::CHAT_TYPE['tg_order'];
        $message = 'Мижозимиз бот га уланди.' .
            "\r\n" . 'Исми: ' . $client->name .
            "\r\n" . 'Телефон раками: ' . $client->phone;

        $post_fields = [
            'chat_id' => '-100' . self::CHAT_TYPE['tg_order'],
            'text' => $message,
            'parse_mode' => "HTML",
        ];

        return self::send($url, $post_fields);
    }
}

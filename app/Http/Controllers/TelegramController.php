<?php

namespace App\Http\Controllers;

use App\Commands\CartCommand;
use App\Commands\CheckoutCommand;
use App\Commands\MenuCommand;
use App\Commands\StartCommand;
use App\Models\TelegramUser;
use App\Models\TelegramUserCard;
use App\Services\CacheService;
use Telegram\Bot\Actions;
use Telegram\Bot\Answers\Answerable;
use Telegram\Bot\Api;


class TelegramController extends Controller
{

    protected $telegram;

    public function setWebHook()
    {
        $telegram = new Api('6019873449:AAFRex1zM2BltwZOigWq8aMOAKL5qUwFDHk');

        $response = $telegram->setWebhook([
            'url' => 'https://9d96-188-113-206-162.in.ngrok.io/bot'
        ]);

        return $response;
    }

    public function run()
    {
        $this->telegram = new Api('6019873449:AAFRex1zM2BltwZOigWq8aMOAKL5qUwFDHk');
        $this->saveContact();
        $this->proccessCallbackData();
        $this->proccessCommands();

        $this->telegram->commandsHandler(true);
    }

    private function proccessCommands()
    {
        $text = $this->telegram->getWebhookUpdate()->getMessage()->getText();

        if (in_array($text, ['/start', 'Бошига қайтиш']))
            $this->startCommand();
        elseif (in_array($text, ['/menu', 'Махсулотлар рўйҳатини кўриш']))
            $this->menuCommand();
    }

    private function startCommand()
    {
        $chat_id = $this->telegram->getWebhookUpdate()->getMessage()->getChat()->getId();
        $this->telegram->sendChatAction(['chat_id' => $chat_id, 'action' => Actions::TYPING]);
        $this->startChat($chat_id);
    }

    private function menuCommand()
    {
        $chat_id = $this->telegram->getWebhookUpdate()->getMessage()->getChat()->getId();
        $user = TelegramUser::query()->where('telegram_id', $chat_id)->first();
        $this->telegram->sendChatAction(['chat_id' => $chat_id, 'action' => Actions::TYPING]);
        $user ? $this->replyMenuList($chat_id) : $this->replyContactNumber($chat_id);
    }

    private function saveContact()
    {
        $message = $this->telegram->getWebhookUpdate()->getMessage();
        if ($message->has('contact'))
        {
            $number = $message->contact->phone_number;
            $chat_id = $message->contact->user_id;

            $user = TelegramUser::query()->where('telegram_id', $chat_id)->first();
            if ($user) {
                $this->telegram->sendMessage(['chat_id' => $chat_id, 'text' => 'Сиз телефон рақаминигизни киритиб бўлганисиз!']);
            }elseif (strlen($number) != 13)
            {
                $this->telegram->sendMessage(['chat_id' => $chat_id, 'text' => 'Сизнинг телефон рақамингиз текширувдан ўтмади!']);
            } else {
                TelegramUser::createNewUser($chat_id, $message->from, substr($number, 4));
                $this->telegram->sendMessage(['chat_id' => $chat_id, 'text' => 'Сиз муаффақиятли рўйҳатдан ўтдингиз.']);
                $this->startChat($chat_id);
            }
        }

    }

    private function proccessCallbackData()
    {
        if($this->telegram->getWebhookUpdate()->has('callback_query')) {
            $chat_id = $this->telegram->getWebhookUpdate()->getMessage()->getChat()->getId();
            $user = TelegramUser::query()->where('telegram_id', $chat_id)->first();
            $callBackData = $this->telegram->getWebhookUpdate()->callbackQuery->data;

            if (str_starts_with($callBackData, 'product_')) {
                $product = CacheService::getProducts()->find(explode('_', $callBackData)[1]);

                /*$cart = TelegramUserCard::query()->where('telegram_user_id', $user->id)
                    ->where('product_id', $product_id)->first();

                if (!$cart) {
                    TelegramUserCard::query()->create([
                        'telegram_user_id' => $user->id,
                        'product_id' => $product_id,
                        'count' => 1
                    ]);
                } else {
                    $cart->increment('count');
                }*/
            }
            $countKeyboard = $this->getCountKeyboard($callBackData);

            $this->telegram->sendMessage([
                'chat_id' => $this->telegram->getWebhookUpdate()->getMessage()->getChat()->getId(),
                'text' => $this->telegram->getWebhookUpdate()->callbackQuery->data,
                'reply_markup' => json_encode([
                    'inline_keyboard' => $countKeyboard,
                ]),
            ]);
        }
    }

    private function getCountKeyboard($callBackData)
    {
        return  [
            [
                [
                    'text' => '1',
                    'callback_data' => 'add_1_' . $callBackData
                ],
                [
                    'text' => '2',
                    'callback_data' => 'add_2_' . $callBackData
                ],
                [
                    'text' => '3',
                    'callback_data' => 'add_3_' .$callBackData
                ],
            ],
            [
                [
                    'text' => '4',
                    'callback_data' => 'add_4_' . $callBackData
                ],
                [
                    'text' => '5',
                    'callback_data' => 'add_5_'. $callBackData
                ],
                [
                    'text' => '6',
                    'callback_data' => 'add_6_' . $callBackData
                ],
            ],
            [
                [
                    'text' => '7',
                    'callback_data' => 'add_7_' . $callBackData
                ],
                [
                    'text' => '8',
                    'callback_data' => 'add_8_'. $callBackData
                ],
                [
                    'text' => '8',
                    'callback_data' => 'add_8_' . $callBackData
                ],
            ]
        ];
    }

    private function replyContactNumber($chat_id)
    {
        $this->telegram->sendMessage([
            'chat_id' => $chat_id,
            'text' => 'Телефон рақамингизни киритинг.',
            'reply_markup' => json_encode([
                'keyboard' => [
                    [
                        [
                            'text' => 'Телефон рақамни юбориш',
                            'request_contact' => true,
                        ]
                    ],
                    [
                        [
                            'text' => 'Бошига қайтиш',
                        ]
                    ]
                ],
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ]),
        ]);
    }

    private function startChat($chat_id)
    {
        $this->telegram->sendMessage([
            'chat_id' => $chat_id,
            'text' => 'Таомчига хуш келибсиз. Менюдан керакли амални танланг!',
            'reply_markup' => json_encode([
                'keyboard' => [
                    [
                        [
                            'text' => 'Махсулотлар рўйҳатини кўриш',
                        ]
                    ],
                    [
                        [
                            'text' => 'Саватни кўриш'
                        ],
                        [
                            'text' => 'Буюртмани якунлаш'
                        ]
                    ]
                ],
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ]),
        ]);
    }

    private function replyMenuList($chat_id)
    {
        $text = 'Махсулотни танланг: ';
        $keyboard = [];

        foreach (CacheService::getProducts() as $product) {
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
    }

}

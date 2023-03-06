<?php

namespace App\Http\Controllers;

use App\Models\TelegramOrder;
use App\Models\TelegramOrderItem;
use App\Models\TelegramUser;
use App\Models\TelegramUserCard;
use App\Services\CacheService;
use Telegram\Bot\Actions;
use Telegram\Bot\Api;


class TelegramController extends Controller
{

    protected $telegram, $user, $chat_id;

    public function __construct()
    {
        $this->telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
    }

    public function setWebHook()
    {
        return $this->telegram->setWebhook([
            'url' => env('APP_URL') . '/bot'
        ]);
    }

    public function run()
    {
        $this->chat_id = $this->telegram->getWebhookUpdate()->getMessage()->getChat()->getId();
        $this->user = TelegramUser::query()->where('telegram_id', $this->chat_id)->first();

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
        elseif (in_array($text, ['/cart', 'Саватни кўриш']))
            $this->cardCommand();
        elseif (in_array($text, ['/checkout', 'Буюртмани якунлаш']))
            $this->checkoutCommand();
    }

    private function startCommand()
    {
        $this->telegram->sendChatAction(['chat_id' => $this->chat_id, 'action' => Actions::TYPING]);
        $this->startChat();
    }

    private function menuCommand()
    {
        $this->telegram->sendChatAction(['chat_id' => $this->chat_id, 'action' => Actions::TYPING]);
        $this->user ? $this->replyMenuList() : $this->replyContactNumber();
    }

    private function cardCommand()
    {
        $this->telegram->sendChatAction(['chat_id' => $this->chat_id, 'action' => Actions::TYPING]);
        $this->user ? $this->showCartList() : $this->replyContactNumber();
    }

    private function checkoutCommand()
    {
        $this->telegram->sendChatAction(['chat_id' => $this->chat_id, 'action' => Actions::TYPING]);
        $this->user ? $this->finishOrder() : $this->replyContactNumber();
    }

    private function saveContact()
    {
        $message = $this->telegram->getWebhookUpdate()->getMessage();
        if ($message->has('contact'))
        {
            $number = $message->contact->phone_number;

            $user = TelegramUser::query()->where('telegram_id', $this->chat_id)->first();
            if ($user) {
                $this->telegram->sendMessage(['chat_id' => $this->chat_id, 'text' => 'Сиз телефон рақаминигизни киритиб бўлганисиз!']);
            }elseif (strlen($number) != 13)
            {
                $this->telegram->sendMessage(['chat_id' => $this->chat_id, 'text' => 'Сизнинг телефон рақамингиз текширувдан ўтмади!']);
            } else {
                TelegramUser::createNewUser($this->chat_id, $message->from, substr($number, 4));
                $this->telegram->sendMessage(['chat_id' => $this->chat_id, 'text' => 'Сиз муаффақиятли рўйҳатдан ўтдингиз.']);
                $this->startChat();
            }
        }

    }

    private function proccessCallbackData()
    {
        if($this->telegram->getWebhookUpdate()->has('callback_query')) {
            $callBackData = $this->telegram->getWebhookUpdate()->callbackQuery->data;

            if (str_starts_with($callBackData, 'product_'))
                $this->selectProduct($callBackData);
            elseif (str_starts_with($callBackData, 'add_'))
                $this->addProductToCart($callBackData);
            elseif ($callBackData == 'checkout')
                $this->checkoutCommand();
            elseif ($callBackData == 'cart_clear')
                $this->cartClear();
            elseif ($callBackData == 'delete_product')
                $this->cartProductsList();
            elseif (str_starts_with($callBackData, 'clear_'))
                $this->deleteProductFromCard($callBackData);

            /*$this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => $callBackData,
            ]);*/
        }
    }

    private function replyContactNumber()
    {
        $this->telegram->sendMessage([
            'chat_id' => $this->chat_id,
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

    private function startChat()
    {
        $this->telegram->sendMessage([
            'chat_id' => $this->chat_id,
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

    private function replyMenuList()
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
            'chat_id' => $this->chat_id,
            'text' => $text,
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard,
            ]),
        ]);
    }

    private function selectProduct($callBackData)
    {
        $countKeyboard = $this->getCountKeyboard($callBackData);
        $product = CacheService::getProducts()->find(explode('_', $callBackData)[1]);
        if ($product) {
            $product_name = $product->name . ' - ' .
                number_format($product->one_price) . " сўм/дона\nМиқдорини киритинг:";

            $this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => $product_name,
                'reply_markup' => json_encode([
                    'inline_keyboard' => $countKeyboard,
                ]),
            ]);
        } else {
            $this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => 'Бундай махсулот топилмади!',
            ]);
        }
    }

    private function addProductToCart($callBackData)
    {
        $product = CacheService::getProducts()->find(explode('_', $callBackData)[3]);
        $product_count = explode('_', $callBackData)[1];
        $cart = TelegramUserCard::query()->where('telegram_user_id', $this->user->id)
                        ->where('product_id', $product->id)->first();

        if (!$cart) {
            TelegramUserCard::query()->create([
                'telegram_user_id' => $this->user->id,
                'product_id' => $product->id,
                'count' => $product_count
            ]);
            $text = $product_count . ' дона ' . $product->name . ' саватга қўшилди';
        } else {
            $cart->increment('count', $product_count);
            $text = $product_count . ' дона ' . $product->name . " саватга қўшилди \n" .
                'Саватдаги миқдори: ' . $cart->count . ' дона ';
        }

        if ($product) {
            $this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => $text,
            ]);
        } else {
            $this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => 'Бундай махсулот топилмади!',
            ]);
        }
    }

    private function showCartList()
    {
        $carts = TelegramUserCard::query()->with(['product'])
            ->where('telegram_user_id', $this->user->id)->get();

        if ($carts->isEmpty()) {
            $this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => 'Саватда махсулотлар мавжуд эмас!',
            ]);
        } else {
            $message = "Саватдаги махсулотлар:  \n\n";
            $total_price = 0;
            foreach ($carts as $cart) {
                $product_price = $cart->product->one_price * $cart->count;
                $message .= $cart->product->name . ' (' . number_format($cart->product->one_price) .  ') x ' .
                    $cart->count . ' = ' . number_format($product_price) . "\n";
                $total_price += $product_price;
            }

            $message .= "\nУмумий суммаси: " . number_format($total_price);

            $this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => $message,
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [
                            [
                                'text' => 'Буюртмани якунлаш',
                                'callback_data' => 'checkout'
                            ]
                        ],
                        [
                            [
                                'text' => 'Саватдаги махсулотни ўчириш',
                                'callback_data' => 'delete_product'
                            ]
                        ],
                        [
                            [
                                'text' => 'Саватни тўлиқ тозалаш',
                                'callback_data' => 'cart_clear'
                            ]
                        ]
                    ]
                ]),
            ]);
        }

    }

    private function cartClear()
    {
        $carts = TelegramUserCard::query()->where('telegram_user_id', $this->user->id)->get();

        if ($carts->isEmpty()) {
            $this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => 'Саватда махсулотлар мавжуд эмас!',
            ]);
        } else {
            TelegramUserCard::query()->where('telegram_user_id', $this->user->id)->delete();
            $this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => 'Саватдаги махсулотлар ўчирилди.',
            ]);
        }
    }

    private function cartProductsList()
    {
        $carts = TelegramUserCard::query()->where('telegram_user_id', $this->user->id)->get();
        $keyboard = [];

        foreach ($carts as $cart) {
            $keyboard[] = [
                [
                    'text' => $cart->product->name . ' - ' . $cart->count . ' дона',
                    'callback_data' => 'clear_' . $cart->id,
                ],
            ];
        }

        $this->telegram->sendMessage([
            'chat_id' => $this->chat_id,
            'text' => 'Ўчириладиган махсулотни танланг: ',
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard,
            ]),
        ]);
    }

    private function deleteProductFromCard($callBackData)
    {
        $cart = TelegramUserCard::query()->find(explode('_', $callBackData)[1]);

        if ($cart) {
            $product_name = $cart->product->name;
            $cart->delete();
            $this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => $product_name . ' саватдан ўчирилди.',
            ]);
        } else {
            $this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => 'Саватда бундай махсулот топилмади!',
            ]);
        }
    }

    private function finishOrder()
    {
        $carts = TelegramUserCard::query()->with(['product'])
            ->where('telegram_user_id', $this->user->id)->get();

        if ($carts->isEmpty()) {
            $this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => 'Буюртмани якунлаш учун саватда махсулотлар мавжуд эмас!',
            ]);
        } else {
            $total_price = 0;
            foreach ($carts as $cart) {
                $total_price += $cart->product->one_price * $cart->count;
            }

            $order = TelegramOrder::query()->create([
                'user_id' => $this->user->id,
                'price' => $total_price,
            ]);

            foreach ($carts as $cart) {
                TelegramOrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $cart->product->id,
                    'count' => $cart->count,
                    'price' => $cart->product->one_price,
                ]);
                $cart->delete();
            }
            $this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => "Буюртма юборилди. \nБуюртма рақами: #" . $order->id,
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
                    'callback_data' => 'add_3_' . $callBackData
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
                    'text' => '9',
                    'callback_data' => 'add_9_' . $callBackData
                ],
            ]
        ];
    }

}

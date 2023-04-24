<?php

namespace App\Http\Controllers;

use App\Models\Place;
use App\Models\TelegramOrder;
use App\Models\TelegramOrderItem;
use App\Models\TelegramUser;
use App\Models\TelegramUserCard;
use App\Services\CacheService;
use App\Services\SendMessageService;
use Telegram\Bot\Actions;
use Telegram\Bot\Api;
use Telegram\Bot\Keyboard\Keyboard;


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
        if ($this->checkWorkingTime()){
            $this->user = TelegramUser::query()->where('telegram_id', $this->chat_id)->first();
            $this->saveContact();
            $this->proccessCallbackData();
            $this->proccessReplyMessage();
            $this->proccessCommands();
        }

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
        elseif (in_array($text, ['/orders', 'Буюртмаларни кўриш']))
            $this->ordersListCommand();
    }

    // commands
    private function startCommand()
    {
        $this->telegram->sendChatAction(['chat_id' => $this->chat_id, 'action' => Actions::TYPING]);
        $this->user ? $this->startChat() : $this->replyUserQuestions();
    }

    private function menuCommand()
    {
        $this->telegram->sendChatAction(['chat_id' => $this->chat_id, 'action' => Actions::TYPING]);
        $this->checkUserInfo() ? $this->replyAddressQuestion() : $this->replyUserQuestions();
    }

    private function cardCommand()
    {
        $this->telegram->sendChatAction(['chat_id' => $this->chat_id, 'action' => Actions::TYPING]);
        $this->user ? $this->showCartList() : $this->replyUserQuestions();
    }

    private function checkoutCommand($message_id)
    {
        $this->telegram->sendChatAction(['chat_id' => $this->chat_id, 'action' => Actions::TYPING]);
        $this->checkUserInfo() ? $this->finishOrder($message_id) : $this->replyUserQuestions();
    }

    private function ordersListCommand()
    {
        $this->telegram->sendChatAction(['chat_id' => $this->chat_id, 'action' => Actions::TYPING]);
        $this->user ? $this->showOrdersList() : $this->replyUserQuestions();
    }

    // commands methods
    private function saveContact()
    {
        $message = $this->telegram->getWebhookUpdate()->getMessage();
        if ($message->has('contact'))
        {
            $number = $message->contact->phone_number;
            if (str_starts_with($number, '+')) {
                $number = substr($number, 1);
            }

            $user = TelegramUser::query()->where('telegram_id', $this->chat_id)->first();
            if ($user) {
                $this->telegram->sendMessage(['chat_id' => $this->chat_id, 'text' => 'Сиз телефон рақаминигизни киритиб бўлганисиз!']);
            }elseif (strlen($number) != 12)
            {
                $this->telegram->sendMessage(['chat_id' => $this->chat_id, 'text' => 'Сизнинг телефон рақамингиз текширувдан ўтмади!']);
            } else {
                TelegramUser::createNewUser($this->chat_id, $message->from, substr($number, 3));
                $this->startChat();
            }
        }

    }

    private function savePlace($callBackData, $message_id)
    {
        $place = Place::query()->find(explode('_', $callBackData)[1]);
        $this->user->place_id = $place->id;
        $this->user->save();

        $this->deleteMessage($message_id);
        $this->telegram->sendMessage([
            'chat_id' => $this->chat_id,
            'text' => "Худуд муаффақиятли сақланди. \nСизнинг худудингиз: " . $place->name . "\nУшбу худуддаги аниқ манзилингизни киритинг",
        ]);

        $this->replyUserQuestions();
    }

    private function saveAddress($replayed_message)
    {
        $this->user->address = $replayed_message;
        $this->user->save();

        $this->telegram->sendMessage(['chat_id' => $this->chat_id, 'text' => 'Сиз муаффақиятли рўйҳатдан ўтдингиз.']);
        $this->startChat();
    }

    private function proccessReplyMessage()
    {
        $message = $this->telegram->getWebhookUpdate()->getMessage();
        if (!$message->has('contact'))
        {
            if ($message->has('reply_to_message'))
            {
                $replayed_message = $message->reply_to_message->text;
                if ($replayed_message == 'Манзил:')
                    $this->saveAddress($message->text);
            }
        }

    }

    private function proccessCallbackData()
    {
        if($this->telegram->getWebhookUpdate()->has('callback_query')) {
            $callBackData = $this->telegram->getWebhookUpdate()->callbackQuery->data;
            $message_id = $this->telegram->getWebhookUpdate()->callbackQuery->message->message_id;

            if (str_starts_with($callBackData, 'product_'))
                $this->selectProduct($callBackData);
            elseif (str_starts_with($callBackData, 'add_'))
                $this->addProductToCart($callBackData, $message_id);
            elseif ($callBackData == 'checkout')
                $this->checkoutCommand($message_id);
            elseif ($callBackData == 'show_cart')
                $this->showCartList();
            elseif ($callBackData == 'cart_clear')
                $this->cartClear($message_id);
            elseif ($callBackData == 'delete_product')
                $this->cartProductButtons();
            elseif (str_starts_with($callBackData, 'clear_'))
                $this->deleteProductFromCard($callBackData, $message_id);
            elseif ($callBackData == 'cancel_orders')
                $this->orderButtons($message_id, 'rollback_');
            elseif ($callBackData == 'info_orders')
                $this->orderButtons($message_id, 'info_');
            elseif (str_starts_with($callBackData, 'rollback_'))
                $this->deleteOrder($callBackData, $message_id);
            elseif (str_starts_with($callBackData, 'info_'))
                $this->infoOrder($callBackData, $message_id);
            elseif (str_starts_with($callBackData, 'place_'))
                $this->savePlace($callBackData, $message_id);
            elseif ($callBackData == 'continue')
                $this->replyMenuList($message_id);
            elseif ($callBackData == 'reset_address')
                $this->resetUserAddress($message_id);
        }
    }

    private function replyUserQuestions()
    {
        if ($this->user)
        {
            if(!$this->user->place_id) {
                $places = CacheService::getPlaces();
                $text = "Давом этишингиз учун аввал манзилни киритиш талаб қилинади. Худудни танланг: ";
                $keyboard = [];

                foreach ($places as $id => $place) {
                    $keyboard[] = [
                        [
                            'text' => $place,
                            'callback_data' => 'place_' . $id,
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
            } elseif (!$this->user->address) {
                $this->telegram->sendMessage([
                    'chat_id' => $this->chat_id,
                    'text' => 'Манзил:',
                    'reply_markup' => Keyboard::forceReply(),
                ]);
            }
        } else {
            $this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => 'Таомчига хуш келибсиз. Aввал рўйҳатдан ўтишингиз керак!',
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
                            'text' => 'Буюртмаларни кўриш'
                        ],
                        [
                            'text' => 'Саватни кўриш'
                        ]
                    ]
                ],
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ]),
        ]);
    }

    private function replyAddressQuestion()
    {
        $this->telegram->sendMessage([
            'chat_id' => $this->chat_id,
            'text' => "Ҳудуд: " . $this->user->place->name . "\nМанзил: " . $this->user->address
                . "\nБуюртма бермоқчи бўлган манзилингиз тўгрими?",
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [
                        [
                            'text' => 'Ха, давом этиш.',
                            'callback_data' => 'continue'
                        ]
                    ],
                    [
                        [
                            'text' => 'Йўқ, бошқа манзил.',
                            'callback_data' => 'reset_address'
                        ]
                    ]
                ]
            ]),
        ]);
    }

    private function resetUserAddress($message_id)
    {
        $this->user->update(['address' => null, 'place_id' => null]);
        TelegramUserCard::query()->where('telegram_user_id', $this->user->id)->delete();

        $this->deleteMessage($message_id);
        $this->telegram->sendMessage([
            'chat_id' => $this->chat_id,
            'text' => 'Эски манзилингиз ўчирилди, давом этишингиз мумкин!',
        ]);

        $this->replyUserQuestions();
    }

    private function replyMenuList($message_id)
    {
        $products = CacheService::getPlaceProducts($this->user->place_id);
        if ($products->isEmpty()) {
            $this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => 'Махсулотлар мавжуд эмас!',
            ]);
        } else {
            $text = 'Махсулотларни танланг: ';
            $keyboard = [];

            foreach ($products as $product) {
                $keyboard[] = [
                    [
                        'text' => $product->product->name . ' - ' . number_format($product->price) . ' сўм',
                        'callback_data' => 'product_' . $product->product_id,
                    ],
                ];
            }

            $this->deleteMessage($message_id);

            $this->sendProductsImage($this->user->place_id);
            $this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => $text,
                'reply_markup' => json_encode([
                    'inline_keyboard' => $keyboard,
                ]),
            ]);
        }
    }

    private function selectProduct($callBackData)
    {
        $countKeyboard = $this->getCountKeyboard($callBackData);
        $product = CacheService::getProducts()->find(explode('_', $callBackData)[1]);
        if ($product) {
            $product_name = "Махсулот: " . $product->name . "\nМиқдорини киритинг:";

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

    private function addProductToCart($callBackData, $message_id)
    {
        $product = CacheService::getProducts()->find(explode('_', $callBackData)[3]);

        if ($product) {
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

            $this->deleteMessage($message_id);
            $this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => $text,
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
                                'text' => 'Саватни кўриш',
                                'callback_data' => 'show_cart'
                            ]
                        ]
                    ]
                ]),
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
            $prices = CacheService::getPlaceProducts($this->user->place_id)->mapWithKeys(function ($item) {
                return [$item->product_id => $item->price];
            });
            foreach ($carts as $cart) {
                $product_price = $prices[$cart->product_id] * $cart->count;
                $message .= $cart->product->name . ' (' . number_format($prices[$cart->product_id]) .  ') x ' .
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

    private function cartClear($message_id)
    {
        $carts = TelegramUserCard::query()->where('telegram_user_id', $this->user->id)->get();

        if ($carts->isEmpty()) {
            $this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => 'Саватда махсулотлар мавжуд эмас!',
            ]);
        } else {
            TelegramUserCard::query()->where('telegram_user_id', $this->user->id)->delete();
            $this->deleteMessage($message_id);
            $this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => 'Саватдаги махсулотлар ўчирилди.',
            ]);
        }
    }

    private function cartProductButtons()
    {
        $carts = TelegramUserCard::query()->where('telegram_user_id', $this->user->id)->get();

        if ($carts->isEmpty()) {
            $this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => 'Саватда махсулотлар мавжуд эмас!',
            ]);
        } else {
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

    }

    private function showOrdersList()
    {
        $orders = TelegramOrder::query()->where('user_id', $this->user->id)->get();

        if ($orders->isEmpty()) {
            $this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => 'Буюртмалар мавжуд эмас!',
            ]);
        } else {
            $message = "Мавжуд буюртмалар:  \n\n";

            foreach ($orders as $key => $order) {
                $message .= $key + 1 . ") Буюртма рақами: #<b>" . $order->id . "</b> \n" .
                    "Буюртма холати: <b>" . TelegramOrder::TYPE[$order->state] . "</b> \n" .
                    "Буюртма суммаси: <b>" . number_format($order->price) . "</b> \n\n";
            }

            $this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'parse_mode' => 'HTML',
                'text' => $message,
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [
                            [
                                'text' => 'Буюртмани қайтариш',
                                'callback_data' => 'cancel_orders'
                            ]
                        ],
                        [
                            [
                                'text' => 'Буюртма маълумотлари',
                                'callback_data' => 'info_orders'
                            ]
                        ]
                    ]
                ]),
            ]);
        }
    }

    private function orderButtons($message_id, $type)
    {
        $orders = TelegramOrder::query()->where('user_id', $this->user->id)->get();

        if ($orders->isEmpty()) {
            $this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => 'Буюртмалар мавжуд эмас!',
            ]);
        } else {
            $keyboard = [];

            foreach ($orders as $order) {
                $keyboard[] = [
                    [
                        'text' => '#' . $order->id . ' рақамли буюртма',
                        'callback_data' => $type . $order->id, // rollback or info
                    ],
                ];
            }

            $this->deleteMessage($message_id);
            $this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => 'Буюртмани танланг: ',
                'reply_markup' => json_encode([
                    'inline_keyboard' => $keyboard,
                ]),
            ]);
        }

    }

    private function deleteProductFromCard($callBackData, $message_id)
    {
        $cart = TelegramUserCard::query()->find(explode('_', $callBackData)[1]);

        $this->deleteMessage($message_id);
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

    private function infoOrder($callBackData, $message_id)
    {
        $order = TelegramOrder::query()->find(explode('_', $callBackData)[1]);

        if ($order)
        {
            $order_items = TelegramOrderItem::query()->with(['product'])
                ->where('order_id', $order->id)->get();

            $message = "Буюртма рақами: #" . $order->id . "\nБуюртма холати: " . TelegramOrder::TYPE[$order->state] . "\n\n";
            $total_price = 0;

            foreach ($order_items as $item) {
                $product_price = $item->price * $item->count;
                $message .=  $item->count .  ' x ' . $item->product->name   . ' = ' . number_format($product_price) . "\n";
                $total_price += $product_price;
            }

            $message .= "\nУмумий суммаси: " . number_format($total_price);
            $this->deleteMessage($message_id);
            $this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => $message,
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [
                            [
                                'text' => 'Буюртмани қайтариш',
                                'callback_data' => 'rollback_' . $order->id
                            ]
                        ]
                    ]
                ]),
            ]);
        } else {
            $this->deleteMessage($message_id);
            $this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => 'Бундай буюртма топилмади!',
            ]);
        }
    }

    private function deleteOrder($callBackData, $message_id)
    {
        $order = TelegramOrder::query()->find(explode('_', $callBackData)[1]);

        if ($order) {
            $order_id = $order->id;
            if ($order->state == 'send_order') {
                $order->products()->delete();
                $order->delete();
                $this->deleteMessage($message_id);
                $this->telegram->sendMessage([
                    'chat_id' => $this->chat_id,
                    'text' => 'Сиз #' . $order_id . ' рақамли буюртмани бекор қилиндингиз.',
                ]);
                SendMessageService::deleteOrder($order_id);
            } else {
                $this->deleteMessage($message_id);
                $this->telegram->sendMessage([
                    'chat_id' => $this->chat_id,
                    'text' => '#' . $order_id . " рақамли буюртма қабул қилинган ва тайёрлаш жараёнида бўлгани учун қайтариб бўлмайди. Мурожат учун телефон раками: \n" .
                        "+998917070907 +998770150907",
                ]);
            }
        } else {
            $this->deleteMessage($message_id);
            $this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => 'Бундай буюртма топилмади!',
            ]);
        }
    }

    private function finishOrder($message_id): void
    {
        $carts = TelegramUserCard::query()->with(['product'])
            ->where('telegram_user_id', $this->user->id)->get();

        if ($carts->isEmpty()) {
            $this->deleteMessage($message_id);
            $this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => 'Буюртмани якунлаш учун саватда махсулотлар мавжуд эмас!',
            ]);
        } else {

            $message = "Махсулотлар:  \n\n";
            $prices = CacheService::getPlaceProducts($this->user->place_id)->mapWithKeys(function ($item) {
                return [$item->product_id => $item->price];
            });
            $total_price = 0;

            foreach ($carts as $item) {
                $product_price = $prices[$item->product_id] * $item->count;
                $message .= $item->product->name . ' (' . number_format($prices[$item->product_id]) .  ') x ' .
                    $item->count . ' = ' . number_format($product_price) . "\n";
                $total_price += $product_price;
            }

            $message .= "\nУмумий суммаси: " . number_format($total_price);

            $order = TelegramOrder::query()->create([
                'user_id' => $this->user->id,
                'price' => $total_price,
                'place_id' => $this->user->place_id,
                'address' => $this->user->address
            ]);

            foreach ($carts as $cart) {
                TelegramOrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $cart->product->id,
                    'count' => $cart->count,
                    'price' => $prices[$cart->product_id],
                ]);
                $cart->delete();
            }

            $this->deleteMessage($message_id);
            $this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => "Буюртма юборилди. \nБуюртма рақами: #" . $order->id . "\n\n" . $message,
            ]);

            SendMessageService::sendTelegramOrder($order->id);
        }
    }

    private function getCountKeyboard($callBackData): array
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

    private function sendProductsImage($place_id)
    {
        $channel = -1001361413476;
        $place = Place::query()->find($place_id);
        try {
            if (!is_null($place_id)) {
                $this->telegram->forwardMessage([
                    'chat_id' => $this->chat_id,
                    'from_chat_id' => $channel,
                    'message_id' => $place->telegram_message_id
                ]);
            }
        } catch (\Exception $e) {}
    }

    private function deleteMessage($message_id)
    {
        $this->telegram->deleteMessage([
            'chat_id' => $this->chat_id,
            'message_id' => $message_id
        ]);
    }

    private function checkUserInfo(): bool
    {
        return $this->user->place_id && $this->user->address;
    }

    private function checkWorkingTime(): bool
    {
        $currentTime = date('H:i:s');
        $startTime = '00:30:00';
        $endTime = '23:30:00';
        $dayWeek = date('N');

        if (!($startTime <= $currentTime && $currentTime <= $endTime) || $dayWeek == 2)
        {
            $this->telegram->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => 'Хизматларимиз сешанбадан - якшанбагача 7:30 дан 15:30 гача ишайди!'
            ]);

            return false;
        } else {
            return true;
        }
    }


}

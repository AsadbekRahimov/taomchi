<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramBotController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/checkPrint/{id}', function ($id){
    $order = \App\Models\Order::query()->with(['cards.product.measure', 'customer'])->find($id);
    return view('printCheck', compact('order'));
})->name('printCheck');

// Telegram bot routes
Route::get('telegram/setWebhook', [TelegramBotController::class, 'setWebhook']);
Route::post('telegram/bot/webhook', [TelegramBotController::class, 'webhook']);

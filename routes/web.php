<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramBotController;
use App\Http\Controllers\TelegramController;

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

Route::get('/checkPrint/{id}', [\App\Http\Controllers\Controller::class, 'orderCheck'])->name('printCheck');
Route::get('/print-tg-order/{id}', [\App\Http\Controllers\Controller::class, 'tgOrderCheck'])->name('print-tg-order');


Route::get('/bot/webhook', [TelegramController::class, 'setWebHook']);
Route::post('/bot', [TelegramController::class, 'run']);

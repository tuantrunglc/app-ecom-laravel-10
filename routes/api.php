<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatNotifyController;
use App\Http\Controllers\NotificationsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/chat/notify-new-message', [ChatNotifyController::class, 'store'])
    ->middleware(['auth']);

Route::get('/notifications', [NotificationsController::class, 'index'])
    ->middleware(['auth']);

Route::post('/notifications/read', [NotificationsController::class, 'markRead'])
    ->middleware(['auth']);

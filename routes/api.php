<?php

use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user',                 [Api\UserController::class, 'me'])->name('api.user');
    Route::get('/users/search',         [Api\UserController::class, 'search']);
    Route::get('/users/{user}',         [Api\UserController::class, 'show']);

    Route::get('/chats',                [Api\ChatController::class, 'index']);
    Route::get('/chats/{chat}/messages',[Api\ChatController::class, 'messages']);

    Route::get('/stories',              [Api\StoryController::class, 'index']);

    Route::get('/notifications',        [Api\NotificationController::class, 'index']);
    Route::post('/notifications/read',  [Api\NotificationController::class, 'markAllRead']);
    Route::patch('/notifications/{id}/read', [Api\NotificationController::class, 'markRead']);
});

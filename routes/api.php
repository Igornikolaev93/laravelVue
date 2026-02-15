<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\YandexMapsController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/yandex-maps/reviews', [YandexMapsController::class, 'fetchReviews'])->name('yandex-maps.reviews');

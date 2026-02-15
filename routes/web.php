<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\YandexMapsController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/yandex-maps', [YandexMapsController::class, 'index'])->name('yandex-maps.index');
Route::get('/yandex-maps/settings', [YandexMapsController::class, 'settings'])->name('yandex-maps.settings');
Route::post('/yandex-maps/connect', [YandexMapsController::class, 'connect'])->name('yandex-maps.connect');
Route::post('/yandex-maps/fetch-reviews', [YandexMapsController::class, 'fetchReviews'])->name('yandex-maps.fetch-reviews');
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\YandexMapsController;

Route::get('/', function () {
    return redirect()->route('yandex-maps.index');
});

Route::match(['get', 'post'], '/yandex-maps', [YandexMapsController::class, 'index'])->name('yandex-maps.index');

Route::post('/yandex-maps/fetch-reviews', [YandexMapsController::class, 'fetchReviews'])->name('yandex-maps.fetch-reviews');

Route::post('/yandex-maps/connect', [YandexMapsController::class, 'connect'])->name('yandex-maps.connect');
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\YandexMapsController;

Route::get('/yandex-maps', [YandexMapsController::class, 'index'])->name('yandex-maps.index');
Route::post('/yandex-maps', [YandexMapsController::class, 'index'])->name('yandex-maps.index.post');

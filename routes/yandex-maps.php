<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\YandexMapsController;

Route::match(['get', 'post'], '/yandex-maps', [YandexMapsController::class, 'index'])->name('yandex-maps.index');

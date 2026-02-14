<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\YandexMapsController;

Route::middleware('auth')->group(function () {
    Route::get('/yandex-maps', [YandexMapsController::class, 'index'])->name('yandex-maps.index');
    Route::get('/yandex-maps/settings', [YandexMapsController::class, 'settings'])->name('yandex-maps.settings');
    Route::post('/yandex-maps/settings', [YandexMapsController::class, 'saveSettings'])->name('yandex-maps.save-settings');
});

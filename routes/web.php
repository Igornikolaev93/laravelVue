<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\YandexMapsController;

// Корневой маршрут
Route::get('/', [YandexMapsController::class, 'index'])->name('home');

// Маршруты для Яндекс отзывов
Route::prefix('yandex-maps')->name('yandex-maps.')->group(function () {
    Route::get('/', [YandexMapsController::class, 'index'])->name('index');
    Route::get('/settings', [YandexMapsController::class, 'settings'])->name('settings');
    Route::post('/connect', [YandexMapsController::class, 'connect'])->name('connect');
    Route::post('/fetch-reviews', [YandexMapsController::class, 'fetchReviews'])->name('fetch-reviews');
    Route::get('/reviews', [YandexMapsController::class, 'reviews'])->name('reviews');
});
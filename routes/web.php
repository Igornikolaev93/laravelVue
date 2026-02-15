<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\YandexMapsController;

Route::get('/', function () {
    return redirect()->route('yandex-maps.index');
});

// Reviews page
Route::get('/reviews', [YandexMapsController::class, 'index'])->name('yandex-maps.index');

// Settings page
Route::get('/settings', [YandexMapsController::class, 'settings'])->name('yandex-maps.settings');

// Handle the form submission for connecting the Yandex Maps URL
Route::post('/yandex-maps/connect', [YandexMapsController::class, 'connect'])->name('yandex-maps.connect');

// Fetch reviews from Yandex Maps
Route::post('/yandex-maps/fetch-reviews', [YandexMapsController::class, 'fetchReviews'])->name('yandex-maps.fetch-reviews');

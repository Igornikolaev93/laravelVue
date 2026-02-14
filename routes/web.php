<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('yandex-maps.index');
});

require __DIR__.'/yandex-maps.php';

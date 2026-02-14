<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YandexMapsSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'yandex_maps_url',
    ];
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('yandex_maps_settings', function (Blueprint $table) {
            $table->id();
            $table->string('yandex_maps_url')->nullable();
            $table->string('rating')->nullable();
            $table->string('total_reviews')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('yandex_maps_settings');
    }
};
<?php

namespace App\Http\Controllers;

use App\Models\YandexMapsSetting;
use Illuminate\Http\Request;

class YandexMapsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'yandex_maps_url' => 'required|url',
            ]);

            YandexMapsSetting::updateOrCreate(
                ['id' => 1],
                ['yandex_maps_url' => $validated['yandex_maps_url']]
            );

            return redirect()->route('yandex-maps.index')->with('success', 'URL saved successfully!');
        }

        $settings = YandexMapsSetting::first();
        
        if (!$settings || !$settings->yandex_maps_url) {
            return view('yandex-maps.connect');
        }

        return view('yandex-maps.index', [
            'settings' => $settings
        ]);
    }
}
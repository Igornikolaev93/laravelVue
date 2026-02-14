<?php

namespace App\Http\Controllers;

use App\Models\YandexMapsSettings;
use Illuminate\Http\Request;

class YandexMapsController extends Controller
{
    public function index()
    {
        $settings = YandexMapsSettings::first();
        // TODO: Fetch reviews from Yandex Maps API
        $reviews = []; // Placeholder
        return view('yandex-maps.index', compact('settings', 'reviews'));
    }

    public function settings()
    {
        $settings = YandexMapsSettings::first();
        return view('yandex-maps.settings', compact('settings'));
    }

    public function saveSettings(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|url',
        ]);

        YandexMapsSettings::updateOrCreate([
            'id' => 1,
        ], [
            'url' => $validated['url'],
        ]);

        return redirect()->route('yandex-maps.settings')->with('success', 'Settings saved successfully!');
    }
}

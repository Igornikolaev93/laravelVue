<?php

namespace App\Http\Controllers;

use App\Models\YandexMapsSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YandexMapsController extends Controller
{
    public function index()
    {
        $settings = YandexMapsSetting::first();
        return view('yandex-maps.index', compact('settings'));
    }

    public function settings()
    {
        $settings = YandexMapsSetting::first();
        return view('yandex-maps.settings', compact('settings'));
    }

    public function connect(Request $request)
    {
        $request->validate([
            'yandex_maps_url' => 'required|url'
        ]);

        try {
            YandexMapsSetting::updateOrCreate(
                ['id' => 1],
                ['yandex_maps_url' => $request->yandex_maps_url]
            );

            return redirect()->route('yandex-maps.index')
                ->with('success', 'URL успешно сохранен!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Ошибка сохранения: ' . $e->getMessage());
        }
    }

    public function fetchReviews(Request $request)
    {
        $request->validate(['url' => 'required|url']);

        $orgId = $this->extractOrganizationId($request->url);
        
        if (!$orgId) {
            return response()->json([
                'error' => 'Не удалось извлечь ID организации из URL. Убедитесь, что ссылка содержит ID организации.'
            ], 400);
        }

        // Пробуем получить отзывы
        $reviews = $this->getYandexReviews($orgId);

        if (empty($reviews)) {
            return response()->json([
                'error' => 'Не удалось загрузить отзывы. Проверьте URL организации.'
            ], 404);
        }

        $stats = [
            'total_reviews' => count($reviews),
            'average_rating' => 0
        ];

        if ($stats['total_reviews'] > 0) {
            $totalRating = array_sum(array_column($reviews, 'rating'));
            $stats['average_rating'] = round($totalRating / $stats['total_reviews'], 2);
        }

        return response()->json([
            'reviews' => $reviews,
            'stats' => $stats
        ]);
    }

    private function extractOrganizationId($url)
    {
        // Извлекаем ID из URL Яндекса
        if (preg_match('/org\/(?:[^\/]+\/)?(\d+)/', $url, $matches)) {
            return $matches[1];
        }
        
        if (preg_match('/\/(\d{6,})\//', $url, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    private function getYandexReviews($orgId)
    {
        try {
            // Пробуем получить отзывы через API Яндекса
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept' => 'application/json',
                'Referer' => 'https://yandex.ru/maps/'
            ])->timeout(10)->get("https://yandex.ru/maps/api/organizations/{$orgId}/reviews?lang=ru&pageSize=20");

            if ($response->successful()) {
                $data = $response->json();
                return $this->parseReviews($data);
            }
        } catch (\Exception $e) {
            Log::error('Yandex API error: ' . $e->getMessage());
        }

        return [];
    }

    private function parseReviews($data)
    {
        $reviews = [];
        
        $items = $data['reviews'] ?? $data['data']['reviews'] ?? [];
        
        foreach ($items as $item) {
            $reviews[] = [
                'author' => $item['author']['name'] ?? $item['user']['name'] ?? 'Аноним',
                'date' => $item['date'] ?? $item['createdAt'] ?? date('Y-m-d'),
                'rating' => $item['rating'] ?? $item['stars'] ?? 5,
                'text' => $item['text'] ?? $item['comment'] ?? ''
            ];
        }
        
        return $reviews;
    }
}
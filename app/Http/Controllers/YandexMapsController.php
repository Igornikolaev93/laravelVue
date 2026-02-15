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
                'success' => false,
                'error' => 'Не удалось найти ID организации'
            ], 400);
        }

        // Пробуем разные методы получения отзывов
        $reviews = $this->fetchFromYandex($orgId);

        if (empty($reviews)) {
            return response()->json([
                'success' => false,
                'error' => 'Отзывы не найдены',
                'reviews' => []
            ], 404);
        }

        $stats = $this->calculateStats($reviews);

        return response()->json([
            'success' => true,
            'reviews' => $reviews,
            'stats' => $stats
        ]);
    }

    private function extractOrganizationId($url)
    {
        // Паттерны для поиска ID в URL Яндекса
        $patterns = [
            '/org\/(?:[^\/]+\/)?(\d+)/',
            '/organization\/(\d+)/',
            '/maps\/(\d+)/',
            '/\/(\d{6,})\//',
            '/reviews\/(\d+)/'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }

    private function fetchFromYandex($orgId)
    {
        // Пробуем разные эндпоинты API
        $endpoints = [
            "https://yandex.ru/maps/api/organizations/{$orgId}/reviews?lang=ru&pageSize=50",
            "https://yandex.ru/maps-api/v2/organizations/{$orgId}/reviews?lang=ru_RU&pageSize=50"
        ];

        foreach ($endpoints as $endpoint) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept' => 'application/json',
                    'Accept-Language' => 'ru-RU,ru;q=0.9',
                    'Referer' => 'https://yandex.ru/maps/'
                ])->timeout(10)->get($endpoint);

                if ($response->successful()) {
                    $data = $response->json();
                    $reviews = $this->parseReviews($data);
                    
                    if (!empty($reviews)) {
                        Log::info('Got reviews from Yandex', [
                            'org_id' => $orgId,
                            'count' => count($reviews)
                        ]);
                        return $reviews;
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Yandex API error', [
                    'endpoint' => $endpoint,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }

        return [];
    }

    private function parseReviews($data)
    {
        $reviews = [];
        
        // Ищем отзывы в разных структурах данных
        $items = $data['reviews'] ?? 
                $data['data']['reviews'] ?? 
                $data['items'] ?? 
                [];

        if (!is_array($items)) {
            return $reviews;
        }

        foreach ($items as $item) {
            $review = [
                'author' => $this->extractAuthor($item),
                'date' => $this->extractDate($item),
                'rating' => $this->extractRating($item),
                'text' => $this->extractText($item)
            ];

            // Добавляем только если есть текст
            if (!empty($review['text']) && strlen($review['text']) > 10) {
                $reviews[] = $review;
            }
        }

        return $reviews;
    }

    private function extractAuthor($item)
    {
        return $item['author']['name'] ?? 
               $item['user']['name'] ?? 
               $item['authorName'] ?? 
               'Аноним';
    }

    private function extractDate($item)
    {
        $date = $item['date'] ?? 
                $item['createdAt'] ?? 
                $item['publishDate'] ?? 
                date('Y-m-d');

        if (is_numeric($date)) {
            return date('Y-m-d', $date);
        }

        return date('Y-m-d', strtotime($date));
    }

    private function extractRating($item)
    {
        return (int) ($item['rating'] ?? $item['stars'] ?? 0);
    }

    private function extractText($item)
    {
        return trim($item['text'] ?? $item['comment'] ?? '');
    }

    private function calculateStats($reviews)
    {
        $total = count($reviews);
        $sum = 0;
        
        foreach ($reviews as $review) {
            $sum += $review['rating'];
        }

        return [
            'total_reviews' => $total,
            'average_rating' => $total > 0 ? round($sum / $total, 1) : 0
        ];
    }
}
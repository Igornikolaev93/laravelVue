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
            // Если не удалось извлечь ID, пробуем другой метод
            $orgId = $this->extractOrganizationIdAlternative($request->url);
        }
        
        if (!$orgId) {
            return response()->json([
                'error' => 'Не удалось извлечь ID организации из URL. Попробуйте другую ссылку.'
            ], 400);
        }

        // Пробуем получить отзывы через API Яндекса
        $reviews = $this->fetchYandexReviews($orgId);

        if (empty($reviews)) {
            // Если API не работает, возвращаем понятное сообщение
            return response()->json([
                'error' => 'Временно не удалось загрузить отзывы. Попробуйте позже.',
                'reviews' => [],
                'stats' => [
                    'total_reviews' => 0,
                    'average_rating' => 0
                ]
            ]);
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
        // Паттерны для извлечения ID из URL Яндекса
        $patterns = [
            '/org\/(?:[^\/]+\/)?(\d+)/',
            '/organization\/(\d+)/',
            '/maps\/(\d+)/',
            '/biz\/(\d+)/',
            '/\/(\d{6,})\/?/',
            '/reviews\/(\d+)/',
            '/oid=(\d+)/',
            '/id=(\d+)/'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                Log::info('Found organization ID', ['id' => $matches[1], 'url' => $url]);
                return $matches[1];
            }
        }
        
        return null;
    }

    private function extractOrganizationIdAlternative($url)
    {
        // Альтернативный метод - ищем любое большое число
        if (preg_match('/(\d{6,})/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function fetchYandexReviews($orgId)
    {
        try {
            // Пробуем разные эндпоинты
            $endpoints = [
                "https://yandex.ru/maps/api/organizations/{$orgId}/reviews?lang=ru&pageSize=20",
                "https://yandex.ru/maps-api/v2/organizations/{$orgId}/reviews?lang=ru_RU&pageSize=20"
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
                            Log::info('Successfully fetched reviews', ['count' => count($reviews)]);
                            return $reviews;
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Endpoint failed', ['endpoint' => $endpoint, 'error' => $e->getMessage()]);
                    continue;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error fetching reviews', ['error' => $e->getMessage()]);
        }

        return [];
    }

    private function parseReviews($data)
    {
        $reviews = [];
        
        // Пробуем разные структуры данных
        $items = $data['reviews'] ?? $data['data']['reviews'] ?? $data['items'] ?? [];
        
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
            
            // Добавляем только если есть текст отзыва
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
                date('Y-m-d H:i:s');
                
        if (is_numeric($date) && strlen((string)$date) === 10) {
            return date('Y-m-d H:i:s', $date);
        }
        
        return date('Y-m-d H:i:s', strtotime($date));
    }

    private function extractRating($item)
    {
        return (float) ($item['rating'] ?? $item['stars'] ?? $item['rate'] ?? 5);
    }

    private function extractText($item)
    {
        return trim($item['text'] ?? $item['comment'] ?? $item['message'] ?? '');
    }
}
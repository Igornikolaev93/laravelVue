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
                'error' => 'Не удалось извлечь ID организации из URL'
            ], 400);
        }

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
        $patterns = [
            '/org\/(?:[^\/]+\/)?(\d+)/',
            '/organization\/(\d+)/',
            '/maps\/(\d+)/',
            '/biz\/(\d+)/',
            '/\/(\d{5,})\/?/',
            '/reviews\/(\d+)/',
            '/oid=(\d+)/',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }
        
        // Пробуем найти любое 6+ значное число
        if (preg_match('/(\d{6,})/', $url, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    private function getYandexReviews($orgId)
    {
        $allReviews = [];
        $page = 1;
        $maxPages = 3;
        
        while ($page <= $maxPages) {
            $reviews = $this->fetchReviewsPage($orgId, $page);
            
            if (empty($reviews)) {
                break;
            }
            
            $allReviews = array_merge($allReviews, $reviews);
            $page++;
            
            // Задержка между запросами
            if ($page <= $maxPages) {
                usleep(300000);
            }
        }
        
        return $allReviews;
    }

    private function fetchReviewsPage($orgId, $page = 1)
    {
        $endpoints = [
            "https://yandex.ru/maps/api/organizations/{$orgId}/reviews?lang=ru&page={$page}&pageSize=20",
            "https://yandex.ru/maps-api/v2/organizations/{$orgId}/reviews?lang=ru_RU&page={$page}&pageSize=20",
        ];

        foreach ($endpoints as $endpoint) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'application/json, text/plain, */*',
                    'Accept-Language' => 'ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
                    'Referer' => 'https://yandex.ru/maps/',
                    'Origin' => 'https://yandex.ru',
                ])->timeout(10)->get($endpoint);

                if ($response->successful()) {
                    $data = $response->json();
                    $reviews = $this->parseReviews($data);
                    
                    if (!empty($reviews)) {
                        return $reviews;
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Endpoint failed: {$endpoint}", ['error' => $e->getMessage()]);
                continue;
            }
        }
        
        return [];
    }

    private function parseReviews($data)
    {
        $reviews = [];
        
        $reviewItems = $data['reviews'] ?? 
                      $data['data']['reviews'] ?? 
                      $data['items'] ?? 
                      [];

        foreach ($reviewItems as $item) {
            $rating = $item['rating'] ?? $item['stars'] ?? $item['rate'] ?? 0;
            
            // Пропускаем отзывы без рейтинга или текста
            if (empty($item['text']) && empty($item['comment'])) {
                continue;
            }

            $reviews[] = [
                'author' => $this->extractAuthor($item),
                'date' => $this->extractDate($item),
                'rating' => (float) $rating,
                'text' => $this->extractText($item)
            ];
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
                
        if (is_numeric($date) && strlen((string)$date) == 10) {
            return date('Y-m-d H:i:s', $date);
        }
        
        return date('Y-m-d H:i:s', strtotime($date));
    }

    private function extractText($item)
    {
        return trim($item['text'] ?? 
               $item['comment'] ?? 
               $item['message'] ?? 
               '');
    }
}
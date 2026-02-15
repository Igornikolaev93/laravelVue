<?php

namespace App\\Http\\Controllers;

use App\\Models\\YandexMapsSetting;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Http;
use Illuminate\\Support\\Facades\\Log;

class YandexMapsController extends Controller
{
    private $apiKey = \'98ee2162-88df-47ac-8a2d-26df528afe73\';
    
    public function index(Request $request)
    {
        $settings = YandexMapsSetting::first();
        
        return view(\'yandex-maps.index\', [
            \'settings\' => $settings
        ]);
    }

    public function settings(Request $request)
    {
        $settings = YandexMapsSetting::first();
        
        return view(\'yandex-maps.settings\', [
            \'settings\' => $settings
        ]);
    }

    public function connect(Request $request)
    {
        $validated = $request->validate([
            \'yandex_maps_url\' => \'required|url\',
        ]);

        YandexMapsSetting::updateOrCreate(
            [\'id\' => 1],
            [\'yandex_maps_url\' => $validated[\'yandex_maps_url\']]
        );

        return redirect()->route(\'yandex-maps.settings\')->with(\'success\', \'URL saved successfully!\');
    }

    public function fetchReviews(Request $request)
    {
        $request->validate([
            \'url\' => \'required|url\'
        ]);

        $orgId = $this->extractOrganizationId($request->url);
        
        if (!$orgId) {
            return response()->json([\'error\' => \'Invalid organization URL\'], 400);
        }

        // Пробуем разные методы получения отзывов
        $reviews = $this->getAllReviews($orgId);

        $stats = [
            \'total_reviews\' => count($reviews),
            \'average_rating\' => 0
        ];

        if ($stats[\'total_reviews\'] > 0) {
            $totalRating = array_sum(array_column($reviews, \'rating\'));
            $stats[\'average_rating\'] = round($totalRating / $stats[\'total_reviews\'], 2);
        }

        return response()->json([
            \'reviews\' => $reviews,
            \'stats\' => $stats
        ]);
    }

    private function getAllReviews($orgId)
    {
        $allReviews = [];
        $page = 1;
        $maxPages = 5; // Максимум страниц для загрузки
        
        while ($page <= $maxPages) {
            $reviews = $this->fetchReviewsPage($orgId, $page);
            
            if (empty($reviews)) {
                break;
            }
            
            $allReviews = array_merge($allReviews, $reviews);
            $page++;
            
            // Небольшая задержка между запросами
            if ($page <= $maxPages) {
                usleep(500000); // 0.5 секунды
            }
        }
        
        return $allReviews;
    }

    private function fetchReviewsPage($orgId, $page = 1)
    {
        $endpoints = [
            // Основной API эндпоинт
            "https://yandex.ru/maps/api/organizations/{$orgId}/reviews?lang=ru&page={$page}&pageSize=20",
            
            // Альтернативные эндпоинты
            "https://yandex.ru/maps-api/v2/organizations/{$orgId}/reviews?lang=ru_RU&page={$page}&pageSize=20",
            "https://yandex.ru/maps/org/reviews/{$orgId}/?page={$page}",
            
            // Публичный API
            "https://yandex.ru/maps/api/reviews?oid={$orgId}&page={$page}&pageSize=20"
        ];

        foreach ($endpoints as $endpoint) {
            try {
                $response = Http::withHeaders([
                    \'User-Agent\' => \'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36\',
                    \'Accept\' => \'application/json, text/plain, */*\',
                    \'Accept-Language\' => \'ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7\',
                    \'Referer\' => \'https://yandex.ru/maps/\',
                    \'Origin\' => \'https://yandex.ru\',
                    \'X-Requested-With\' => \'XMLHttpRequest\'
                ])->timeout(15)->get($endpoint);

                if ($response->successful()) {
                    $data = $response->json();
                    $reviews = $this->parseReviews($data);
                    
                    if (!empty($reviews)) {
                        Log::info(\'Got reviews from endpoint\', [
                            \'endpoint\' => $endpoint,
                            \'page\' => $page,
                            \'count\' => count($reviews)
                        ]);
                        return $reviews;
                    }
                    
                    // Если получили HTML вместо JSON
                    if (strpos($response->body(), \'<html\') !== false) {
                        $reviews = $this->parseHtmlReviews($response->body());
                        if (!empty($reviews)) {
                            return $reviews;
                        }
                    }
                }
            } catch (\\Exception $e) {
                Log::warning("Endpoint failed: {$endpoint}", [\'error\' => $e->getMessage()]);
                continue;
            }
        }
        
        return [];
    }

    private function parseReviews($data)
    {
        $reviews = [];
        
        // Пробуем разные структуры данных
        $reviewItems = [];
        
        if (isset($data[\'reviews\']) && is_array($data[\'reviews\'])) {
            $reviewItems = $data[\'reviews\'];
        } elseif (isset($data[\'data\'][\'reviews\']) && is_array($data[\'data\'][\'reviews\'])) {
            $reviewItems = $data[\'data\'][\'reviews\'];
        } elseif (isset($data[\'items\']) && is_array($data[\'items\'])) {
            $reviewItems = $data[\'items\'];
        } elseif (isset($data[\'results\']) && is_array($data[\'results\'])) {
            $reviewItems = $data[\'results\'];
        } elseif (isset($data[\'response\'][\'reviews\']) && is_array($data[\'response\'][\'reviews\'])) {
            $reviewItems = $data[\'response\'][\'reviews\'];
        }

        foreach ($reviewItems as $item) {
            $review = [
                \'author\' => $this->extractAuthor($item),
                \'date\' => $this->extractDate($item),
                \'rating\' => $this->extractRating($item),
                \'text\' => $this->extractText($item)
            ];
            
            // Добавляем только если есть текст отзыва
            if (!empty($review[\'text\']) && strlen($review[\'text\']) > 5) {
                $reviews[] = $review;
            }
        }
        
        return $reviews;
    }

    private function parseHtmlReviews($html)
    {
        $reviews = [];
        
        // Ищем JSON данные в HTML
        $patterns = [
            \'/window\\.__INITIAL_STATE__\\s*=\\s*({.+?});/\',
            \'/<script[^>]*>\\s*window\\.__PRELOADED_STATE__\\s*=\\s*({.+?});\\s*<\\/script>/\',
            \'/<script[^>]*>\\s*var\\s+\\_\_ssr\_data\\s*=\\s*({.+?});\\s*<\\/script>/\',
            \'/"reviews":(\\[.*?\\])/\'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                try {
                    $data = json_decode($matches[1], true);
                    if ($data) {
                        $parsedReviews = $this->parseReviews($data);
                        if (!empty($parsedReviews)) {
                            return $parsedReviews;
                        }
                    }
                } catch (\\Exception $e) {
                    continue;
                }
            }
        }
        
        return $reviews;
    }

    private function extractAuthor($item)
    {
        return $item[\'author\'][\'name\'] ?? 
               $item[\'user\'][\'name\'] ?? 
               $item[\'authorName\'] ?? 
               $item[\'userName\'] ?? 
               $item[\'name\'] ?? 
               \'Аноним\';
    }

    private function extractDate($item)
    {
        $date = $item[\'date\'] ?? 
                $item[\'createdAt\'] ?? 
                $item[\'publishDate\'] ?? 
                $item[\'time\'] ?? 
                $item[\'publishedAt\'] ?? 
                date(\'Y-m-d H:i:s\');
                
        // Конвертируем timestamp в дату
        if (is_numeric($date) && strlen($date) == 10) {
            return date(\'Y-m-d H:i:s\', $date);
        }
        
        return date(\'Y-m-d H:i:s\', strtotime($date));
    }

    private function extractRating($item)
    {
        $rating = $item[\'rating\'] ?? 
                  $item[\'stars\'] ?? 
                  $item[\'rate\'] ?? 
                  $item[\'score\'] ?? 
                  $item[\'averageRating\'] ?? 
                  0;
                  
        return (float) $rating;
    }

    private function extractText($item)
    {
        return trim($item[\'text\'] ?? 
               $item[\'comment\'] ?? 
               $item[\'message\'] ?? 
               $item[\'content\'] ?? 
               $item[\'reviewText\'] ?? 
               $item[\'body\'] ?? 
               \'\');
    }

    private function extractOrganizationId($url)
    {
        $patterns = [
            \'/org\\/(?:[^\\/]+\\/)?(\\d+)/\',
            \'/organization\\/(\\d+)/\',
            \'/maps\\/(\\d+)/\',
            \'/biz\\/(\\d+)/\',
            \'/\\/(\\d{5,})\\/?/\',
            \'/-\\/org\\/(?:[^\\/]+\\/)?(\\d+)/\',
            \'/organizations\\/(\\d+)/\',
            \'/reviews\\/(\\d+)/\',
            \'/oid=(\\d+)/\',
            \'/id=(\\d+)/\'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                Log::info(\'Extracted organization ID\', [\'id\' => $matches[1]]);
                return $matches[1];
            }
        }
        
        // Пробуем найти любое 6+ значное число
        if (preg_match(\'/(\\d{6,})/\', $url, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
}
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

    public function reviews()
    {
        return view('yandex-maps.reviews');
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

            return redirect()->route('yandex-maps.reviews')
                ->with('success', 'URL успешно сохранен!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Ошибка сохранения: ' . $e->getMessage());
        }
    }

    public function fetchReviews(Request $request)
    {
        $request->validate(['url' => 'required|url']);

        // Получаем HTML страницы с отзывами
        $html = $this->fetchPageContent($request->url);
        
        if (!$html) {
            return response()->json([
                'success' => false,
                'error' => 'Не удалось загрузить страницу с отзывами'
            ], 404);
        }

        // Парсим отзывы из HTML
        $reviews = $this->parseReviewsFromHtml($html);

        if (empty($reviews)) {
            return response()->json([
                'success' => false,
                'error' => 'Отзывы не найдены на странице'
            ], 404);
        }

        $stats = $this->calculateStats($reviews);

        return response()->json([
            'success' => true,
            'reviews' => $reviews,
            'stats' => $stats
        ]);
    }

    private function fetchPageContent($url)
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
                'Cache-Control' => 'no-cache',
                'Pragma' => 'no-cache'
            ])->timeout(15)->get($url);

            if ($response->successful()) {
                return $response->body();
            }
        } catch (\Exception $e) {
            Log::error('Error fetching page: ' . $e->getMessage());
        }

        return null;
    }

    private function parseReviewsFromHtml($html)
    {
        $reviews = [];
        
        // Ищем JSON данные в HTML
        $pattern = '/<script[^>]*>\s*window\.__INITIAL_STATE__\s*=\s*({.+?});\s*<\/script>/';
        if (preg_match($pattern, $html, $matches)) {
            $data = json_decode($matches[1], true);
            if ($data) {
                // Ищем отзывы в структуре данных
                $items = $this->extractReviewsFromData($data);
                if (!empty($items)) {
                    return $items;
                }
            }
        }

        // Если не нашли JSON, ищем отзывы в HTML структуре
        $reviews = $this->extractReviewsFromHtml($html);
        
        return $reviews;
    }

    private function extractReviewsFromData($data)
    {
        $reviews = [];
        
        // Рекурсивно ищем массив с отзывами
        array_walk_recursive($data, function($value, $key) use (&$reviews) {
            if ($key === 'reviews' && is_array($value)) {
                foreach ($value as $item) {
                    if (isset($item['text']) && isset($item['author'])) {
                        $reviews[] = [
                            'author' => $item['author']['name'] ?? $item['user']['name'] ?? 'Аноним',
                            'date' => $item['date'] ?? $item['createdAt'] ?? date('Y-m-d'),
                            'rating' => $item['rating'] ?? $item['stars'] ?? 5,
                            'text' => $item['text'] ?? $item['comment'] ?? ''
                        ];
                    }
                }
            }
        });
        
        return $reviews;
    }

    private function extractReviewsFromHtml($html)
    {
        $reviews = [];
        
        // Простой парсинг HTML (можно улучшить)
        $dom = new \DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new \DOMXPath($dom);
        
        // Ищем элементы с классами отзывов
        $nodes = $xpath->query("//div[contains(@class, 'review')]");
        
        foreach ($nodes as $node) {
            $author = '';
            $date = '';
            $rating = 5;
            $text = '';
            
            // Парсим автора
            $authorNodes = $xpath->query(".//*[contains(@class, 'author')]", $node);
            if ($authorNodes->length > 0) {
                $author = trim($authorNodes->item(0)->textContent);
            }
            
            // Парсим дату
            $dateNodes = $xpath->query(".//*[contains(@class, 'date')]", $node);
            if ($dateNodes->length > 0) {
                $date = trim($dateNodes->item(0)->textContent);
            }
            
            // Парсим текст
            $textNodes = $xpath->query(".//*[contains(@class, 'text')]", $node);
            if ($textNodes->length > 0) {
                $text = trim($textNodes->item(0)->textContent);
            }
            
            if (!empty($text)) {
                $reviews[] = [
                    'author' => $author ?: 'Аноним',
                    'date' => $date ?: date('Y-m-d'),
                    'rating' => $rating,
                    'text' => $text
                ];
            }
        }
        
        return $reviews;
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
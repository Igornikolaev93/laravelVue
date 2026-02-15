<?php

namespace App\Http\Controllers;

use App\Models\YandexMapsSetting;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class YandexMapsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'yandex_maps_url' => 'required|url',
            ]);

            $settings = YandexMapsSetting::updateOrCreate(
                ['id' => 1],
                ['yandex_maps_url' => $validated['yandex_maps_url']]
            );

            $orgId = $this->extractOrganizationId($validated['yandex_maps_url']);
            
            if ($orgId) {
                $this->fetchReviewsAndRating($orgId, $settings);
            }

            return redirect()->route('yandex-maps.index')->with('success', 'Data fetched successfully!');
        }

        $settings = YandexMapsSetting::first();
        
        if (!$settings || !$settings->yandex_maps_url) {
            return view('yandex-maps.connect');
        }

        $reviews = session('yandex_reviews', []);
        
        $perPage = 5;
        $currentPage = $request->get('page', 1);
        $paginatedReviews = new LengthAwarePaginator(
            array_slice($reviews, ($currentPage - 1) * $perPage, $perPage),
            count($reviews),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('yandex-maps.index', [
            'settings' => $settings,
            'reviews' => $paginatedReviews
        ]);
    }

    private function fetchReviewsAndRating($orgId, $settings)
    {
        $client = new Client([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept' => 'application/json',
                'Accept-Language' => 'ru-RU,ru;q=0.9'
            ],
            'timeout' => 15
        ]);

        try {
            // Получаем отзывы через неофициальный API Яндекс Карт
            $response = $client->get("https://yandex.ru/maps/api/organizations/{$orgId}/reviews", [
                'query' => [
                    'lang' => 'ru_RU',
                    'page' => 1,
                    'pageSize' => 20,
                    'sortBy' => 'date'
                ]
            ]);
            
            $data = json_decode($response->getBody(), true);
            
            if (isset($data['reviews']) && is_array($data['reviews'])) {
                $reviews = [];
                foreach ($data['reviews'] as $item) {
                    // Извлекаем текст отзыва
                    $text = '';
                    if (isset($item['text'])) {
                        $text = $item['text'];
                    } elseif (isset($item['pros'])) {
                        $text = $item['pros'];
                        if (isset($item['cons'])) {
                            $text .= ' ' . $item['cons'];
                        }
                    }
                    
                    $reviews[] = [
                        'author' => $item['author']['name'] ?? $item['user']['name'] ?? 'Аноним',
                        'date' => date('Y-m-d', strtotime($item['date'] ?? $item['createdAt'] ?? 'now')),
                        'rating' => $item['rating'] ?? $item['stars'] ?? null,
                        'text' => trim($text)
                    ];
                }
                
                session(['yandex_reviews' => $reviews]);
            }
            
            // Обновляем рейтинг и количество
            if (isset($data['rating'])) {
                $settings->rating = $data['rating'];
                $settings->total_reviews = $data['total'] ?? count($data['reviews'] ?? []);
                $settings->save();
            }
            
        } catch (\Exception $e) {
            Log::error('Yandex Maps API error: ' . $e->getMessage());
            session(['yandex_reviews' => []]);
        }
    }

    private function extractOrganizationId($url)
    {
        // Ищем ID организации в разных форматах URL
        $patterns = [
            '/\/org\/(?:[^\/]+\/)?(\d+)/',
            '/organization\/(\d+)/',
            '/maps\/(\d+)/',
            '/biz\/(\d+)/'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
}

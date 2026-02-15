<?php

namespace App\Http\Controllers;

use App\Models\YandexMapsSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YandexMapsController extends Controller
{
    private $apiKey = '98ee2162-88df-47ac-8a2d-26df528afe73';
    
    public function index(Request $request)
    {
        $settings = YandexMapsSetting::first();
        
        return view('yandex-maps.index', [
            'settings' => $settings
        ]);
    }

    public function settings(Request $request)
    {
        $settings = YandexMapsSetting::first();
        
        return view('yandex-maps.settings', [
            'settings' => $settings
        ]);
    }

    public function connect(Request $request)
    {
        $validated = $request->validate([
            'yandex_maps_url' => 'required|url',
        ]);

        YandexMapsSetting::updateOrCreate(
            ['id' => 1],
            ['yandex_maps_url' => $validated['yandex_maps_url']]
        );

        return redirect()->route('yandex-maps.settings')->with('success', 'URL saved successfully!');
    }

    public function fetchReviews(Request $request)
    {
        $request->validate([
            'url' => 'required|url'
        ]);

        $orgId = $this->extractOrganizationId($request->url);
        
        if (!$orgId) {
            return response()->json(['error' => 'Invalid organization URL'], 400);
        }

        $reviews = $this->fetchYandexApiReviews($orgId);

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
            '/-\/org\/(?:[^\/]+\/)?(\d+)/',
            '/organizations\/(\d+)/'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }

    private function fetchYandexApiReviews($orgId)
    {
        try {
            $response = Http::timeout(15)->get('https://search-maps.yandex.ru/v1/', [
                'apikey' => $this->apiKey,
                'text' => $orgId,
                'type' => 'biz',
                'lang' => 'ru_RU',
                'results' => 1
            ]);

            if ($response->successful() && isset($response->json()['features'][0])) {
                return $this->fetchReviewsFromYandex($orgId);
            }
        } catch (\Exception $e) {
            Log::error('API Error: ' . $e->getMessage());
        }

        return [];
    }

    private function fetchReviewsFromYandex($orgId)
    {
        $endpoints = [
            "https://yandex.ru/maps/api/organizations/{$orgId}/reviews?lang=ru&pageSize=100",
            "https://yandex.ru/maps-api/v2/organizations/{$orgId}/reviews?lang=ru_RU&pageSize=100",
        ];

        foreach ($endpoints as $endpoint) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept' => 'application/json',
                    'Referer' => 'https://yandex.ru/maps/',
                ])->timeout(15)->get($endpoint);

                if ($response->successful()) {
                    $data = $response->json();
                    $reviews = $this->parseReviews($data);
                    if (!empty($reviews)) return $reviews;
                }
            } catch (\Exception $e) {
                Log::warning("Endpoint failed: {$endpoint}", ['error' => $e->getMessage()]);
            }
        }

        return [];
    }

    private function parseReviews($data)
    {
        $reviews = [];
        $reviewItems = $data['reviews'] ?? $data['data']['reviews'] ?? $data['items'] ?? [];
        
        if (!is_array($reviewItems)) return [];

        foreach ($reviewItems as $item) {
            $review = [
                'author' => $item['author']['name'] ?? $item['user']['name'] ?? 'Аноним',
                'date' => $item['date'] ?? $item['createdAt'] ?? date('Y-m-d'),
                'rating' => $item['rating'] ?? $item['rate'] ?? 0,
                'text' => $item['text'] ?? $item['comment'] ?? ''
            ];
            if (!empty($review['text'])) $reviews[] = $review;
        }
        
        return $reviews;
    }
}

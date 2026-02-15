<?php

namespace App\Http\Controllers;

use App\Models\YandexMapsSetting;
use Illuminate\Http\Request;
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

            YandexMapsSetting::updateOrCreate(
                ['id' => 1],
                ['yandex_maps_url' => $validated['yandex_maps_url']]
            );

            return redirect()->route('yandex-maps.index')
                ->with('success', 'URL saved. Loading reviews...');
        }

        $settings = YandexMapsSetting::first();
        
        if (!$settings || !$settings->yandex_maps_url) {
            return view('yandex-maps.connect');
        }

        return view('yandex-maps.index', [
            'settings' => $settings
        ]);
    }

    public function fetchReviews(Request $request)
    {
        $settings = YandexMapsSetting::first();

        if (!$settings || !$settings->yandex_maps_url) {
            return response()->json(['error' => 'Yandex Maps URL not set.'], 400);
        }

        $orgId = $this->extractOrganizationId($settings->yandex_maps_url);

        if (!$orgId) {
            return response()->json(['error' => 'Invalid Yandex Maps URL format.'], 400);
        }

        $client = new Client([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept' => 'application/json',
                'Accept-Language' => 'ru-RU,ru;q=0.9'
            ],
            'timeout' => 15
        ]);

        try {
            $response = $client->get("https://yandex.ru/maps/api/organizations/{$orgId}/reviews", [
                'query' => [
                    'lang' => 'ru_RU',
                    'page' => $request->get('page', 1),
                    'pageSize' => 5,
                    'sortBy' => 'date'
                ]
            ]);
            
            $data = json_decode($response->getBody(), true);

            if (!isset($data['reviews']) || !is_array($data['reviews'])) {
                return response()->json(['error' => 'No reviews found.'], 404);
            }
            
            $reviews = [];
            foreach ($data['reviews'] as $item) {
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

            if (isset($data['rating'])) {
                $settings->rating = $data['rating'];
                $settings->total_reviews = $data['total'] ?? count($data['reviews'] ?? []);
                $settings->save();
            }
            
            return response()->json([
                'reviews' => $reviews,
                'total_reviews' => $data['total'] ?? 0,
                'rating' => $data['rating'] ?? 0
            ]);

        } catch (\Exception $e) {
            Log::error('Yandex Maps API error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch reviews.'], 500);
        }
    }

    private function extractOrganizationId($url)
    {
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

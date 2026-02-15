<?php

namespace App\Http\Controllers;

use App\Models\YandexMapsSetting;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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

            // Fetch reviews using the API method
            $reviews = $this->fetchReviewsFromApi($validated['yandex_maps_url']);
            
            // Save reviews to the session
            session(['yandex_reviews' => $reviews]);

            return redirect()->route('yandex-maps.index')->with('success', 'URL saved and reviews fetched successfully!');
        }

        $settings = YandexMapsSetting::first();
        
        if (!$settings || !$settings->yandex_maps_url) {
            return view('yandex-maps.connect');
        }

        // Get reviews from the session
        $reviews = session('yandex_reviews', []);
        
        // Paginate the reviews
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

    private function fetchReviewsFromApi($url)
    {
        $orgId = $this->extractOrganizationId($url);
        
        if (!$orgId) {
            return [];
        }

        $client = new \GuzzleHttp\Client([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept' => 'application/json',
            ],
            'timeout'  => 10,
        ]);

        try {
            // Try various API endpoints
            $endpoints = [
                "https://yandex.ru/maps/api/organizations/{$orgId}/reviews?lang=ru&page=1&pageSize=50",
                "https://yandex.ru/maps-api/v2/organizations/{$orgId}/reviews?lang=ru_RU&pageSize=50",
            ];

            foreach ($endpoints as $endpoint) {
                try {
                    $response = $client->get($endpoint);
                    $data = json_decode($response->getBody(), true);
                    
                    $parsedReviews = $this->parseApiResponse($data);
                    
                    if (!empty($parsedReviews)) {
                        return $parsedReviews;
                    }
                } catch (\Exception $e) {
                    Log::warning("API endpoint failed: {$endpoint}. Error: " . $e->getMessage());
                    continue;
                }
            }
        } catch (\Exception $e) {
            Log::error('API fetch error: ' . $e->getMessage());
        }

        return [];
    }

    private function parseApiResponse($data)
    {
        $reviews = [];
        
        $reviewItems = $data['reviews'] ?? $data['data']['reviews'] ?? [];

        if (is_array($reviewItems)) {
            foreach ($reviewItems as $item) {
                $reviews[] = [
                    'author' => $item['author']['name'] ?? $item['user']['name'] ?? 'Аноним',
                    'date' => isset($item['date']) ? date('Y-m-d', strtotime($item['date'])) : (isset($item['createdAt']) ? date('Y-m-d', strtotime($item['createdAt'])) : date('Y-m-d')),
                    'rating' => $item['rating'] ?? $item['stars'] ?? null,
                    'text' => $item['text'] ?? $item['comment'] ?? ''
                ];
            }
        }
        
        return $reviews;
    }

    private function extractOrganizationId($url)
    {
        $patterns = [
            '/\/org\/(?:[^\/]+\/)?(\d+)/',
            '/organization\/(\d+)/',
            '/maps\/(\d+)/',
            '/biz\/(\d+)/',
            '/\/(\d{5,})\/reviews/'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
}

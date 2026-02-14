<?php

namespace App\Http\Controllers;

use App\Models\YandexMapsSettings;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use GuzzleHttp\Client;
use DiDom\Document;
use Illuminate\Support\Facades\Log;

class YandexMapsController extends Controller
{
    public function index(Request $request)
    {
        $settings = YandexMapsSettings::first();
        $reviews = [];

        if ($settings && $settings->yandex_maps_url) {
            try {
                $client = new Client([
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                        'Accept-Language' => 'ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
                        'Cache-Control' => 'no-cache',
                        'Pragma' => 'no-cache'
                    ],
                    'timeout' => 30,
                    'verify' => false
                ]);
                
                $response = $client->request('GET', $settings->yandex_maps_url, ['http_errors' => false]);
                $html = (string) $response->getBody();
                $document = new Document($html);
                
                $this->updateRatingAndReviewsCount($document, $settings);
                
                Log::info('Trying to parse reviews from JSON-LD');
                $jsonScripts = $document->find('script[type="application/ld+json"]');

                foreach ($jsonScripts as $script) {
                    $jsonContent = json_decode($script->text(), true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        continue;
                    }
                    $foundReviews = $this->findReviewsInJson($jsonContent);
                    if (!empty($foundReviews)) {
                        $reviews = $foundReviews;
                        Log::info('Found ' . count($reviews) . ' reviews in JSON-LD');
                        break;
                    }
                }

                if (empty($reviews)) {
                    Log::info('JSON-LD parsing failed. Falling back to HTML parsing.');
                    $reviewElements = $this->findReviews($document);
                    if (!empty($reviewElements)) {
                        foreach ($reviewElements as $element) {
                            $review = $this->parseReview($element);
                            if (!empty($review['text']) || !empty($review['author'])) {
                                $reviews[] = $review;
                            }
                        }
                    }
                }
                
                Log::info('Total found ' . count($reviews) . ' reviews');

            } catch (\Exception $e) {
                Log::error('Failed to parse Yandex reviews: ' . $e->getMessage());
            }
        }

        $sort = $request->get('sort', 'newest');
        if (!empty($reviews)) {
            usort($reviews, fn($a, $b) => ($sort === 'newest') ? strcmp($b['date'], $a['date']) : strcmp($a['date'], $b['date']));
        }

        $perPage = 5;
        $currentPage = $request->get('page', 1);
        $currentPageReviews = array_slice($reviews, ($currentPage - 1) * $perPage, $perPage);
        $paginatedReviews = new LengthAwarePaginator($currentPageReviews, count($reviews), $perPage, $currentPage, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return view('yandex-maps.index', [
            'settings' => $settings, 
            'reviews' => $paginatedReviews, 
            'sort' => $sort
        ]);
    }
    
    private function updateRatingAndReviewsCount($document, $settings)
    {
        // ... (existing code) ...
    }
    
    private function findReviewsInJson($data, $depth = 0)
    {
        if ($depth > 10) return [];

        if (is_array($data)) {
            if (isset($data['review']) && is_array($data['review'])) {
                return $this->parseJsonReviews($data['review']);
            }
            if (isset($data['reviews']) && is_array($data['reviews'])) {
                return $this->parseJsonReviews($data['reviews']);
            }

            foreach ($data as $key => $value) {
                $result = $this->findReviewsInJson($value, $depth + 1);
                if (!empty($result)) {
                    return $result;
                }
            }
        }
        return [];
    }

    private function parseJsonReviews($jsonReviews)
    {
        $parsed = [];
        foreach ($jsonReviews as $item) {
            $review = [
                'author' => $item['author']['name'] ?? $item['author'] ?? 'N/A',
                'rating' => $item['reviewRating']['ratingValue'] ?? $item['ratingValue'] ?? 'N/A',
                'text'   => $item['reviewBody'] ?? $item['description'] ?? '',
                'date'   => $item['datePublished'] ?? $item['dateCreated'] ?? '',
            ];

            if (!empty($review['text']) || $review['author'] !== 'N/A') {
                $parsed[] = $review;
            }
        }
        return $parsed;
    }
    
    private function findReviews($document)
    {
        // ... (existing code) ...
    }
    
    private function parseReview($element)
    {
        // ... (existing code) ...
    }

    public function settings()
    {
        // ... (existing code) ...
    }

    public function saveSettings(Request $request)
    {
        // ... (existing code) ...
    }
}

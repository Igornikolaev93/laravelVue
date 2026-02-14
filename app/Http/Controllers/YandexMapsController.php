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
        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'yandex_maps_url' => 'required|url',
            ]);

            YandexMapsSettings::updateOrCreate(
                ['id' => 1],
                ['yandex_maps_url' => $validated['yandex_maps_url']]
            );

            return redirect()->route('yandex-maps.index')->with('success', 'URL saved. Fetching reviews...');
        }

        $settings = YandexMapsSettings::first();
        $reviews = [];

        if ($settings && $settings->yandex_maps_url) {
            try {
                $client = new Client([
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                        'Accept-Language' => 'ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
                    ],
                    'timeout' => 30,
                    'verify' => false
                ]);
                
                $response = $client->request('GET', $settings->yandex_maps_url, ['http_errors' => false]);
                $html = (string) $response->getBody();
                $document = new Document($html);
                
                $this->updateRatingAndReviewsCount($document, $settings);
                
                $jsonScripts = $document->find('script[type="application/ld+json"]');
                foreach ($jsonScripts as $script) {
                    $jsonContent = json_decode($script->text(), true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $foundReviews = $this->findReviewsInJson($jsonContent);
                        if (!empty($foundReviews)) {
                            $reviews = $foundReviews;
                            break;
                        }
                    }
                }

                if (empty($reviews)) {
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
            } catch (\Exception $e) {
                Log::error('Failed to parse Yandex reviews: ' . $e->getMessage());
                return back()->with('error', 'Failed to fetch reviews. Please check the URL.');
            }
        }

        $sort = $request->get('sort', 'newest');
        if (!empty($reviews)) {
            usort($reviews, fn($a, $b) => ($sort === 'newest') ? strcmp($b['date'], $a['date']) : strcmp($a['date'], $b['date']));
        }

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
            'reviews' => $paginatedReviews, 
            'sort' => $sort
        ]);
    }
    
    private function updateRatingAndReviewsCount($document, $settings){ /* ... */ }
    
    private function findReviewsInJson($data, $depth = 0){ /* ... */ }

    private function parseJsonReviews($jsonReviews){ /* ... */ }
    
    private function findReviews($document){ /* ... */ }
    
    private function parseReview($element){ /* ... */ }
}

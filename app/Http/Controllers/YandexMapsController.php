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
                $client = new Client(['headers' => ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36']]);
                $response = $client->request('GET', $settings->yandex_maps_url, ['http_errors' => false]);
                $html = (string) $response->getBody();
                $document = new Document($html);

                if ($document->has('.business-summary-rating-badge-view__rating-value')) {
                    $settings->rating = $document->first('.business-summary-rating-badge-view__rating-value')->text();
                }

                if ($document->has('.business-summary-rating-badge-view__reviews-count')) {
                    $settings->total_reviews = $document->first('.business-summary-rating-badge-view__reviews-count')->text();
                }
                $settings->save();


                $reviewElements = $document->find('.business-review-view');

                foreach ($reviewElements as $element) {
                    $author = $element->first('.business-review-view__author .business-user-title__title') ? $element->first('.business-review-view__author .business-user-title__title')->text() : 'N/A';
                    $text = $element->first('.business-review-view__body-text') ? $element->first('.business-review-view__body-text')->text() : '';
                    $date = $element->first('.business-review-view__date') ? $element->first('.business-review-view__date')->text() : '';

                    $ratingValue = 'N/A';
                    $ratingElement = $element->first('meta[itemprop="ratingValue"]');
                    if ($ratingElement) {
                        $ratingValue = $ratingElement->getAttribute('content');
                    }


                    $reviews[] = [
                        'author' => trim($author),
                        'rating' => trim($ratingValue),
                        'text'   => trim($text),
                        'date'   => trim($date),
                    ];
                }
            } catch (\Exception $e) {
                Log::error('Failed to fetch or parse Yandex reviews: ' . $e->getMessage());
            }
        }

        $sort = $request->get('sort', 'newest');

        if ($sort === 'newest') {
            usort($reviews, fn($a, $b) => strcmp($b['date'], $a['date']));
        } else {
            usort($reviews, fn($a, $b) => strcmp($a['date'], $b['date']));
        }

        $perPage = 5;
        $currentPage = $request->get('page', 1);
        $currentPageReviews = array_slice($reviews, ($currentPage - 1) * $perPage, $perPage);
        $paginatedReviews = new LengthAwarePaginator($currentPageReviews, count($reviews), $perPage, $currentPage, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);


        return view('yandex-maps.index', ['settings' => $settings, 'reviews' => $paginatedReviews, 'sort' => $sort]);
    }

    public function settings()
    {
        $settings = YandexMapsSettings::first();
        return view('yandex-maps.settings', compact('settings'));
    }

    public function saveSettings(Request $request)
    {
        $validated = $request->validate([
            'yandex_maps_url' => 'required|url',
        ]);

        YandexMapsSettings::updateOrCreate([
            'id' => 1,
        ], [
            'yandex_maps_url' => $validated['yandex_maps_url'],
        ]);

        return redirect()->route('yandex-maps.index')->with('success', 'Settings saved successfully!');
    }
}

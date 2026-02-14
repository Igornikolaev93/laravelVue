<?php

namespace App\Http\Controllers;

use App\Models\YandexMapsSetting;
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
            $validated = $request->validate(['yandex_maps_url' => 'required|url']);
            YandexMapsSetting::updateOrCreate(['id' => 1], ['yandex_maps_url' => $validated['yandex_maps_url']]);
            return redirect()->route('yandex-maps.index')->with('success', 'URL saved. Fetching reviews...');
        }

        $settings = YandexMapsSetting::first();
        if (!$settings || !$settings->yandex_maps_url) return view('yandex-maps.connect');

        $reviews = [];
        try {
            $client = new Client(['headers' => ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'], 'timeout' => 30, 'verify' => false]);
            $response = $client->get($settings->yandex_maps_url);
            $document = new Document((string) $response->getBody());
            
            $this->updateRating($document, $settings);
            
            foreach ($document->find('script[type="application/ld+json"]') as $script) {
                $json = json_decode($script->text(), true);
                if ($json && $reviews = $this->findReviewsInJson($json)) break;
            }
            
            if (!$reviews) {
                foreach ($document->find('[class*="review"], [class*="Review"]') as $el) {
                    if ($review = $this->parseReview($el)) $reviews[] = $review;
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to parse Yandex reviews: ' . $e->getMessage());
            return back()->with('error', 'Failed to fetch reviews. Please check the URL.');
        }

        $sort = $request->get('sort', 'newest');
        if ($reviews) usort($reviews, fn($a, $b) => $sort === 'newest' ? strtotime($b['date']) - strtotime($a['date']) : strtotime($a['date']) - strtotime($b['date']));

        $page = $request->get('page', 1);
        $perPage = 5;
        $paginated = new LengthAwarePaginator(
            array_slice($reviews, ($page - 1) * $perPage, $perPage),
            count($reviews), $perPage, $page, ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('yandex-maps.index', ['settings' => $settings, 'reviews' => $paginated, 'sort' => $sort]);
    }
    
    private function updateRating($doc, $settings)
    {
        foreach ($doc->find('script[type="application/ld+json"]') as $script) {
            $json = json_decode($script->text(), true);
            if (isset($json['aggregateRating'])) {
                $settings->rating = $json['aggregateRating']['ratingValue'] ?? null;
                $settings->total_reviews = $json['aggregateRating']['reviewCount'] ?? null;
                $settings->save();
                return;
            }
        }
    }

    private function findReviewsInJson($data, $depth = 0)
    {
        if ($depth > 10 || !is_array($data)) return [];
        
        if (isset($data['@type']) && $data['@type'] === 'Review') return $this->parseJsonReviews([$data]);
        if (isset($data['review'])) return $this->parseJsonReviews($data['review']);
        if (isset($data['reviews'])) return $this->parseJsonReviews($data['reviews']);
        
        foreach ($data as $value) {
            if (is_array($value) && $result = $this->findReviewsInJson($value, $depth + 1)) return $result;
        }
        return [];
    }

    private function parseJsonReviews($reviews)
    {
        $result = [];
        foreach ((array) $reviews as $r) {
            if (!is_array($r)) continue;
            $result[] = [
                'author' => $r['author']['name'] ?? $r['author'] ?? 'Аноним',
                'date' => $r['datePublished'] ?? $r['dateCreated'] ?? date('Y-m-d'),
                'rating' => $r['reviewRating']['ratingValue'] ?? null,
                'text' => $r['reviewBody'] ?? $r['description'] ?? $r['text'] ?? '',
            ];
        }
        return $result;
    }
    
    private function parseReview($el)
    {
        $text = trim($el->text());
        if (strlen($text) < 10) return null;
        
        return [
            'author' => trim($el->first('[class*="author"], [class*="name"]')?->text() ?? 'Аноним'),
            'date' => date('Y-m-d', strtotime($el->first('[class*="date"], time')?->text() ?? 'now') ?: time()),
            'rating' => (float) ($el->first('[class*="rating"], [class*="stars"]')?->text() ?? 0),
            'text' => $text,
        ];
    }
}
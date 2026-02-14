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
            $validated = $request->validate(['yandex_maps_url' => 'required|url']);
            YandexMapsSetting::updateOrCreate(['id' => 1], ['yandex_maps_url' => $validated['yandex_maps_url']]);
            return redirect()->route('yandex-maps.index')->with('success', 'URL saved. Fetching reviews...');
        }

        $settings = YandexMapsSetting::first();
        if (!$settings || !$settings->yandex_maps_url) {
            return view('yandex-maps.connect');
        }

        $reviews = [];
        
        try {
            $client = new Client([
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
                ],
                'timeout' => 30,
                'verify' => false
            ]);
            
            $response = $client->get($settings->yandex_maps_url);
            $html = (string) $response->getBody();
            
            // Поиск данных в скриптах
            preg_match_all('/<script[^>]*>(.*?)<\/script>/is', $html, $matches);
            
            foreach ($matches[1] as $script) {
                if (strpos($script, 'reviews') === false && strpos($script, 'отзыв') === false) {
                    continue;
                }
                
                // Поиск JSON
                if (preg_match('/\{.*"reviews".*\}/s', $script, $jsonMatch)) {
                    $json = preg_replace(['/^[^{]*/', '/[^}]*$/'], '', $jsonMatch[0]);
                    $data = json_decode($json, true);
                    
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $reviews = $this->extractReviews($data);
                        if (!empty($reviews)) break;
                    }
                }
                
                // Поиск INITIAL_STATE
                if (preg_match('/window\.__INITIAL_STATE__\s*=\s*(\{.*?\});/s', $script, $stateMatch)) {
                    $data = json_decode($stateMatch[1], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $reviews = $this->extractFromInitialState($data);
                        if (!empty($reviews)) break;
                    }
                }
            }
            
            // Мета-теги
            if (empty($reviews)) {
                preg_match('/itemprop="ratingValue"\s+content="([^"]*)"/i', $html, $r);
                preg_match('/itemprop="reviewCount"\s+content="([^"]*)"/i', $html, $c);
                
                $settings->rating = isset($r[1]) ? (float) $r[1] : null;
                $settings->total_reviews = isset($c[1]) ? (int) $c[1] : 0;
                $settings->save();
            }
            
        } catch (\Exception $e) {
            Log::error('Yandex fetch failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to fetch reviews. Please check the URL.');
        }

        // Сортировка
        $sort = $request->get('sort', 'newest');
        if ($reviews) {
            usort($reviews, fn($a, $b) => $sort === 'newest' 
                ? strtotime($b['date']) - strtotime($a['date'])
                : strtotime($a['date']) - strtotime($b['date']));
        }

        // Пагинация
        $page = $request->get('page', 1);
        $perPage = 5;
        $paginated = new LengthAwarePaginator(
            array_slice($reviews, ($page - 1) * $perPage, $perPage),
            count($reviews), $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('yandex-maps.index', compact('settings', 'paginated', 'sort'));
    }
    
    private function extractReviews($data, $depth = 0)
    {
        if ($depth > 10 || !is_array($data)) return [];
        
        $reviews = [];
        
        // Прямой поиск
        if (isset($data['reviews']) && is_array($data['reviews'])) {
            foreach ($data['reviews'] as $r) {
                if (is_array($r) && isset($r['text'])) {
                    $reviews[] = [
                        'author' => $r['author']['name'] ?? $r['author'] ?? $r['user']['name'] ?? 'Аноним',
                        'date' => $this->formatDate($r['date'] ?? $r['created'] ?? $r['datePublished'] ?? null),
                        'rating' => $r['rating'] ?? $r['stars'] ?? null,
                        'text' => $r['text'] ?? $r['content'] ?? $r['reviewBody'] ?? '',
                    ];
                }
            }
        }
        
        // Рекурсивный поиск
        foreach ($data as $value) {
            if (is_array($value)) {
                $reviews = array_merge($reviews, $this->extractReviews($value, $depth + 1));
            }
        }
        
        return $reviews;
    }
    
    private function extractFromInitialState($data)
    {
        if (!is_array($data)) return [];
        
        $reviews = [];
        
        if (isset($data['reviews']['items']) && is_array($data['reviews']['items'])) {
            foreach ($data['reviews']['items'] as $r) {
                if (isset($r['text'])) {
                    $reviews[] = [
                        'author' => $r['author']['name'] ?? $r['user']['name'] ?? 'Аноним',
                        'date' => $this->formatDate($r['date'] ?? $r['createdAt'] ?? null),
                        'rating' => $r['rating'] ?? $r['stars'] ?? null,
                        'text' => $r['text'] ?? $r['content'] ?? '',
                    ];
                }
            }
        }
        
        return $reviews;
    }
    
    private function formatDate($date)
    {
        if (!$date) return date('Y-m-d');
        $timestamp = is_numeric($date) ? $date : strtotime($date);
        return $timestamp ? date('Y-m-d', $timestamp) : date('Y-m-d');
    }
}
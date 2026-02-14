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
        if (!$settings || !$settings->yandex_maps_url) {
            return view('yandex-maps.connect');
        }

        $reviews = [];
        try {
            // Извлекаем ID организации из URL
            $orgId = $this->extractOrgId($settings->yandex_maps_url);
            
            if ($orgId) {
                // Пробуем получить отзывы через API Яндекс Карт
                $reviews = $this->fetchReviewsFromApi($orgId);
            }
            
            // Если API не сработал, пробуем парсинг HTML
            if (empty($reviews)) {
                $reviews = $this->parseHtmlReviews($settings->yandex_maps_url);
            }
            
            // Обновляем рейтинг и количество отзывов
            if (!empty($reviews)) {
                $this->updateSettingsFromReviews($settings, $reviews);
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to fetch Yandex reviews: ' . $e->getMessage());
            return back()->with('error', 'Failed to fetch reviews. Please check the URL.');
        }

        // Сортировка
        $sort = $request->get('sort', 'newest');
        if ($reviews) {
            usort($reviews, fn($a, $b) => $sort === 'newest' 
                ? strtotime($b['date'] ?? '1970-01-01') - strtotime($a['date'] ?? '1970-01-01')
                : strtotime($a['date'] ?? '1970-01-01') - strtotime($b['date'] ?? '1970-01-01'));
        }

        // Пагинация
        $page = $request->get('page', 1);
        $perPage = 5;
        $paginated = new LengthAwarePaginator(
            array_slice($reviews, ($page - 1) * $perPage, $perPage),
            count($reviews), $perPage, $page, ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('yandex-maps.index', compact('settings', 'paginated', 'sort'));
    }
    
    /**
     * Извлечение ID организации из URL Яндекс Карт
     */
    private function extractOrgId($url)
    {
        // Паттерны для разных форматов URL Яндекс Карт
        $patterns = [
            '/org\/([^\/]+)/',
            '/oid=(\d+)/',
            '/business\/(\d+)/',
            '/profile\/(\d+)/'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
    
    /**
     * Получение отзывов через API Яндекс Карт
     */
    private function fetchReviewsFromApi($orgId)
    {
        $reviews = [];
        
        try {
            // Пробуем разные эндпоинты API
            $endpoints = [
                "https://yandex.ru/maps/api/org/{$orgId}/reviews",
                "https://yandex.ru/maps/api/business/{$orgId}/reviews",
                "https://yandex.ru/maps/api/profile/{$orgId}/reviews"
            ];
            
            $client = new Client(['timeout' => 10, 'verify' => false]);
            
            foreach ($endpoints as $endpoint) {
                try {
                    $response = $client->get($endpoint, [
                        'headers' => [
                            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                            'Accept' => 'application/json',
                            'X-Requested-With' => 'XMLHttpRequest'
                        ],
                        'query' => [
                            'lang' => 'ru',
                            'limit' => 50
                        ]
                    ]);
                    
                    $data = json_decode($response->getBody(), true);
                    
                    if (isset($data['reviews']) && is_array($data['reviews'])) {
                        foreach ($data['reviews'] as $review) {
                            $reviews[] = [
                                'author' => $this->getAuthorFromApi($review),
                                'date' => $this->getDateFromApi($review),
                                'rating' => $this->getRatingFromApi($review),
                                'text' => $this->getTextFromApi($review)
                            ];
                        }
                        break;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
            
        } catch (\Exception $e) {
            Log::error('API fetch failed: ' . $e->getMessage());
        }
        
        return $reviews;
    }
    
    /**
     * Парсинг HTML страницы
     */
    private function parseHtmlReviews($url)
    {
        $reviews = [];
        
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
            
            $response = $client->get($url);
            $html = (string) $response->getBody();
            $document = new Document($html);
            
            // Поиск в JSON-LD
            foreach ($document->find('script[type="application/ld+json"]') as $script) {
                $json = json_decode($script->text(), true);
                if ($json && isset($json['review'])) {
                    return $this->parseJsonReviews($json['review']);
                }
                if ($json && isset($json['reviews'])) {
                    return $this->parseJsonReviews($json['reviews']);
                }
            }
            
            // Поиск в HTML структуре
            $selectors = [
                '[class*="review"]:not([class*="review-form"])',
                '[class*="Review"]',
                '[data-testid*="review"]',
                '.business-review',
                '.reviews-list > div',
                '.feedback-item'
            ];
            
            foreach ($selectors as $selector) {
                $elements = $document->find($selector);
                if (count($elements) > 0) {
                    foreach ($elements as $element) {
                        if ($review = $this->parseReviewElement($element)) {
                            $reviews[] = $review;
                        }
                    }
                    break;
                }
            }
            
        } catch (\Exception $e) {
            Log::error('HTML parse failed: ' . $e->getMessage());
        }
        
        return $reviews;
    }
    
    /**
     * Парсинг элемента отзыва из HTML
     */
    private function parseReviewElement($element)
    {
        $text = '';
        $author = 'Аноним';
        $date = date('Y-m-d');
        $rating = null;
        
        try {
            // Поиск текста
            $textSelectors = ['[class*="text"]', '[class*="content"]', 'p', '[itemprop="reviewBody"]'];
            foreach ($textSelectors as $selector) {
                if ($el = $element->first($selector)) {
                    $text = trim($el->text());
                    if (strlen($text) > 10) break;
                }
            }
            
            if (strlen($text) < 10) return null;
            
            // Поиск автора
            $authorSelectors = ['[class*="author"]', '[class*="name"]', '[itemprop="author"]', 'strong'];
            foreach ($authorSelectors as $selector) {
                if ($el = $element->first($selector)) {
                    $authorText = trim($el->text());
                    if (!empty($authorText) && strlen($authorText) < 50) {
                        $author = $authorText;
                        break;
                    }
                }
            }
            
            // Поиск даты
            $dateSelectors = ['[class*="date"]', '[datetime]', 'time', '[itemprop="datePublished"]'];
            foreach ($dateSelectors as $selector) {
                if ($el = $element->first($selector)) {
                    $dateText = $el->attr('datetime') ?? $el->text();
                    if ($timestamp = strtotime($dateText)) {
                        $date = date('Y-m-d', $timestamp);
                        break;
                    }
                }
            }
            
            // Поиск рейтинга
            $ratingSelectors = ['[class*="rating"]', '[class*="stars"]', '[itemprop="ratingValue"]'];
            foreach ($ratingSelectors as $selector) {
                if ($el = $element->first($selector)) {
                    $ratingText = $el->text();
                    if (preg_match('/(\d+[,.]?\d*)/', $ratingText, $matches)) {
                        $rating = (float) str_replace(',', '.', $matches[1]);
                        break;
                    }
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Parse element error: ' . $e->getMessage());
        }
        
        return [
            'author' => $author,
            'date' => $date,
            'rating' => $rating,
            'text' => $text
        ];
    }
    
    /**
     * Парсинг JSON отзывов
     */
    private function parseJsonReviews($reviews)
    {
        $result = [];
        $reviews = is_array($reviews) && isset($reviews[0]) ? $reviews : [$reviews];
        
        foreach ($reviews as $review) {
            if (!is_array($review)) continue;
            
            $result[] = [
                'author' => $this->extractAuthorFromJson($review),
                'date' => $this->extractDateFromJson($review),
                'rating' => $this->extractRatingFromJson($review),
                'text' => $this->extractTextFromJson($review)
            ];
        }
        
        return $result;
    }
    
    private function extractAuthorFromJson($review)
    {
        if (isset($review['author'])) {
            return is_array($review['author']) 
                ? ($review['author']['name'] ?? 'Аноним')
                : $review['author'];
        }
        return 'Аноним';
    }
    
    private function extractDateFromJson($review)
    {
        return $review['datePublished'] 
            ?? $review['dateCreated'] 
            ?? $review['date'] 
            ?? date('Y-m-d');
    }
    
    private function extractRatingFromJson($review)
    {
        if (isset($review['reviewRating'])) {
            return is_array($review['reviewRating'])
                ? ($review['reviewRating']['ratingValue'] ?? null)
                : $review['reviewRating'];
        }
        return null;
    }
    
    private function extractTextFromJson($review)
    {
        return $review['reviewBody'] 
            ?? $review['description'] 
            ?? $review['text'] 
            ?? '';
    }
    
    private function getAuthorFromApi($review)
    {
        return $review['user']['name'] 
            ?? $review['author'] 
            ?? $review['userName'] 
            ?? 'Аноним';
    }
    
    private function getDateFromApi($review)
    {
        return date('Y-m-d', strtotime($review['date'] 
            ?? $review['createdAt'] 
            ?? $review['datePublished'] 
            ?? 'now'));
    }
    
    private function getRatingFromApi($review)
    {
        return $review['rating'] 
            ?? $review['stars'] 
            ?? $review['score'] 
            ?? null;
    }
    
    private function getTextFromApi($review)
    {
        return $review['text'] 
            ?? $review['content'] 
            ?? $review['comment'] 
            ?? $review['reviewText'] 
            ?? '';
    }
    
    private function updateSettingsFromReviews($settings, $reviews)
    {
        // Вычисляем средний рейтинг
        $ratings = array_column($reviews, 'rating');
        $ratings = array_filter($ratings, fn($r) => $r !== null && $r > 0);
        
        if (!empty($ratings)) {
            $settings->rating = round(array_sum($ratings) / count($ratings), 1);
        }
        
        $settings->total_reviews = count($reviews);
        $settings->save();
    }
}
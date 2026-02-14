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
                            $parsedReview = $this->parseApiReview($review);
                            if ($this->isValidReview($parsedReview)) {
                                $reviews[] = $parsedReview;
                            }
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
                if ($json) {
                    $jsonReviews = $this->findReviewsInJson($json);
                    if (!empty($jsonReviews)) {
                        foreach ($jsonReviews as $review) {
                            if ($this->isValidReview($review)) {
                                $reviews[] = $review;
                            }
                        }
                    }
                }
            }
            
            // Если не нашли в JSON, ищем в HTML
            if (empty($reviews)) {
                // Более специфичные селекторы для Яндекс Карт
                $selectors = [
                    'div[class*="review"][class*="item"]',
                    'div[data-testid="review"]',
                    'div[class*="business-review"]',
                    'li[class*="review"]',
                    'div[class*="feedback-item"]'
                ];
                
                foreach ($selectors as $selector) {
                    $elements = $document->find($selector);
                    foreach ($elements as $element) {
                        $review = $this->parseReviewElement($element);
                        if ($this->isValidReview($review)) {
                            $reviews[] = $review;
                        }
                    }
                    if (!empty($reviews)) break;
                }
            }
            
        } catch (\Exception $e) {
            Log::error('HTML parse failed: ' . $e->getMessage());
        }
        
        return $reviews;
    }
    
    /**
     * Проверка, является ли отзыв валидным
     */
    private function isValidReview($review)
    {
        // Проверяем, что это не призыв оставить отзыв
        $spamPhrases = [
            'оцените',
            'напишите отзыв',
            'ваше мнение',
            'поделитесь впечатлениями',
            'оставьте отзыв',
            'как вам',
            'оценить',
            'написать отзыв'
        ];
        
        $text = mb_strtolower($review['text'] ?? '');
        foreach ($spamPhrases as $phrase) {
            if (mb_strpos($text, $phrase) !== false) {
                return false;
            }
        }
        
        // Проверяем длину текста (минимум 10 символов)
        if (strlen(trim($text)) < 10) {
            return false;
        }
        
        // Проверяем, что автор не "Аноним" или что есть текст
        if ($review['author'] === 'Аноним' && empty($text)) {
            return false;
        }
        
        // Проверяем, что дата не сегодняшняя (если это призыв оставить отзыв)
        if ($review['date'] === date('Y-m-d') && empty($review['rating'])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Поиск отзывов в JSON
     */
    private function findReviewsInJson($data, $depth = 0)
    {
        if ($depth > 10 || !is_array($data)) return [];
        
        $reviews = [];
        
        // Проверяем, является ли текущий элемент отзывом
        if (isset($data['@type']) && $data['@type'] === 'Review' && isset($data['reviewBody'])) {
            $review = $this->parseJsonReview($data);
            if ($this->isValidReview($review)) {
                $reviews[] = $review;
            }
        }
        
        // Проверяем наличие массива отзывов
        if (isset($data['review']) && is_array($data['review'])) {
            foreach ((array) $data['review'] as $r) {
                if (is_array($r)) {
                    $review = $this->parseJsonReview($r);
                    if ($this->isValidReview($review)) {
                        $reviews[] = $review;
                    }
                }
            }
        }
        
        if (isset($data['reviews']) && is_array($data['reviews'])) {
            foreach ($data['reviews'] as $r) {
                if (is_array($r)) {
                    $review = $this->parseJsonReview($r);
                    if ($this->isValidReview($review)) {
                        $reviews[] = $review;
                    }
                }
            }
        }
        
        // Рекурсивный поиск
        foreach ($data as $value) {
            if (is_array($value)) {
                $found = $this->findReviewsInJson($value, $depth + 1);
                if (!empty($found)) {
                    $reviews = array_merge($reviews, $found);
                }
            }
        }
        
        return $reviews;
    }
    
    /**
     * Парсинг отзыва из JSON
     */
    private function parseJsonReview($review)
    {
        return [
            'author' => $this->extractAuthorFromJson($review),
            'date' => $this->extractDateFromJson($review),
            'rating' => $this->extractRatingFromJson($review),
            'text' => $this->extractTextFromJson($review)
        ];
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
            // Поиск текста (исключаем кнопки и формы)
            $textElements = $element->find('[class*="text"]:not(button):not(input), [class*="content"]:not(button):not(input), p:not(button):not(input)');
            foreach ($textElements as $el) {
                $potentialText = trim($el->text());
                // Пропускаем если это кнопка или призыв к действию
                if (strlen($potentialText) > 10 && !preg_match('/^(оцените|напишите|показать|ещё|читать)/iu', $potentialText)) {
                    $text = $potentialText;
                    break;
                }
            }
            
            if (empty($text)) return null;
            
            // Поиск автора
            $authorElements = $element->find('[class*="author"]:not(button), [class*="name"]:not(button), [itemprop="author"]:not(button), strong:not(button)');
            foreach ($authorElements as $el) {
                $authorText = trim($el->text());
                if (!empty($authorText) && strlen($authorText) < 50 && !preg_match('/^(оцените|напишите)/iu', $authorText)) {
                    $author = $authorText;
                    break;
                }
            }
            
            // Поиск даты
            $dateElements = $element->find('[class*="date"]:not(button), [datetime]:not(button), time:not(button), [itemprop="datePublished"]:not(button)');
            foreach ($dateElements as $el) {
                $dateText = $el->attr('datetime') ?? $el->text();
                $dateText = preg_replace('/[^0-9.\-\s]/u', '', $dateText);
                if ($timestamp = strtotime($dateText)) {
                    $date = date('Y-m-d', $timestamp);
                    break;
                }
            }
            
            // Поиск рейтинга
            $ratingElements = $element->find('[class*="rating"]:not(button):not(input), [class*="stars"]:not(button):not(input), [itemprop="ratingValue"]:not(button):not(input)');
            foreach ($ratingElements as $el) {
                $ratingText = $el->text();
                if (preg_match('/(\d+[,.]?\d*)/', $ratingText, $matches)) {
                    $rating = (float) str_replace(',', '.', $matches[1]);
                    if ($rating > 0 && $rating <= 5) {
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
    
    private function extractAuthorFromJson($review)
    {
        if (isset($review['author'])) {
            if (is_array($review['author'])) {
                return $review['author']['name'] ?? 'Аноним';
            }
            return $review['author'];
        }
        return 'Аноним';
    }
    
    private function extractDateFromJson($review)
    {
        $date = $review['datePublished'] ?? $review['dateCreated'] ?? $review['date'] ?? null;
        if ($date && $timestamp = strtotime($date)) {
            return date('Y-m-d', $timestamp);
        }
        return date('Y-m-d');
    }
    
    private function extractRatingFromJson($review)
    {
        if (isset($review['reviewRating'])) {
            if (is_array($review['reviewRating'])) {
                return $review['reviewRating']['ratingValue'] ?? null;
            }
            return $review['reviewRating'];
        }
        return null;
    }
    
    private function extractTextFromJson($review)
    {
        return $review['reviewBody'] ?? $review['description'] ?? $review['text'] ?? '';
    }
    
    private function parseApiReview($review)
    {
        return [
            'author' => $this->getAuthorFromApi($review),
            'date' => $this->getDateFromApi($review),
            'rating' => $this->getRatingFromApi($review),
            'text' => $this->getTextFromApi($review)
        ];
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
        $date = $review['date'] ?? $review['createdAt'] ?? $review['datePublished'] ?? 'now';
        return date('Y-m-d', strtotime($date));
    }
    
    private function getRatingFromApi($review)
    {
        $rating = $review['rating'] ?? $review['stars'] ?? $review['score'] ?? null;
        return $rating ? (float) $rating : null;
    }
    
    private function getTextFromApi($review)
    {
        return $review['text'] ?? $review['content'] ?? $review['comment'] ?? $review['reviewText'] ?? '';
    }
    
    private function updateSettingsFromReviews($settings, $reviews)
    {
        // Вычисляем средний рейтинг
        $ratings = array_column($reviews, 'rating');
        $ratings = array_filter($ratings, fn($r) => is_numeric($r) && $r > 0 && $r <= 5);
        
        if (!empty($ratings)) {
            $settings->rating = round(array_sum($ratings) / count($ratings), 1);
        }
        
        $settings->total_reviews = count($reviews);
        $settings->save();
    }
}
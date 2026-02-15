<?php

namespace App\Http\Controllers;

use App\Models\YandexMapsSetting;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use GuzzleHttp\Client;
use DiDom\Document;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class YandexMapsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'yandex_maps_url' => 'required|url',
            ]);
            
            // Извлекаем ID организации из URL
            $orgId = $this->extractOrganizationId($validated['yandex_maps_url']);
            
            if (!$orgId) {
                return back()->with('error', 'Invalid Yandex Maps URL format');
            }
            
            // Используем API Яндекс Карт (требуется API ключ)
            $apiKey = config('services.yandex_maps.api_key');
            $client = new Client();
            
            try {
                $response = $client->get("https://search-maps.yandex.ru/v1/", [
                    'query' => [
                        'apikey' => $apiKey,
                        'text' => $orgId,
                        'lang' => 'ru_RU',
                        'type' => 'biz'
                    ]
                ]);
                
                $data = json_decode($response->getBody(), true);
                
                // Сохраняем данные в БД
                YandexMapsSetting::updateOrCreate(
                    ['id' => 1],
                    [
                        'yandex_maps_url' => $validated['yandex_maps_url'],
                        'rating' => $data['features'][0]['properties']['CompanyMetaData']['rating'] ?? null,
                        'total_reviews' => $data['features'][0]['properties']['CompanyMetaData']['reviews'] ?? 0
                    ]
                );
                
                return redirect()->route('yandex-maps.index')->with('success', 'Data fetched successfully');
                
            } catch (\Exception $e) {
                Log::error('Yandex Maps API error: ' . $e->getMessage());
                return back()->with('error', 'Failed to fetch data from Yandex Maps API');
            }
        }
        
        $settings = YandexMapsSetting::first();

        if (!$settings || !$settings->yandex_maps_url) {
            return view('yandex-maps.connect');
        }

        $reviews = [];

        if ($settings && $settings->yandex_maps_url) {
            try {
                // Используем новый клиент с расширенными заголовками
                $client = new Client([
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                        'Accept-Language' => 'ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
                        'Cache-Control' => 'no-cache, no-store, must-revalidate',
                        'Pragma' => 'no-cache',
                        'Expires' => '0',
                    ],
                    'timeout' => 30,
                    'verify' => false,
                    'allow_redirects' => true,
                    'cookies' => true,
                ]);
                
                // Добавляем параметр для обхода кеша
                $url = $settings->yandex_maps_url . (strpos($settings->yandex_maps_url, '?') === false ? '?' : '&') . '_=' . time();
                
                $response = $client->request('GET', $url, ['http_errors' => false]);
                $html = (string) $response->getBody();
                
                // Сохраняем HTML для отладки
                if (config('app.debug')) {
                    file_put_contents(storage_path('logs/yandex_debug_' . date('Y-m-d_H-i-s') . '.html'), $html);
                }
                
                $document = new Document($html);
                
                // Принудительно обновляем рейтинг и количество
                $this->updateRatingAndReviewsCount($document, $settings);
                
                // Ищем отзывы в JSON-LD
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

                // Если не нашли в JSON, ищем в HTML
                if (empty($reviews)) {
                    $reviewElements = $this->findReviews($document);
                    if (!empty($reviewElements)) {
                        foreach ($reviewElements as $element) {
                            $review = $this->parseReview($element);
                            if (!empty($review['text']) && strlen($review['text']) > 20) {
                                $reviews[] = $review;
                            }
                        }
                    }
                }
                
                // Если все еще нет отзывов, но есть рейтинг - показываем сообщение
                if (empty($reviews) && $settings->rating) {
                    Log::info('No reviews found, but rating exists: ' . $settings->rating);
                }
                
            } catch (\Exception $e) {
                Log::error('Failed to parse Yandex reviews: ' . $e->getMessage());
                return back()->with('error', 'Failed to fetch reviews. Please check the URL.');
            }
        }

        $sort = $request->get('sort', 'newest');
        if (!empty($reviews)) {
            usort($reviews, function($a, $b) use ($sort) {
                if ($sort === 'newest') {
                    return strtotime($b['date']) - strtotime($a['date']);
                }
                return strtotime($a['date']) - strtotime($b['date']);
            });
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
    
    private function extractOrganizationId($url)
    {
        // Извлекаем ID организации из URL
        // Пример: https://yandex.ru/maps/org/.../...
        preg_match('/org\/([^\/]+)/', $url, $matches);
        return $matches[1] ?? null;
    }

    private function updateRatingAndReviewsCount($document, $settings)
    {
        try {
            // Поиск в JSON-LD
            $jsonScripts = $document->find('script[type="application/ld+json"]');
            foreach ($jsonScripts as $script) {
                $jsonContent = json_decode($script->text(), true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    if (isset($jsonContent['aggregateRating'])) {
                        $settings->rating = $jsonContent['aggregateRating']['ratingValue'] ?? $settings->rating;
                        $settings->total_reviews = $jsonContent['aggregateRating']['reviewCount'] ?? $settings->total_reviews;
                        $settings->save();
                        return;
                    }
                }
            }

            // Поиск в meta-тегах
            $ratingMeta = $document->first('meta[itemprop="ratingValue"]');
            if ($ratingMeta && $ratingMeta->hasAttribute('content')) {
                $settings->rating = (float) $ratingMeta->attr('content');
            }
            
            $countMeta = $document->first('meta[itemprop="reviewCount"]');
            if ($countMeta && $countMeta->hasAttribute('content')) {
                $settings->total_reviews = (int) $countMeta->attr('content');
            }

            $settings->save();
        } catch (\Exception $e) {
            Log::error('Failed to update rating and reviews count: ' . $e->getMessage());
        }
    }
    
    private function findReviewsInJson($data, $depth = 0)
    {
        if ($depth > 10) return [];
        
        if (is_array($data)) {
            if (isset($data['@type']) && $data['@type'] === 'Review' && isset($data['reviewBody'])) {
                return $this->parseJsonReviews([$data]);
            }
            
            if (isset($data['review']) && is_array($data['review'])) {
                return $this->parseJsonReviews($data['review']);
            }
            
            if (isset($data['reviews']) && is_array($data['reviews'])) {
                return $this->parseJsonReviews($data['reviews']);
            }
            
            foreach ($data as $value) {
                if (is_array($value)) {
                    $result = $this->findReviewsInJson($value, $depth + 1);
                    if (!empty($result)) {
                        return $result;
                    }
                }
            }
        }
        
        return [];
    }

    private function parseJsonReviews($jsonReviews)
    {
        $reviews = [];
        
        if (!is_array($jsonReviews)) {
            return $reviews;
        }
        
        if (isset($jsonReviews['@type']) && $jsonReviews['@type'] === 'Review') {
            $jsonReviews = [$jsonReviews];
        }
        
        foreach ($jsonReviews as $review) {
            if (!is_array($review)) continue;
            
            $author = 'Аноним';
            if (isset($review['author'])) {
                if (is_string($review['author'])) {
                    $author = $review['author'];
                } elseif (is_array($review['author']) && isset($review['author']['name'])) {
                    $author = $review['author']['name'];
                }
            }
            
            $date = $review['datePublished'] ?? $review['dateCreated'] ?? date('Y-m-d');
            
            $rating = null;
            if (isset($review['reviewRating'])) {
                if (is_array($review['reviewRating']) && isset($review['reviewRating']['ratingValue'])) {
                    $rating = (float) $review['reviewRating']['ratingValue'];
                }
            }
            
            $text = $review['reviewBody'] ?? $review['description'] ?? $review['text'] ?? '';
            
            if (!empty($text) || $author !== 'Аноним') {
                $reviews[] = [
                    'author' => $author,
                    'date' => $date,
                    'rating' => $rating,
                    'text' => $text,
                ];
            }
        }
        
        return $reviews;
    }
    
    private function findReviews($document)
    {
        $selectors = [
            'div[class*="review"][class*="item"]',
            'div[data-testid="review"]',
            'div[class*="business-review"]',
            'li[class*="review"]',
            'article[class*="review"]',
        ];
        
        foreach ($selectors as $selector) {
            $elements = $document->find($selector);
            if (!empty($elements)) {
                return $elements;
            }
        }
        
        return [];
    }
    
    private function parseReview($element)
    {
        try {
            // Ищем текст
            $text = '';
            $textSelectors = ['p', 'div[class*="text"]', 'div[class*="content"]', 'span[class*="text"]'];
            foreach ($textSelectors as $selector) {
                $textEl = $element->first($selector);
                if ($textEl) {
                    $text = trim($textEl->text());
                    if (strlen($text) > 20) break;
                }
            }
            
            if (strlen($text) < 20) return null;
            
            // Ищем автора
            $author = 'Аноним';
            $authorSelectors = ['strong', 'b', 'span[class*="author"]', 'div[class*="author"]', 'span[class*="name"]'];
            foreach ($authorSelectors as $selector) {
                $authorEl = $element->first($selector);
                if ($authorEl) {
                    $authorText = trim($authorEl->text());
                    if (!empty($authorText) && strlen($authorText) < 50 && !preg_match('/^(написать|оставить)/iu', $authorText)) {
                        $author = $authorText;
                        break;
                    }
                }
            }
            
            // Ищем дату
            $date = date('Y-m-d');
            $dateSelectors = ['time', 'span[class*="date"]', 'div[class*="date"]', '[datetime]'];
            foreach ($dateSelectors as $selector) {
                $dateEl = $element->first($selector);
                if ($dateEl) {
                    $dateText = $dateEl->attr('datetime') ?? $dateEl->text();
                    $timestamp = strtotime($dateText);
                    if ($timestamp !== false && $timestamp <= time()) {
                        $date = date('Y-m-d', $timestamp);
                        break;
                    }
                }
            }
            
            // Ищем рейтинг
            $rating = null;
            $ratingSelectors = ['div[class*="rating"]', 'span[class*="rating"]', 'meta[itemprop="ratingValue"]'];
            foreach ($ratingSelectors as $selector) {
                $ratingEl = $element->first($selector);
                if ($ratingEl) {
                    if ($ratingEl->tag === 'meta' && $ratingEl->hasAttribute('content')) {
                        $rating = (float) $ratingEl->attr('content');
                    } else {
                        $ratingText = $ratingEl->text();
                        if (preg_match('/(\d+[,.]?\d*)/', $ratingText, $matches)) {
                            $rating = (float) str_replace(',', '.', $matches[1]);
                        }
                    }
                    if ($rating && $rating > 0 && $rating <= 5) break;
                }
            }
            
            return [
                'author' => $author,
                'date' => $date,
                'rating' => $rating,
                'text' => $text,
            ];
            
        } catch (\Exception $e) {
            Log::error('Error parsing review: ' . $e->getMessage());
            return null;
        }
    }
}

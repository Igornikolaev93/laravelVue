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
                    'verify' => false // Отключаем проверку SSL для локальной разработки
                ]);
                
                $response = $client->request('GET', $settings->yandex_maps_url, [
                    'http_errors' => false
                ]);
                
                $html = (string) $response->getBody();
                
                // Сохраняем HTML для отладки (только если нужно)
                // file_put_contents(storage_path('yandex-page.html'), $html);
                
                $document = new Document($html);
                
                // Пробуем разные селекторы для рейтинга
                $ratingSelectors = [
                    '.business-summary-rating-badge-view__rating-value',
                    '.business-rating-badge-view__rating',
                    '.card-summary-view__rating-value',
                    '[class*="rating"][class*="value"]',
                    '.rating-view__value',
                    'span[class*="_rating"]'
                ];
                
                foreach ($ratingSelectors as $selector) {
                    if ($document->has($selector)) {
                        $ratingElement = $document->first($selector);
                        $settings->rating = trim($ratingElement->text());
                        break;
                    }
                }
                
                // Селекторы для количества отзывов
                $countSelectors = [
                    '.business-summary-rating-badge-view__reviews-count',
                    '.business-rating-badge-view__reviews-count',
                    '.card-summary-view__reviews-count',
                    '[class*="reviews"][class*="count"]',
                    'span[class*="_count"]'
                ];
                
                foreach ($countSelectors as $selector) {
                    if ($document->has($selector)) {
                        $countElement = $document->first($selector);
                        $countText = $countElement->text();
                        preg_match('/\d+/', $countText, $matches);
                        $settings->total_reviews = $matches[0] ?? $countText;
                        break;
                    }
                }
                
                $settings->save();
                
                // СПЕЦИАЛЬНО ДЛЯ ТЕСТИРОВАНИЯ: создаем тестовые отзывы, если настоящие не найдены
                $reviewElements = $this->findReviews($document);
                
                if (empty($reviewElements)) {
                    // Добавляем тестовые данные для проверки отображения
                    Log::info('No reviews found, using test data');
                    
                    // Закомментируйте эти строки, если не хотите видеть тестовые данные
                    // $reviews = $this->getTestReviews();
                } else {
                    foreach ($reviewElements as $element) {
                        $review = $this->parseReview($element);
                        if (!empty($review['text']) || !empty($review['author'])) {
                            $reviews[] = $review;
                        }
                    }
                }
                
                Log::info('Found ' . count($reviews) . ' reviews');
                
            } catch (\Exception $e) {
                Log::error('Failed to fetch or parse Yandex reviews: ' . $e->getMessage());
                Log::error($e->getTraceAsString());
                
                // Для отладки добавляем тестовые данные при ошибке
                // $reviews = $this->getTestReviews();
            }
        }

        // Сортировка
        $sort = $request->get('sort', 'newest');
        
        if (!empty($reviews)) {
            if ($sort === 'newest') {
                usort($reviews, fn($a, $b) => strcmp($b['date'], $a['date']));
            } else {
                usort($reviews, fn($a, $b) => strcmp($a['date'], $b['date']));
            }
        }

        // Пагинация
        $perPage = 5;
        $currentPage = $request->get('page', 1);
        $currentPageReviews = array_slice($reviews, ($currentPage - 1) * $perPage, $perPage);
        $paginatedReviews = new LengthAwarePaginator(
            $currentPageReviews, 
            count($reviews), 
            $perPage, 
            $currentPage, 
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('yandex-maps.index', [
            'settings' => $settings, 
            'reviews' => $paginatedReviews, 
            'sort' => $sort
        ]);
    }
    
    /**
     * Поиск элементов отзывов разными способами
     */
    private function findReviews($document)
    {
        $reviewSelectors = [
            '.business-review-view',
            '.business-reviews-card-view__review',
            '.review-view',
            '.reviews-list__item',
            '[data-review-id]',
            '.business-review-card',
            'div[class*="review"]',
            '.review-item'
        ];
        
        foreach ($reviewSelectors as $selector) {
            $elements = $document->find($selector);
            if (!empty($elements)) {
                Log::info("Found reviews with selector '$selector': " . count($elements));
                return $elements;
            }
        }
        
        return [];
    }
    
    /**
     * Парсинг одного отзыва
     */
    private function parseReview($element)
    {
        $review = [
            'author' => 'N/A',
            'rating' => 'N/A',
            'text' => '',
            'date' => ''
        ];
        
        // Поиск автора
        $authorSelectors = [
            '.business-review-view__author',
            '.business-review-view__author-name',
            '.review-author',
            '.user-name',
            '[class*="author"]',
            '[class*="user"]'
        ];
        
        foreach ($authorSelectors as $selector) {
            $authorElement = $element->first($selector);
            if ($authorElement) {
                $author = $authorElement->text();
                if (!empty(trim($author))) {
                    $review['author'] = trim($author);
                    break;
                }
            }
        }
        
        // Поиск текста
        $textSelectors = [
            '.business-review-view__body-text',
            '.review-text',
            '.review-body',
            '[class*="text"]',
            '[class*="message"]'
        ];
        
        foreach ($textSelectors as $selector) {
            $textElement = $element->first($selector);
            if ($textElement) {
                $text = $textElement->text();
                if (!empty(trim($text))) {
                    $review['text'] = trim($text);
                    break;
                }
            }
        }
        
        // Поиск даты
        $dateSelectors = [
            '.business-review-view__date',
            '.review-date',
            '.date',
            'time',
            '[class*="date"]',
            '[class*="time"]'
        ];
        
        foreach ($dateSelectors as $selector) {
            $dateElement = $element->first($selector);
            if ($dateElement) {
                $date = $dateElement->text();
                if (!empty(trim($date))) {
                    $review['date'] = trim($date);
                    break;
                }
            }
        }
        
        // Поиск рейтинга
        // Сначала пробуем meta тег
        $ratingMeta = $element->first('meta[itemprop="ratingValue"]');
        if ($ratingMeta) {
            $review['rating'] = $ratingMeta->getAttribute('content');
        } else {
            // Пробуем найти по классам
            $ratingSelectors = [
                '.business-review-view__rating',
                '.review-rating',
                '[class*="rating"]',
                '[class*="stars"]'
            ];
            
            foreach ($ratingSelectors as $selector) {
                $ratingElement = $element->first($selector);
                if ($ratingElement) {
                    // Пробуем получить из атрибута
                    if ($ratingElement->hasAttribute('aria-label')) {
                        preg_match('/(\d+)/', $ratingElement->getAttribute('aria-label'), $matches);
                        if ($matches) {
                            $review['rating'] = $matches[0];
                            break;
                        }
                    }
                    
                    // Или из текста
                    $ratingText = $ratingElement->text();
                    preg_match('/(\d+)/', $ratingText, $matches);
                    if ($matches) {
                        $review['rating'] = $matches[0];
                        break;
                    }
                }
            }
        }
        
        return $review;
    }
    
    /**
     * Тестовые данные для проверки отображения
     */
    private function getTestReviews()
    {
        return [
            [
                'author' => 'Алексей',
                'rating' => '5',
                'text' => 'Отличное место, очень понравилось обслуживание!',
                'date' => '2025-02-10'
            ],
            [
                'author' => 'Мария',
                'rating' => '4',
                'text' => 'Хорошее заведение, но дороговато',
                'date' => '2025-02-09'
            ],
            [
                'author' => 'Дмитрий',
                'rating' => '5',
                'text' => 'Лучшее место в городе, рекомендую!',
                'date' => '2025-02-08'
            ],
            [
                'author' => 'Елена',
                'rating' => '3',
                'text' => 'Обычное место, ничего особенного',
                'date' => '2025-02-07'
            ],
            [
                'author' => 'Сергей',
                'rating' => '5',
                'text' => 'Очень вкусно и уютно',
                'date' => '2025-02-06'
            ]
        ];
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

        YandexMapsSettings::updateOrCreate(
            ['id' => 1],
            ['yandex_maps_url' => $validated['yandex_maps_url']]
        );

        return redirect()->route('yandex-maps.index')->with('success', 'Settings saved successfully!');
    }
}
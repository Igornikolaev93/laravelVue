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
            // Получаем HTML страницы
            $client = new Client([
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
                ],
                'timeout' => 30,
                'verify' => false
            ]);
            
            $response = $client->get($settings->yandex_maps_url);
            $html = (string) $response->getBody();
            
            // Сохраняем HTML для отладки
            Log::info('HTML length: ' . strlen($html));
            
            // Ищем JSON данные в HTML
            preg_match_all('/<script[^>]*type="application\/ld\+json"[^>]*>(.*?)<\/script>/s', $html, $matches);
            
            foreach ($matches[1] as $jsonContent) {
                $data = json_decode($jsonContent, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    // Ищем отзывы в JSON
                    $foundReviews = $this->extractReviewsFromJson($data);
                    if (!empty($foundReviews)) {
                        $reviews = array_merge($reviews, $foundReviews);
                    }
                }
            }
            
            // Если не нашли в JSON, ищем в HTML структуре
            if (empty($reviews)) {
                $document = new Document($html);
                
                // Ищем блоки с отзывами
                $reviewBlocks = $document->find('div[class*="review"], div[class*="Review"], div[data-testid*="review"]');
                
                foreach ($reviewBlocks as $block) {
                    $review = $this->extractReviewFromBlock($block);
                    if ($review && !empty($review['text']) && strlen($review['text']) > 10) {
                        $reviews[] = $review;
                    }
                }
            }
            
            // Обновляем информацию о рейтинге
            if (!empty($reviews)) {
                $settings->total_reviews = count($reviews);
                
                // Вычисляем средний рейтинг
                $ratings = array_column($reviews, 'rating');
                $ratings = array_filter($ratings, fn($r) => is_numeric($r) && $r > 0);
                if (!empty($ratings)) {
                    $settings->rating = round(array_sum($ratings) / count($ratings), 1);
                }
                
                $settings->save();
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
     * Извлечение отзывов из JSON
     */
    private function extractReviewsFromJson($data, $depth = 0)
    {
        if ($depth > 10 || !is_array($data)) {
            return [];
        }
        
        $reviews = [];
        
        // Проверяем, является ли текущий элемент отзывом
        if (isset($data['@type']) && $data['@type'] === 'Review' && isset($data['reviewBody'])) {
            $review = [
                'author' => $this->getJsonValue($data, 'author'),
                'date' => $this->formatDate($this->getJsonValue($data, 'datePublished') ?? $this->getJsonValue($data, 'dateCreated')),
                'rating' => $this->extractRatingFromJson($data),
                'text' => $data['reviewBody'] ?? '',
            ];
            
            if (!empty($review['text']) && strlen($review['text']) > 10) {
                $reviews[] = $review;
            }
        }
        
        // Ищем вложенные отзывы
        if (isset($data['review']) && is_array($data['review'])) {
            foreach ($data['review'] as $item) {
                $reviews = array_merge($reviews, $this->extractReviewsFromJson($item, $depth + 1));
            }
        }
        
        if (isset($data['reviews']) && is_array($data['reviews'])) {
            foreach ($data['reviews'] as $item) {
                $reviews = array_merge($reviews, $this->extractReviewsFromJson($item, $depth + 1));
            }
        }
        
        // Рекурсивный обход
        foreach ($data as $key => $value) {
            if (is_array($value) && !in_array($key, ['review', 'reviews'])) {
                $reviews = array_merge($reviews, $this->extractReviewsFromJson($value, $depth + 1));
            }
        }
        
        return $reviews;
    }
    
    /**
     * Извлечение значения из JSON
     */
    private function getJsonValue($data, $key)
    {
        if (!isset($data[$key])) {
            return null;
        }
        
        if (is_array($data[$key])) {
            return $data[$key]['name'] ?? $data[$key][0] ?? null;
        }
        
        return $data[$key];
    }
    
    /**
     * Извлечение рейтинга из JSON
     */
    private function extractRatingFromJson($data)
    {
        if (isset($data['reviewRating'])) {
            if (is_array($data['reviewRating'])) {
                return $data['reviewRating']['ratingValue'] ?? null;
            }
            return $data['reviewRating'];
        }
        return null;
    }
    
    /**
     * Извлечение отзыва из HTML блока
     */
    private function extractReviewFromBlock($block)
    {
        try {
            // Список фраз, которые указывают на то, что это не отзыв, а форма или призыв
            $skipPhrases = [
                'написать отзыв',
                'оставить отзыв',
                'ваше мнение',
                'поделитесь',
                'оцените',
                'как вам',
                'что вы думаете',
                'добавить отзыв',
                'напишите',
                'расскажите',
                'плюсы',
                'минусы',
                'комментарий',
                'достоинства',
                'недостатки',
                'впечатления',
                'оценка',
                'поставьте оценку'
            ];
            
            // Проверяем весь блок на наличие фраз-триггеров
            $fullText = strtolower(trim($block->text()));
            foreach ($skipPhrases as $phrase) {
                if (strpos($fullText, $phrase) !== false) {
                    // Если это форма ввода или призыв, пропускаем
                    if ($this->isFormElement($block)) {
                        return null;
                    }
                }
            }
            
            $text = '';
            $author = '';
            $date = '';
            $rating = null;
            
            // Ищем текст отзыва - только в параграфах или блоках с большим объемом текста
            $textElements = $block->find('p, div[class*="text"]:not([class*="form"]):not([class*="input"]), div[class*="content"]:not([class*="form"])');
            
            foreach ($textElements as $el) {
                // Проверяем, не является ли элемент полем ввода
                if ($el->hasAttribute('contenteditable') || $el->hasAttribute('role') && $el->attr('role') === 'textbox') {
                    continue;
                }
                
                $potentialText = trim($el->text());
                
                // Пропускаем короткие тексты и тексты с признаками формы
                if (strlen($potentialText) < 20) {
                    continue;
                }
                
                // Проверяем на наличие маркеров формы/ввода
                $lowerText = strtolower($potentialText);
                $hasFormMarkers = false;
                foreach (['плюсы', 'минусы', 'комментарий', 'достоинства', 'недостатки'] as $marker) {
                    if (strpos($lowerText, $marker) !== false && strlen($lowerText) < 50) {
                        $hasFormMarkers = true;
                        break;
                    }
                }
                
                if ($hasFormMarkers) {
                    continue;
                }
                
                $text = $potentialText;
                break;
            }
            
            // Если нет текста или текст слишком короткий - пропускаем
            if (empty($text) || strlen($text) < 20) {
                return null;
            }
            
            // Ищем автора (только если это не "Аноним" по умолчанию)
            $authorElements = $block->find('strong:not(:contains("Написать")), b:not(:contains("Написать")), span[class*="author"]:not(:contains("Написать")), div[class*="author"]:not(:contains("Написать"))');
            
            foreach ($authorElements as $el) {
                $authorText = trim($el->text());
                
                // Пропускаем, если это призыв к действию
                if (preg_match('/^(написать|оставить|добавить|оценить)/iu', $authorText)) {
                    continue;
                }
                
                // Пропускаем служебные надписи
                if (in_array(strtolower($authorText), ['написать отзыв', 'оставить отзыв', 'ваше имя', 'анонимно'])) {
                    continue;
                }
                
                if (!empty($authorText) && strlen($authorText) < 30) {
                    $author = $authorText;
                    break;
                }
            }
            
            // Если автор не найден, ставим "Аноним" только если есть текст отзыва
            if (empty($author)) {
                $author = 'Аноним';
            }
            
            // Ищем дату
            $dateElements = $block->find('time, span[class*="date"]:not(:contains("сегодня")), div[class*="date"]:not(:contains("сегодня")), [datetime]');
            
            foreach ($dateElements as $el) {
                $dateText = $el->attr('datetime') ?? $el->text();
                
                // Пропускаем, если это надпись "сегодня" или "вчера" без конкретной даты
                if (preg_match('/^(сегодня|вчера|только что|сейчас)$/iu', trim($dateText))) {
                    continue;
                }
                
                $dateText = preg_replace('/[^0-9.\-\s]/u', '', $dateText);
                if ($timestamp = strtotime($dateText)) {
                    // Проверяем, что дата не в будущем и не слишком старая
                    if ($timestamp <= time() && $timestamp > strtotime('-10 years')) {
                        $date = date('Y-m-d', $timestamp);
                        break;
                    }
                }
            }
            
            // Если дата не найдена, используем текущую только если это реальный отзыв
            if (empty($date)) {
                // Проверяем, есть ли признаки реального отзыва
                if (preg_match('/[а-яА-Я]{10,}/u', $text)) { // Есть русский текст достаточной длины
                    $date = date('Y-m-d');
                } else {
                    return null;
                }
            }
            
            // Ищем рейтинг
            $ratingElements = $block->find('div[class*="rating"]:not([class*="form"]), span[class*="rating"]:not([class*="form"]), meta[itemprop="ratingValue"]');
            
            foreach ($ratingElements as $el) {
                if ($el->tag === 'meta') {
                    $ratingText = $el->attr('content');
                } else {
                    $ratingText = $el->text();
                }
                
                if (preg_match('/(\d+[,.]?\d*)/', $ratingText, $matches)) {
                    $rating = (float) str_replace(',', '.', $matches[1]);
                    if ($rating > 0 && $rating <= 5) {
                        break;
                    }
                }
            }
            
            // Финальная проверка - убеждаемся, что это действительно отзыв
            if ($this->isRealReview($text, $author, $date, $rating)) {
                return [
                    'author' => $author,
                    'date' => $date,
                    'rating' => $rating,
                    'text' => $text,
                ];
            }
            
            return null;
            
        } catch (\Exception $e) {
            Log::error('Error extracting review: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Проверка, является ли элемент формой или полем ввода
     */
    private function isFormElement($element)
    {
        // Проверяем тег
        $tag = strtolower($element->tag);
        if (in_array($tag, ['form', 'input', 'textarea', 'button', 'select'])) {
            return true;
        }
        
        // Проверяем атрибуты
        if ($element->hasAttribute('contenteditable') || 
            $element->hasAttribute('role') && in_array($element->attr('role'), ['textbox', 'form'])) {
            return true;
        }
        
        // Проверяем классы
        $class = strtolower($element->attr('class') ?? '');
        if (strpos($class, 'form') !== false || 
            strpos($class, 'input') !== false || 
            strpos($class, 'textarea') !== false) {
            return true;
        }
        
        return false;
    }

    /**
     * Проверка, является ли текст реальным отзывом
     */
    private function isRealReview($text, $author, $date, $rating)
    {
        // Слишком короткий текст
        if (strlen($text) < 20) {
            return false;
        }
        
        // Текст содержит маркеры формы
        $formMarkers = ['плюсы:', 'минусы:', 'комментарий:', 'достоинства:', 'недостатки:'];
        $lowerText = strtolower($text);
        foreach ($formMarkers as $marker) {
            if (strpos($lowerText, $marker) !== false && strlen($lowerText) < 100) {
                return false;
            }
        }
        
        // Автор - служебная надпись
        if (in_array(strtolower($author), ['написать отзыв', 'оставить отзыв', 'анонимно', 'ваше имя'])) {
            return false;
        }
        
        // Текст содержит только цифры или спецсимволы
        if (preg_match('/^[^\p{L}]*$/u', $text)) {
            return false;
        }
        
        // Текст - это просто оценка без содержания
        if (preg_match('/^(оценка|rating|оценил)/iu', $text) && strlen($text) < 30) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Форматирование даты
     */
    private function formatDate($date)
    {
        if (!$date) {
            return date('Y-m-d');
        }
        
        $timestamp = is_numeric($date) ? $date : strtotime($date);
        return $timestamp ? date('Y-m-d', $timestamp) : date('Y-m-d');
    }
}

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
            $validated = $request->validate([
                'yandex_maps_url' => 'required|url',
            ]);

            // При сохранении нового URL сбрасываем старые данные
            YandexMapsSetting::updateOrCreate(
                ['id' => 1],
                [
                    'yandex_maps_url' => $validated['yandex_maps_url'],
                    'rating' => null,
                    'total_reviews' => 0
                ]
            );

            return redirect()->route('yandex-maps.index')->with('success', 'URL saved. Fetching reviews...');
        }

        $settings = YandexMapsSetting::first();

        if (!$settings || !$settings->yandex_maps_url) {
            return view('yandex-maps.connect');
        }

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
    
    private function updateRatingAndReviewsCount($document, $settings)
    {
        try {
            // Поиск рейтинга в JSON-LD
            $jsonScripts = $document->find('script[type="application/ld+json"]');
            foreach ($jsonScripts as $script) {
                $jsonContent = json_decode($script->text(), true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    if (isset($jsonContent['aggregateRating'])) {
                        $settings->rating = $jsonContent['aggregateRating']['ratingValue'] ?? null;
                        $settings->total_reviews = $jsonContent['aggregateRating']['reviewCount'] ?? null;
                        $settings->save();
                        return;
                    }
                }
            }

            // Поиск рейтинга в HTML структуре
            $ratingElement = $document->first('[class*="rating"] [class*="value"]');
            if ($ratingElement) {
                $ratingText = $ratingElement->text();
                if (preg_match('/(\d+[,.]?\d*)/', $ratingText, $matches)) {
                    $settings->rating = str_replace(',', '.', $matches[1]);
                }
            }

            $reviewsCountElement = $document->first('[class*="reviews-count"], [class*="count"]');
            if ($reviewsCountElement) {
                $countText = $reviewsCountElement->text();
                if (preg_match('/(\d+)/', $countText, $matches)) {
                    $settings->total_reviews = (int)$matches[1];
                }
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
            
            foreach ($data as $key => $value) {
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
            
            $parsedReview = [
                'author' => $this->extractAuthorFromJson($review),
                'date' => $this->extractDateFromJson($review),
                'rating' => $this->extractRatingFromJson($review),
                'text' => $this->extractTextFromJson($review),
            ];
            
            if (!empty($parsedReview['text']) || !empty($parsedReview['author'])) {
                $reviews[] = $parsedReview;
            }
        }
        
        return $reviews;
    }

    private function extractAuthorFromJson($review)
    {
        if (isset($review['author'])) {
            if (is_string($review['author'])) {
                return $review['author'];
            } elseif (is_array($review['author']) && isset($review['author']['name'])) {
                return $review['author']['name'];
            }
        }
        return 'Аноним';
    }

    private function extractDateFromJson($review)
    {
        if (isset($review['datePublished'])) {
            return $review['datePublished'];
        }
        if (isset($review['dateCreated'])) {
            return $review['dateCreated'];
        }
        return date('Y-m-d');
    }

    private function extractRatingFromJson($review)
    {
        if (isset($review['reviewRating'])) {
            if (is_array($review['reviewRating'])) {
                if (isset($review['reviewRating']['ratingValue'])) {
                    return $review['reviewRating']['ratingValue'];
                }
            }
        }
        return null;
    }

    private function extractTextFromJson($review)
    {
        if (isset($review['reviewBody'])) {
            return $review['reviewBody'];
        }
        if (isset($review['description'])) {
            return $review['description'];
        }
        if (isset($review['text'])) {
            return $review['text'];
        }
        return '';
    }
    
    private function findReviews($document)
    {
        $reviews = [];
        
        $selectors = [
            '[class*="review"]',
            '[class*="Review"]',
            '[class*="feedback"]',
            '[class*="comment"]',
            '[data-testid*="review"]',
            'article',
            '.business-review',
            '.reviews-list .review-item'
        ];
        
        foreach ($selectors as $selector) {
            $elements = $document->find($selector);
            if (count($elements) > 3) {
                return $elements;
            }
            if (!empty($elements)) {
                $reviews = array_merge($reviews, $elements);
            }
        }
        
        return array_unique($reviews);
    }
    
    private function parseReview($element)
    {
        $review = [
            'author' => 'Аноним',
            'date' => date('Y-m-d'),
            'rating' => null,
            'text' => ''
        ];
        
        try {
            $authorSelectors = [
                '[class*="author"]',
                '[class*="name"]',
                '[class*="user"]',
                'strong',
                'b',
                '[itemprop="author"]'
            ];
            
            foreach ($authorSelectors as $selector) {
                $authorElement = $element->first($selector);
                if ($authorElement) {
                    $authorText = trim($authorElement->text());
                    if (!empty($authorText) && strlen($authorText) < 50) {
                        $review['author'] = $authorText;
                        break;
                    }
                }
            }
            
            $dateSelectors = [
                '[class*="date"]',
                '[class*="time"]',
                '[datetime]',
                '[itemprop="datePublished"]',
                'time'
            ];
            
            foreach ($dateSelectors as $selector) {
                $dateElement = $element->first($selector);
                if ($dateElement) {
                    if ($dateElement->hasAttribute('datetime')) {
                        $dateText = $dateElement->attr('datetime');
                    } else {
                        $dateText = $dateElement->text();
                    }
                    
                    $timestamp = strtotime($dateText);
                    if ($timestamp !== false) {
                        $review['date'] = date('Y-m-d', $timestamp);
                        break;
                    }
                }
            }
            
            $ratingSelectors = [
                '[class*="rating"]',
                '[class*="stars"]',
                '[itemprop="ratingValue"]',
                '[aria-label*="звезд"]',
                '[aria-label*="star"]'
            ];
            
            foreach ($ratingSelectors as $selector) {
                $ratingElement = $element->first($selector);
                if ($ratingElement) {
                    $ratingText = $ratingElement->text();
                    if (preg_match('/(\d+[,.]?\d*)/', $ratingText, $matches)) {
                        $review['rating'] = (float)str_replace(',', '.', $matches[1]);
                        break;
                    }
                    
                    if ($ratingElement->hasAttribute('aria-label')) {
                        $ariaLabel = $ratingElement->attr('aria-label');
                        if (preg_match('/(\d+[,.]?\d*)/', $ariaLabel, $matches)) {
                            $review['rating'] = (float)str_replace(',', '.', $matches[1]);
                            break;
                        }
                    }
                }
            }
            
            $textSelectors = [
                '[class*="text"]',
                '[class*="content"]',
                '[class*="message"]',
                '[itemprop="reviewBody"]',
                '[class*="description"]',
                'p'
            ];
            
            foreach ($textSelectors as $selector) {
                $textElement = $element->first($selector);
                if ($textElement) {
                    $text = trim($textElement->text());
                    if (!empty($text) && strlen($text) > 10) {
                        $review['text'] = $text;
                        break;
                    }
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Error parsing review element: ' . $e->getMessage());
        }
        
        return $review;
    }
}

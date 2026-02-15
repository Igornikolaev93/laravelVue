<?php

namespace App\Http\Controllers;

use App\Models\YandexMapsSetting;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class YandexMapsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'yandex_maps_url' => 'required|url',
            ]);
            
            $orgId = $this->extractOrganizationId($validated['yandex_maps_url']);
            
            if (!$orgId) {
                return back()->with('error', 'Invalid Yandex Maps URL format. URL must contain an organization ID, e.g., "yandex.ru/maps/org/123456789".');
            }
            
            $apiKey = config('services.yandex_maps.api_key');
            if (!$apiKey) {
                Log::error('Yandex Maps API key is not set in config/services.php.');
                return back()->with('error', 'The Yandex Maps API key has not been configured. Please contact the administrator.');
            }

            $client = new Client();
            
            try {
                $response = $client->get("https://search-maps.yandex.ru/v1/", [
                    'query' => [
                        'apikey' => $apiKey,
                        'text' => $orgId,
                        'lang' => 'ru_RU',
                        'type' => 'biz',
                        'results' => 1,
                        'snippets' => 'businessrating/1.x'
                    ],
                    'http_errors' => false
                ]);
                
                $data = json_decode($response->getBody(), true);

                if (empty($data['features'])) {
                    return back()->with('error', 'Organization not found on Yandex Maps for the given URL.');
                }

                // According to the Yandex Search API docs, rating and reviews are in the 'Ratings' object.
                $companyMetaData = $data['features'][0]['properties']['CompanyMetaData'] ?? null;
                $ratingData = $companyMetaData['Ratings'] ?? null; 

                YandexMapsSetting::updateOrCreate(
                    ['id' => 1],
                    [
                        'yandex_maps_url' => $validated['yandex_maps_url'],
                        'rating' => $ratingData['score'] ?? null,
                        'total_reviews' => $ratingData['reviews'] ?? 0,
                    ]
                );
                
                return redirect()->route('yandex-maps.index')->with('success', 'Data fetched successfully!');
                
            } catch (\Exception $e) {
                Log::error('Yandex Maps API error: ' . $e->getMessage());
                return back()->with('error', 'Failed to fetch data from Yandex Maps API. See logs for more details.');
            }
        }
        
        $settings = YandexMapsSetting::first();

        if (!$settings || !$settings->yandex_maps_url) {
            return view('yandex-maps.connect');
        }

        // The new API implementation does not fetch individual reviews.
        // We pass an empty array for the 'reviews' variable to the view.
        return view('yandex-maps.index', [
            'settings' => $settings,
            'reviews' => [],
            'sort' => 'newest'
        ]);
    }
    
    private function extractOrganizationId($url)
    {
        // Extracts the numerical organization ID from a Yandex Maps URL.
        // e.g., https://yandex.ru/maps/org/some_name/123456789/?ll=... -> 123456789
        if (preg_match('/\/org\/[a-zA-Z_-]+\/([0-9]+)/', $url, $matches) || preg_match('/\/org\/([0-9]+)/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }
}

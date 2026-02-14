<?php

namespace App\Http\Controllers;

use App\Models\YandexMapsSettings;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class YandexMapsController extends Controller
{
    public function index(Request $request)
    {
        $settings = YandexMapsSettings::first();
        
        // Dummy data for reviews
        $reviews = [
            ['author' => 'John Doe', 'rating' => 5, 'text' => 'Excellent!', 'date' => '2024-05-20'],
            ['author' => 'Jane Smith', 'rating' => 4, 'text' => 'Very good.', 'date' => '2024-05-19'],
            ['author' => 'Peter Jones', 'rating' => 3, 'text' => 'Good.', 'date' => '2024-05-21'],
            ['author' => 'Mary Williams', 'rating' => 2, 'text' => 'Could be better.', 'date' => '2024-05-18'],
            ['author' => 'David Brown', 'rating' => 1, 'text' => 'Not good.', 'date' => '2024-05-22'],
            ['author' => 'Susan Davis', 'rating' => 5, 'text' => 'Amazing!', 'date' => '2024-05-17'],
            ['author' => 'Michael Miller', 'rating' => 4, 'text' => 'Great place.', 'date' => '2024-05-23'],
            ['author' => 'Linda Wilson', 'rating' => 5, 'text' => 'I love it!', 'date' => '2024-05-16'],
            ['author' => 'Robert Moore', 'rating' => 3, 'text' => 'It\'s okay.', 'date' => '2024-05-24'],
            ['author' => 'Patricia Taylor', 'rating' => 4, 'text' => 'I recommend it.', 'date' => '2024-05-15'],
        ];

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
            'url' => 'required|url',
        ]);

        YandexMapsSettings::updateOrCreate([
            'id' => 1,
        ], [
            'url' => $validated['url'],
        ]);

        return redirect()->route('yandex-maps.settings')->with('success', 'Settings saved successfully!');
    }
}

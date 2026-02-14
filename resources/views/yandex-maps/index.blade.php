@extends('layouts.app')

@section('content')
<style>
    .url-form-container form { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
    .url-input { width: 480px; height: 38px; padding: 6px 14px; border: 1px solid #DCE4EA; border-radius: 6px; }
    .submit-button { background: #339AF0; color: white; border: none; border-radius: 6px; padding: 10px 20px; font-weight: 600; cursor: pointer; height: 38px; }
    .rating-block { display: flex; align-items: center; gap: 20px; padding: 20px; background: #f6f8fa; border-radius: 6px; margin-bottom: 20px; }
    .stars { color: #ffc107; }
    .review-card { background: white; border: 1px solid #e5e5e5; border-radius: 6px; padding: 20px; margin-bottom: 20px; }
    .review-header { color: #6c757d; margin-bottom: 10px; display: flex; gap: 10px; align-items: center; }
    .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
    .alert-danger { background: #f8d7da; color: #721c24; }
    .alert-success { background: #d4edda; color: #155724; }
</style>

<div class="reviews-container">
    <div class="url-form-container">
        <form action="{{ route('yandex-maps.index') }}" method="POST">
            @csrf
            <input type="text" name="yandex_maps_url" class="url-input" placeholder="Enter Yandex Maps URL" value="{{ $settings->yandex_maps_url ?? '' }}" required>
            <button type="submit" class="submit-button">Fetch Reviews</button>
        </form>
        @if ($errors->any() || session('error'))
            <div class="alert alert-danger">{{ $errors->first() ?? session('error') }}</div>
        @endif
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
    </div>

    @if ($settings && $settings->yandex_maps_url)
        @if(isset($paginated) && $paginated->count() > 0)
            <div class="rating-block">
                <span class="platform-name">Яндекс Карты</span>
                <span class="rating-value">{{ $settings->rating ?? 'N/A' }}</span>
                <div class="stars">{{ str_repeat('★', round($settings->rating ?? 0)) . str_repeat('☆', 5 - round($settings->rating ?? 0)) }}</div>
                <span>{{ $settings->total_reviews ?? 0 }} отзывов</span>
            </div>

            @foreach ($paginated as $review)
                <div class="review-card">
                    <div class="review-header">
                        <span>{{ $review['author'] }}</span>
                        <span>{{ $review['date'] }}</span>
                        @if($review['rating'])
                            <div class="stars">{{ str_repeat('★', round($review['rating'])) . str_repeat('☆', 5 - round($review['rating'])) }}</div>
                        @endif
                    </div>
                    <div>{{ $review['text'] }}</div>
                </div>
            @endforeach

            <div class="pagination-container">{{ $paginated->appends(['sort' => $sort])->links() }}</div>
        @else
            <div class="review-card">No reviews found for this URL. Please check the URL and try again.</div>
        @endif
    @endif
</div>
@endsection
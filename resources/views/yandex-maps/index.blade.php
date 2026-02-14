@extends('layouts.app')

@section('content')
<style>
    .reviews-container {
        /* padding: 20px; */
    }
    .url-form-container {
        margin-bottom: 20px;
    }
    .url-input {
        width: 480px;
        height: 38px;
        padding: 6px 14px;
        border: 1px solid #DCE4EA;
        border-radius: 6px;
    }
    .submit-button {
        background-color: #339AF0;
        color: white;
        border: none;
        border-radius: 6px;
        padding: 10px 20px;
        font-weight: 600;
        cursor: pointer;
        height: 38px;
    }
    .rating-platform {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        padding: 20px;
        background-color: #f6f8fa;
        border-radius: 6px;
    }
    .rating-block {
        display: flex;
        align-items: center;
        margin-right: 20px;
    }
    .platform-name {
        font-size: 18px;
        font-weight: 600;
        margin-left: 10px;
    }
    .rating-stars-block {
        display: flex;
        align-items: center;
    }
    .rating-value {
        font-size: 18px;
        font-weight: 600;
        margin-right: 10px;
    }
    .stars {
        color: #ffc107;
    }
    .total-reviews {
        margin-left: 10px;
        color: #6c757d;
    }
    .review-card {
        background-color: white;
        border: 1px solid #e5e5e5;
        border-radius: 6px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .review-header {
        color: #6c757d;
        margin-bottom: 10px;
    }
    .review-author-info {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }
    .review-author {
        font-weight: 600;
        margin-right: 10px;
    }
    .review-text {
        line-height: 1.6;
    }
    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border: 1px solid transparent;
        border-radius: 4px;
    }
    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }
    .alert-success {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
    }
</style>

<div class="reviews-container">
    <div class="url-form-container">
        <form action="{{ route('yandex-maps.index') }}" method="POST" style="display: flex; align-items: center; gap: 10px;">
            @csrf
            <input type="text" name="yandex_maps_url" class="url-input" placeholder="Enter Yandex Maps URL" value="{{ $settings->yandex_maps_url ?? '' }}" required>
            <button type="submit" class="submit-button">Fetch Reviews</button>
        </form>
        @if ($errors->any())
            <div class="alert alert-danger" style="margin-top: 10px;">
                {{ $errors->first() }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger" style="margin-top: 10px;">
                {{ session('error') }}
            </div>
        @endif
        @if (session('success'))
            <div class="alert alert-success" style="margin-top: 10px;">
                {{ session('success') }}
            </div>
        @endif
    </div>

    @if ($settings && $settings->yandex_maps_url)
        @if (count($reviews) > 0)
            <!-- Rating Block -->
            <div class="rating-platform">
                <div class="rating-block">
                    <span class="platform-name">Яндекс Карты</span>
                </div>
                <div class="rating-stars-block">
                    <span class="rating-value">{{ $settings->rating ?? 'N/A' }}</span>
                    <div class="stars">
                        @php
                            $rating = round((float)($settings->rating ?? 0));
                            echo str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
                        @endphp
                    </div>
                    <div class="total-reviews">
                        {{ $settings->total_reviews ?? '0' }} отзывов
                    </div>
                </div>
            </div>

            <!-- Reviews List -->
            @forelse ($reviews as $review)
                <div class="review-card">
                    <div class="review-header">
                        <span>{{ $review['date'] }}</span>
                    </div>
                    <div class="review-author-info">
                        <span class="review-author">{{ $review['author'] }}</span>
                        <div class="stars" style="font-size: 16px;">
                            @php
                                $reviewRating = round((float)($review['rating'] ?? 0));
                                echo str_repeat('★', $reviewRating) . str_repeat('☆', 5 - $reviewRating);
                            @endphp
                        </div>
                    </div>
                    <div class="review-text">
                        {{ $review['text'] }}
                    </div>
                </div>
            @empty
                <div class="review-card">
                    <div class="review-text">
                        No reviews found for this URL, or the service is temporarily unavailable.
                    </div>
                </div>
            @endforelse

            <!-- Pagination -->
            <div class="pagination-container" style="margin-top: 20px;">
                {{ $reviews->appends(['sort' => $sort])->links() }}
            </div>
        @else
            <div class="review-card">
                <div class="review-text">
                    No reviews found for this URL. Please check the URL and try again.
                </div>
            </div>
        @endif
    @endif
</div>
@endsection

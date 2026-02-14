@extends('layouts.app')

@section('content')
<div class="container">
    <div class="main-layout">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="account-name">Hotels</div>
            <div class="menu">
                <div class="menu-item">
                    <span class="menu-icon">O</span>
                    <span>Dashboard</span>
                </div>
                <div class="menu-item active">
                    <span class="menu-icon">O</span>
                    <span>Reviews</span>
                </div>
                <div class="menu-item">
                    <span class="menu-icon">O</span>
                    <span>UI Elements</span>
                </div>
                <div class="menu-item">
                    <span class="menu-icon">O</span>
                    <span>Settings</span>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            @if ($settings && $settings->yandex_maps_url)
                <!-- Rating Block -->
                <div class="rating-platform">
                    <div class="rating-block">
                        <span class="rating-icon">Y</span>
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
                            No reviews found or the service is temporarily unavailable.
                        </div>
                    </div>
                @endforelse

                <!-- Pagination -->
                <div class="pagination-container" style="margin-top: 20px;">
                    {{ $reviews->appends(['sort' => $sort])->links() }}
                </div>

            @else
                <p>Please configure the Yandex Maps URL in the <a href="{{ route('yandex-maps.settings') }}">settings</a>.</p>
            @endif
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container">
    <div class="main-layout">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="account-name">Hotels</div>
            <div class="menu">
                <div class="menu-item active">
                    <span class="menu-icon">O</span>
                    <span>Reviews</span>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="content">

            @if ($settings && $settings->yandex_maps_url)
                <div class="url-form-container">
                    <form action="{{ route('yandex-maps.index') }}" method="POST">
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
                @if (count($reviews) > 0)
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
            @else
                <div class="url-form-container">
                    <h2>Подключить Яндекс</h2>
                    <p>Укажите ссылку на Яндекс, пример</p>
                    <form action="{{ route('yandex-maps.index') }}" method="POST">
                        @csrf
                        <input type="text" name="yandex_maps_url" class="url-input" placeholder="https://yandex.ru/maps/org/samoye_populyarnoye_kafe/1010501395/reviews/" required>
                        <button type="submit" class="submit-button">Сохранить</button>
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
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

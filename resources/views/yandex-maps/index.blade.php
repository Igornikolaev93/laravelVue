@extends('layouts.app')

@section('content')
<style>
    .url-form-container form { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
    .url-input { width: 480px; height: 38px; padding: 6px 14px; border: 1px solid #DCE4EA; border-radius: 6px; }
    .submit-button { background: #339AF0; color: white; border: none; border-radius: 6px; padding: 10px 20px; font-weight: 600; cursor: pointer; height: 38px; }
    .rating-block { display: flex; align-items: center; gap: 20px; padding: 20px; background: #f6f8fa; border-radius: 6px; margin-bottom: 20px; flex-wrap: wrap; }
    .stars { color: #ffc107; font-size: 1.2em; letter-spacing: 2px; }
    .review-card { background: white; border: 1px solid #e5e5e5; border-radius: 6px; padding: 20px; margin-bottom: 20px; }
    .review-header { color: #6c757d; margin-bottom: 10px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
    .alert-danger { background: #f8d7da; color: #721c24; }
    .alert-success { background: #d4edda; color: #155724; }
    .no-reviews { text-align: center; padding: 40px; color: #6c757d; font-style: italic; }
    .pagination { display: flex; justify-content: center; margin-top: 20px; }
    .pagination .page-link { padding: 8px 12px; margin: 0 4px; border: 1px solid #dee2e6; color: #339AF0; text-decoration: none; }
    .pagination .active .page-link { background: #339AF0; color: white; border-color: #339AF0; }
    .sort-select { padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 20px; }
</style>

<div class="reviews-container">
    <div class="url-form-container">
        <form action="{{ route('yandex-maps.index') }}" method="POST">
            @csrf
            <input type="url" name="yandex_maps_url" class="url-input" 
                   placeholder="https://yandex.ru/maps/org/..." 
                   value="{{ $settings->yandex_maps_url ?? '' }}" required>
            <button type="submit" class="submit-button">Get Reviews</button>
        </form>
        
        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif
        
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
    </div>

    @if ($settings && $settings->yandex_maps_url)
        @php
            $totalReviews = count(session('yandex_reviews', []));
            $avgRating = collect(session('yandex_reviews', []))->avg('rating');
        @endphp

        @if($totalReviews > 0)
        <div class="rating-block">
            <span class="platform-name">Яндекс Карты</span>
            
            @if($avgRating)
                <span class="rating-value">{{ number_format($avgRating, 1) }}</span>
                <div class="stars">
                    @for($i = 1; $i <= 5; $i++)
                        <span>{{ $i <= round($avgRating) ? '★' : '☆' }}</span>
                    @endfor
                </div>
            @endif
            
            <span>{{ $totalReviews }} {{ trans_choice('отзыв|отзыва|отзывов', $totalReviews) }}</span>
        </div>
        @endif

        @if($reviews && $reviews->count() > 0)
            <select class="sort-select" id="sortSelect">
                <option value="newest">Сначала новые</option>
                <option value="oldest">Сначала старые</option>
                <option value="highest">Сначала высокий рейтинг</option>
                <option value="lowest">Сначала низкий рейтинг</option>
            </select>

            <div id="reviews-list">
                @foreach ($reviews as $review)
                    <div class="review-card" 
                         data-date="{{ $review['date'] }}" 
                         data-rating="{{ $review['rating'] ?? 0 }}">
                        <div class="review-header">
                            <strong>{{ $review['author'] }}</strong>
                            <span>{{ $review['date'] }}</span>
                            @if(!empty($review['rating']))
                                <div class="stars">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span>{{ $i <= round($review['rating']) ? '★' : '☆' }}</span>
                                    @endfor
                                </div>
                            @endif
                        </div>
                        <div>{{ $review['text'] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="pagination">
                {{ $reviews->links() }}
            </div>
        @else
            <div class="no-reviews">
                Нет отзывов для данной организации
            </div>
        @endif
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sortSelect = document.getElementById('sortSelect');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const sortBy = this.value;
            const reviewsList = document.getElementById('reviews-list');
            const cards = Array.from(reviewsList.children);
            
            cards.sort((a, b) => {
                switch(sortBy) {
                    case 'newest':
                        return new Date(b.dataset.date) - new Date(a.dataset.date);
                    case 'oldest':
                        return new Date(a.dataset.date) - new Date(b.dataset.date);
                    case 'highest':
                        return (b.dataset.rating || 0) - (a.dataset.rating || 0);
                    case 'lowest':
                        return (a.dataset.rating || 0) - (b.dataset.rating || 0);
                    default:
                        return 0;
                }
            });
            
            reviewsList.innerHTML = '';
            cards.forEach(card => reviewsList.appendChild(card));
        });
    }
});
</script>
@endsection

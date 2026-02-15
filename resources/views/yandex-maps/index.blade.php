
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="main-content">
        <div class="reviews-container">
            <div class="url-form-container">
                <form action="{{ route('yandex-maps.index') }}" method="POST">
                    @csrf
                    <input type="url" 
                           name="yandex_maps_url" 
                           class="url-input" 
                           placeholder="https://yandex.ru/maps/org/..." 
                           value="{{ $settings->yandex_maps_url }}" 
                           required>
                    <button type="submit" class="submit-button">Обновить URL</button>
                    <a href="{{ route('yandex-maps.index') }}" class="change-url-link">⟲ Сбросить</a>
                </form>
                
                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif
                
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
            </div>

            <div id="fetchStatus" class="fetch-status" style="display: none;"></div>
            
            <div id="ratingBlock" class="rating-block" style="display: none;">
                <span class="platform-name">Яндекс Карты</span>
                <span class="rating-value" id="avgRating"></span>
                <div class="stars" id="starsContainer"></div>
                <span id="totalReviews"></span>
            </div>

            <select id="sortSelect" class="sort-select" style="display: none;">
                <option value="newest">Сначала новые</option>
                <option value="oldest">Сначала старые</option>
                <option value="highest">Сначала высокий рейтинг</option>
                <option value="lowest">Сначала низкий рейтинг</option>
            </select>

            <div id="reviewsList"></div>
            
            <div id="pagination" class="pagination"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const url = '{{ $settings->yandex_maps_url }}';
    
    if (url) {
        loadYandexReviews(url);
    }
});

async function loadYandexReviews(url) {
    const statusDiv = document.getElementById('fetchStatus');
    const ratingBlock = document.getElementById('ratingBlock');
    const sortSelect = document.getElementById('sortSelect');
    
    statusDiv.style.display = 'flex';
    statusDiv.className = 'fetch-status loading';
    statusDiv.innerHTML = '<div class="loading-spinner"></div> Загружаем отзывы через сервер...';
    
    try {
        const response = await fetch('{{ route("yandex-maps.fetch-reviews") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ url: url })
        });
        
        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Ошибка при загрузке отзывов');
        }
        
        const reviews = await response.json();
        
        if (reviews.length === 0) {
            statusDiv.className = 'fetch-status error';
            statusDiv.innerHTML = '❌ Отзывы не найдены для данной организации';
            return;
        }
        
        statusDiv.className = 'fetch-status success';
        statusDiv.innerHTML = `✅ Загружено ${reviews.length} отзывов`;
        
        displayReviews(reviews);
        
        ratingBlock.style.display = 'flex';
        sortSelect.style.display = 'block';
        
        sortSelect.removeEventListener('change', window.sortHandler);
        window.sortHandler = function() {
            const sorted = sortReviews([...reviews], this.value);
            renderReviews(sorted);
        };
        sortSelect.addEventListener('change', window.sortHandler);
        
    } catch (error) {
        statusDiv.className = 'fetch-status error';
        statusDiv.innerHTML = `❌ Ошибка: ${error.message}`;
        console.error('Error loading reviews:', error);
    }
}

function displayReviews(reviews) {
    window.allReviews = reviews;
    
    const avgRating = reviews.reduce((sum, r) => sum + (parseFloat(r.rating) || 0), 0) / reviews.length;
    
    document.getElementById('avgRating').textContent = avgRating ? avgRating.toFixed(1) : '0.0';
    document.getElementById('totalReviews').textContent = `${reviews.length} ${getReviewWord(reviews.length)}`;
    
    const starsContainer = document.getElementById('starsContainer');
    starsContainer.innerHTML = '';
    if (avgRating) {
        for (let i = 1; i <= 5; i++) {
            starsContainer.innerHTML += i <= Math.round(avgRating) ? '★' : '☆';
        }
    }
    
    renderReviews(reviews);
}

function renderReviews(reviews) {
    const reviewsList = document.getElementById('reviewsList');
    
    if (reviews.length === 0) {
        reviewsList.innerHTML = '<div class="no-reviews">Нет отзывов</div>';
        return;
    }
    
    reviewsList.innerHTML = reviews.map(review => `
        <div class="review-card">
            <div class="review-header">
                <strong>${escapeHtml(review.author)}</strong>
                <span>${formatDisplayDate(review.date)}</span>
                ${review.rating ? `
                    <div class="stars">
                        ${generateStars(review.rating)}
                    </div>
                ` : ''}
            </div>
            <div class="review-text">${escapeHtml(review.text)}</div>
        </div>
    `).join('');
}

function sortReviews(reviews, sortBy) {
    return [...reviews].sort((a, b) => {
        switch(sortBy) {
            case 'newest':
                return new Date(b.date) - new Date(a.date);
            case 'oldest':
                return new Date(a.date) - new Date(b.date);
            case 'highest':
                return (parseFloat(b.rating) || 0) - (parseFloat(a.rating) || 0);
            case 'lowest':
                return (parseFloat(a.rating) || 0) - (parseFloat(b.rating) || 0);
            default:
                return 0;
        }
    });
}

function generateStars(rating) {
    const fullStars = Math.round(parseFloat(rating));
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        stars += i <= fullStars ? '★' : '☆';
    }
    return stars;
}

function formatDisplayDate(dateString) {
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('ru-RU');
    } catch {
        return dateString;
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function getReviewWord(count) {
    if (count % 10 === 1 && count % 100 !== 11) return 'отзыв';
    if ([2, 3, 4].includes(count % 10) && ![12, 13, 14].includes(count % 100)) return 'отзыва';
    return 'отзывов';
}
</script>
@endsection

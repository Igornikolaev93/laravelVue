<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Яндекс Отзывы</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f8f9fa;
            color: #212529;
            line-height: 1.5;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 15px 0;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 24px;
            color: #339AF0;
        }
        
        .main-content {
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .url-form-container form {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .url-input {
            flex: 1;
            min-width: 400px;
            height: 42px;
            padding: 8px 16px;
            border: 1px solid #DCE4EA;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .url-input:focus {
            outline: none;
            border-color: #339AF0;
            box-shadow: 0 0 0 3px rgba(51,154,240,0.1);
        }
        
        .submit-button {
            background: #339AF0;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 10px 24px;
            font-weight: 600;
            cursor: pointer;
            height: 42px;
            font-size: 14px;
            transition: background-color 0.2s;
        }
        
        .submit-button:hover {
            background: #2b7ac9;
        }
        
        .rating-block {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px;
            background: #f6f8fa;
            border-radius: 8px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .platform-name {
            font-weight: 600;
            color: #495057;
        }
        
        .rating-value {
            font-size: 24px;
            font-weight: 700;
            color: #212529;
        }
        
        .stars {
            color: #ffc107;
            font-size: 1.2em;
            letter-spacing: 2px;
        }
        
        .review-card {
            background: white;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            transition: box-shadow 0.2s;
        }
        
        .review-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .review-header {
            color: #6c757d;
            margin-bottom: 12px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .review-header strong {
            color: #212529;
            font-size: 16px;
        }
        
        .review-text {
            color: #212529;
            line-height: 1.6;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .no-reviews {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
            font-style: italic;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            gap: 5px;
        }
        
        .pagination .page-link {
            padding: 8px 14px;
            border: 1px solid #dee2e6;
            color: #339AF0;
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.2s;
        }
        
        .pagination .page-link:hover {
            background: #e9ecef;
        }
        
        .pagination .active .page-link {
            background: #339AF0;
            color: white;
            border-color: #339AF0;
        }
        
        .sort-select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin-bottom: 25px;
            min-width: 200px;
            font-size: 14px;
            cursor: pointer;
        }
        
        .sort-select:focus {
            outline: none;
            border-color: #339AF0;
        }
        
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #339AF0;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .fetch-status {
            margin: 20px 0;
            padding: 15px 20px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .fetch-status.loading {
            background: #e3f2fd;
            color: #0d47a1;
            border: 1px solid #bbdefb;
        }
        
        .fetch-status.error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        
        .fetch-status.success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        
        .change-url-link {
            color: #339AF0;
            text-decoration: none;
            font-size: 14px;
            margin-left: 15px;
        }
        
        .change-url-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>Яндекс Карты - Отзывы</h1>
        </div>
    </div>
    
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
        
        // Удаляем старый обработчик и добавляем новый
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
</body>
</html>

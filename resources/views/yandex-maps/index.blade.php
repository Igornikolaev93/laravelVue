('layouts.app')

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
        padding: 15px;
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
</style>

<div class="reviews-container">
    <div class="url-form-container">
        <form action="{{ route('yandex-maps.index') }}" method="POST">
            @csrf
            <input type="url" name="yandex_maps_url" class="url-input" 
                   placeholder="https://yandex.ru/maps/org/..." 
                   value="{{ $settings->yandex_maps_url ?? '' }}" required>
            <button type="submit" class="submit-button">Save URL</button>
        </form>
        
        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif
        
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
    </div>

    @if ($settings && $settings->yandex_maps_url)
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
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const url = '{{ $settings->yandex_maps_url ?? "" }}';
    
    if (url) {
        loadYandexReviews(url);
    }
});

async function loadYandexReviews(url) {
    const statusDiv = document.getElementById('fetchStatus');
    const ratingBlock = document.getElementById('ratingBlock');
    const sortSelect = document.getElementById('sortSelect');
    const reviewsList = document.getElementById('reviewsList');
    
    statusDiv.style.display = 'flex';
    statusDiv.className = 'fetch-status loading';
    statusDiv.innerHTML = '<div class="loading-spinner"></div> Извлекаем ID организации...';
    
    try {
        // Извлекаем ID организации из URL
        const orgId = extractOrganizationId(url);
        
        if (!orgId) {
            throw new Error('Не удалось извлечь ID организации из URL');
        }
        
        statusDiv.innerHTML = `<div class="loading-spinner"></div> ID организации: ${orgId}. Загружаем отзывы...`;
        
        // Загружаем отзывы
        const reviews = await fetchYandexReviews(orgId);
        
        if (reviews.length === 0) {
            statusDiv.className = 'fetch-status error';
            statusDiv.innerHTML = '❌ Отзывы не найдены для данной организации';
            return;
        }
        
        statusDiv.className = 'fetch-status success';
        statusDiv.innerHTML = `✅ Загружено ${reviews.length} отзывов`;
        
        // Отображаем отзывы
        displayReviews(reviews);
        
        // Показываем элементы управления
        ratingBlock.style.display = 'flex';
        sortSelect.style.display = 'block';
        
        // Добавляем обработчик сортировки
        sortSelect.addEventListener('change', function() {
            const sorted = sortReviews([...reviews], this.value);
            renderReviews(sorted);
        });
        
    } catch (error) {
        statusDiv.className = 'fetch-status error';
        statusDiv.innerHTML = `❌ Ошибка: ${error.message}`;
        console.error('Error loading reviews:', error);
    }
}

function extractOrganizationId(url) {
    // Паттерны для извлечения ID организации из URL Яндекса
    const patterns = [
        /org\/(?:[^\/]+\/)?(\d+)/,
        /organization\/(\d+)/,
        /maps\/(\d+)/,
        /biz\/(\d+)/,
        /\/(\d{5,})\/?/,
        /-\/org\/(?:[^\/]+\/)?(\d+)/,
        /organizations\/(\d+)/
    ];
    
    for (const pattern of patterns) {
        const match = url.match(pattern);
        if (match) {
            return match[1];
        }
    }
    
    return null;
}

async function fetchYandexReviews(orgId) {
    // Пробуем разные эндпоинты API Яндекса
    const endpoints = [
        `https://yandex.ru/maps/api/organizations/${orgId}/reviews?lang=ru&pageSize=100`,
        `https://yandex.ru/maps-api/v2/organizations/${orgId}/reviews?lang=ru_RU&pageSize=100`,
        `https://yandex.ru/maps/org/reviews/${orgId}/`
    ];
    
    // Прокси для обхода CORS
    const proxies = [
        'https://corsproxy.io/?',
        'https://api.allorigins.win/raw?url=',
        'https://cors-anywhere.herokuapp.com/'
    ];
    
    for (const endpoint of endpoints) {
        for (const proxy of proxies) {
            try {
                const url = proxy + encodeURIComponent(endpoint);
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json, text/html',
                        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                    }
                });
                
                if (response.ok) {
                    const contentType = response.headers.get('content-type');
                    
                    if (contentType && contentType.includes('application/json')) {
                        const data = await response.json();
                        const reviews = parseJsonReviews(data);
                        if (reviews.length > 0) {
                            return reviews;
                        }
                    } else {
                        const html = await response.text();
                        const reviews = parseHtmlReviews(html);
                        if (reviews.length > 0) {
                            return reviews;
                        }
                    }
                }
            } catch (e) {
                console.log(`Endpoint failed: ${endpoint} with proxy ${proxy}`, e);
                continue;
            }
        }
    }
    
    return [];
}

function parseJsonReviews(data) {
    const reviews = [];
    
    // Пробуем разные структуры данных
    const reviewItems = data.reviews || 
                       data.data?.reviews || 
                       data.items || 
                       data.results || 
                       [];
    
    if (Array.isArray(reviewItems)) {
        reviewItems.forEach(item => {
            const review = {
                author: item.author?.name || 
                       item.user?.name || 
                       item.authorName || 
                       'Аноним',
                date: item.date || 
                     item.createdAt || 
                     item.publishDate || 
                     new Date().toISOString().split('T')[0],
                rating: item.rating || 
                       item.stars || 
                       item.rate || 
                       0,
                text: item.text || 
                     item.comment || 
                     item.message || 
                     item.content || 
                     ''
            };
            
            // Добавляем только если есть текст отзыва
            if (review.text && review.text.trim()) {
                reviews.push(review);
            }
        });
    }
    
    return reviews;
}

function parseHtmlReviews(html) {
    const reviews = [];
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    
    // Ищем отзывы по разным селекторам
    const selectors = [
        '[class*="review"]',
        '[class*="Review"]',
        '[class*="feedback"]',
        '[class*="response"]',
        '.business-review',
        '.org-review'
    ];
    
    for (const selector of selectors) {
        const elements = doc.querySelectorAll(selector);
        
        elements.forEach(el => {
            // Пытаемся извлечь данные из элемента
            const author = extractText(el, ['author', 'user', 'name']) || 'Аноним';
            const date = extractText(el, ['date', 'time', 'published']) || formatDate(new Date());
            const rating = extractRating(el);
            const text = extractText(el, ['text', 'comment', 'message', 'content']) || '';
            
            if (text) {
                reviews.push({ author, date, rating, text });
            }
        });
        
        if (reviews.length > 0) {
            break;
        }
    }
    
    return reviews;
}

function extractText(element, possibleClasses) {
    for (const className of possibleClasses) {
        const found = element.querySelector(`[class*="${className}"]`);
        if (found && found.textContent) {
            return found.textContent.trim();
        }
    }
    return null;
}

function extractRating(element) {
    // Ищем рейтинг по разным признакам
    const ratingSelectors = ['[class*="rating"]', '[class*="stars"]', '[class*="rate"]'];
    
    for (const selector of ratingSelectors) {
        const ratingEl = element.querySelector(selector);
        if (ratingEl) {
            // Пробуем найти число в тексте
            const text = ratingEl.textContent || '';
            const match = text.match(/(\d+(?:\.\d+)?)/);
            if (match) {
                return parseFloat(match[1]);
            }
            
            // Считаем звезды
            const stars = (ratingEl.innerHTML.match(/★/g) || []).length;
            if (stars > 0) {
                return stars;
            }
        }
    }
    
    return 0;
}

function formatDate(date) {
    return date.toISOString().split('T')[0];
}

function displayReviews(reviews) {
    // Сохраняем все отзывы
    window.allReviews = reviews;
    
    // Вычисляем статистику
    const avgRating = reviews.reduce((sum, r) => sum + (r.rating || 0), 0) / reviews.length;
    
    // Отображаем рейтинг
    document.getElementById('avgRating').textContent = avgRating.toFixed(1);
    document.getElementById('totalReviews').textContent = `${reviews.length} ${getReviewWord(reviews.length)}`;
    
    // Отображаем звезды
    const starsContainer = document.getElementById('starsContainer');
    starsContainer.innerHTML = '';
    for (let i = 1; i <= 5; i++) {
        starsContainer.innerHTML += i <= Math.round(avgRating) ? '★' : '☆';
    }
    
    // Отображаем отзывы
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
                return (b.rating || 0) - (a.rating || 0);
            case 'lowest':
                return (a.rating || 0) - (b.rating || 0);
            default:
                return 0;
        }
    });
}

function generateStars(rating) {
    const fullStars = Math.round(rating);
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
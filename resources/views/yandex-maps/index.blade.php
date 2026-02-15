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
    .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .no-reviews { text-align: center; padding: 40px; color: #6c757d; font-style: italic; }
    .loading { text-align: center; padding: 40px; color: #6c757d; }
    .pagination { display: flex; justify-content: center; gap: 10px; margin-top: 20px; }
    .pagination button { padding: 5px 10px; border: 1px solid #ddd; background: white; cursor: pointer; border-radius: 4px; }
    .pagination button.active { background: #339AF0; color: white; border-color: #339AF0; }
    .pagination button:disabled { opacity: 0.5; cursor: not-allowed; }
    .review-rating { display: flex; align-items: center; gap: 5px; }
    .sort-select { padding: 5px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 20px; }
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
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif
        
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
    </div>

    @if ($settings && $settings->yandex_maps_url)
        <div id="reviews-container">
            <div class="loading">Loading reviews...</div>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const yandexUrl = "{{ $settings->yandex_maps_url ?? '' }}";
    if (yandexUrl) {
        fetchYandexReviews(yandexUrl);
    }
});

async function fetchYandexReviews(url) {
    const container = document.getElementById('reviews-container');
    
    try {
        // Извлекаем ID организации из URL
        const orgId = extractOrgId(url);
        
        if (!orgId) {
            showError('Could not extract organization ID from URL');
            return;
        }

        // Используем публичный CORS прокси для обхода ограничений
        const proxyUrl = 'https://api.allorigins.win/raw?url=';
        const yandexUrl = `https://yandex.ru/maps/org/${orgId}/reviews`;
        
        const response = await fetch(proxyUrl + encodeURIComponent(yandexUrl));
        const html = await response.text();
        
        // Парсим HTML
        parseYandexReviews(html, url);
        
    } catch (error) {
        console.error('Error fetching reviews:', error);
        showError('Failed to load reviews. Please try again.');
    }
}

function extractOrgId(url) {
    // Пробуем разные паттерны для извлечения ID
    const patterns = [
        /\/org\/(?:[^\/]+\/)?(\d+)/,
        /organization\/(\d+)/,
        /maps\/(\d+)/,
        /biz\/(\d+)/,
        /(\d+)\/reviews/
    ];
    
    for (const pattern of patterns) {
        const match = url.match(pattern);
        if (match) return match[1];
    }
    
    return null;
}

function parseYandexReviews(html, url) {
    const container = document.getElementById('reviews-container');
    
    // Создаем DOM парсер
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    
    let reviews = [];
    let rating = null;
    let totalReviews = 0;
    
    // Ищем рейтинг
    const ratingElement = doc.querySelector('[itemprop="ratingValue"], meta[itemprop="ratingValue"], .rating-value, [class*="rating"]');
    if (ratingElement) {
        if (ratingElement.tagName === 'META') {
            rating = ratingElement.getAttribute('content');
        } else {
            rating = ratingElement.textContent.trim();
        }
        rating = parseFloat(rating.replace(',', '.'));
    }
    
    // Ищем количество отзывов
    const countElement = doc.querySelector('[itemprop="reviewCount"], meta[itemprop="reviewCount"], [class*="reviews-count"]');
    if (countElement) {
        if (countElement.tagName === 'META') {
            totalReviews = countElement.getAttribute('content');
        } else {
            const match = countElement.textContent.match(/\d+/);
            if (match) totalReviews = match[0];
        }
    }
    
    // Ищем отзывы - пробуем разные селекторы
    const selectors = [
        '[class*="review"][class*="item"]',
        '[data-testid="review"]',
        '[class*="business-review"]',
        '[class*="review-card"]',
        '.reviews-list .review',
        '.business-reviews-view .review'
    ];
    
    let reviewElements = [];
    for (const selector of selectors) {
        const elements = doc.querySelectorAll(selector);
        if (elements.length > 0) {
            reviewElements = elements;
            break;
        }
    }
    
    // Парсим каждый отзыв
    reviewElements.forEach(element => {
        const review = {
            author: 'Аноним',
            date: new Date().toISOString().split('T')[0],
            rating: null,
            text: ''
        };
        
        // Ищем автора
        const authorEl = element.querySelector('b, strong, [class*="author"], [class*="user"]');
        if (authorEl) {
            review.author = authorEl.textContent.trim();
        }
        
        // Ищем дату
        const dateEl = element.querySelector('time, [class*="date"], [datetime]');
        if (dateEl) {
            const dateText = dateEl.getAttribute('datetime') || dateEl.textContent;
            const parsedDate = new Date(dateText);
            if (!isNaN(parsedDate)) {
                review.date = parsedDate.toISOString().split('T')[0];
            }
        }
        
        // Ищем рейтинг
        const ratingEl = element.querySelector('[class*="rating"], [class*="stars"]');
        if (ratingEl) {
            const match = ratingEl.textContent.match(/(\d+[,.]?\d*)/);
            if (match) {
                review.rating = parseFloat(match[1].replace(',', '.'));
            }
        }
        
        // Ищем текст
        const textEl = element.querySelector('p, [class*="text"], [class*="content"]');
        if (textEl) {
            review.text = textEl.textContent.trim();
        }
        
        // Добавляем только если есть текст
        if (review.text && review.text.length > 10) {
            reviews.push(review);
        }
    });
    
    // Если не нашли через селекторы, пробуем найти JSON данные
    if (reviews.length === 0) {
        const scripts = doc.querySelectorAll('script[type="application/ld+json"]');
        scripts.forEach(script => {
            try {
                const data = JSON.parse(script.textContent);
                if (data.review || data.reviews) {
                    const jsonReviews = data.review || data.reviews;
                    if (Array.isArray(jsonReviews)) {
                        jsonReviews.forEach(item => {
                            if (item.reviewBody || item.description) {
                                reviews.push({
                                    author: item.author?.name || 'Аноним',
                                    date: item.datePublished || item.dateCreated || new Date().toISOString().split('T')[0],
                                    rating: item.reviewRating?.ratingValue || null,
                                    text: item.reviewBody || item.description || ''
                                });
                            }
                        });
                    }
                }
            } catch (e) {
                // Ignore JSON parse errors
            }
        });
    }
    
    // Отображаем результаты
    if (reviews.length > 0 || rating) {
        displayReviews(reviews, rating, totalReviews);
    } else {
        // Пробуем использовать API как запасной вариант
        fetchReviewsFromApi(extractOrgId(url));
    }
}

async function fetchReviewsFromApi(orgId) {
    if (!orgId) return;
    
    try {
        const proxyUrl = 'https://api.allorigins.win/raw?url=';
        const apiUrl = `https://yandex.ru/maps/api/organizations/${orgId}/reviews?lang=ru_RU&page=1&pageSize=20`;
        const response = await fetch(proxyUrl + encodeURIComponent(apiUrl));
        const data = await response.json();
        
        const reviews = [];
        if (data.reviews && Array.isArray(data.reviews)) {
            data.reviews.forEach(item => {
                reviews.push({
                    author: item.author?.name || 'Аноним',
                    date: item.date || new Date().toISOString().split('T')[0],
                    rating: item.rating || null,
                    text: item.text || item.pros || item.cons || ''
                });
            });
        }
        
        displayReviews(reviews, data.rating, data.total);
    } catch (error) {
        console.error('API fallback error:', error);
        showNoReviews();
    }
}

function displayReviews(reviews, rating, totalReviews) {
    const container = document.getElementById('reviews-container');
    
    let html = '';
    
    // Блок рейтинга
    if (rating || totalReviews) {
        html += `
            <div class="rating-block">
                <span class="platform-name">Яндекс Карты</span>
                ${rating ? `
                    <span class="rating-value">${parseFloat(rating).toFixed(1)}</span>
                    <div class="stars">${generateStars(rating)}</div>
                ` : ''}
                <span>${totalReviews || reviews.length} ${pluralize(reviews.length, ['отзыв', 'отзыва', 'отзывов'])}</span>
            </div>
        `;
    }
    
    // Селектор сортировки
    html += `
        <select class="sort-select" onchange="sortReviews(this.value)">
            <option value="newest">Сначала новые</option>
            <option value="oldest">Сначала старые</option>
            <option value="highest">Сначала высокий рейтинг</option>
            <option value="lowest">Сначала низкий рейтинг</option>
        </select>
    `;
    
    // Отзывы
    if (reviews.length > 0) {
        html += '<div id="reviews-list">';
        reviews.forEach(review => {
            html += generateReviewCard(review);
        });
        html += '</div>';
        
        // Пагинация
        html += generatePagination(reviews.length);
    } else {
        html += '<div class="no-reviews">Нет отзывов для данной организации</div>';
    }
    
    container.innerHTML = html;
    
    // Сохраняем все отзывы для сортировки и пагинации
    window.allReviews = reviews;
}

function generateReviewCard(review) {
    return `
        <div class="review-card">
            <div class="review-header">
                <strong>${escapeHtml(review.author)}</strong>
                <span>${review.date}</span>
                ${review.rating ? `
                    <div class="review-rating">
                        <div class="stars">${generateStars(review.rating)}</div>
                        <span>${review.rating.toFixed(1)}</span>
                    </div>
                ` : ''}
            </div>
            <div>${escapeHtml(review.text)}</div>
        </div>
    `;
}

function generateStars(rating) {
    const value = Math.round(parseFloat(rating));
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        stars += i <= value ? '★' : '☆';
    }
    return stars;
}

function pluralize(count, words) {
    const cases = [2, 0, 1, 1, 1, 2];
    return words[(count % 100 > 4 && count % 100 < 20) ? 2 : cases[Math.min(count % 10, 5)]];
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showError(message) {
    const container = document.getElementById('reviews-container');
    container.innerHTML = `<div class="alert alert-danger">${message}</div>`;
}

function showNoReviews() {
    const container = document.getElementById('reviews-container');
    container.innerHTML = '<div class="no-reviews">No reviews found for this URL. Please check the URL and try again.</div>';
}

// Функции для сортировки и пагинации
function sortReviews(sortBy) {
    if (!window.allReviews) return;
    
    const sorted = [...window.allReviews];
    
    switch(sortBy) {
        case 'newest':
            sorted.sort((a, b) => new Date(b.date) - new Date(a.date));
            break;
        case 'oldest':
            sorted.sort((a, b) => new Date(a.date) - new Date(b.date));
            break;
        case 'highest':
            sorted.sort((a, b) => (b.rating || 0) - (a.rating || 0));
            break;
        case 'lowest':
            sorted.sort((a, b) => (a.rating || 0) - (b.rating || 0));
            break;
    }
    
    displayReviews(sorted, null, sorted.length);
}

function generatePagination(total) {
    return `
        <div class="pagination">
            <button onclick="changePage(-1)" id="prevBtn" disabled>←</button>
            <span>Страница <span id="currentPage">1</span> из ${Math.ceil(total / 5)}</span>
            <button onclick="changePage(1)" id="nextBtn">→</button>
        </div>
    `;
}

function changePage(direction) {
    // Здесь можно добавить логику пагинации
}
</script>
@endsection

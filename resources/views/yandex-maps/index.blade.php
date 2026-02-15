@extends('layouts.app')

@section('content')
<style>
    .url-info {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 30px;
        border: 1px solid #e9ecef;
    }
    
    .url-info p {
        margin-bottom: 15px;
        word-break: break-all;
        color: #495057;
    }
    
    .btn {
        display: inline-block;
        padding: 10px 20px;
        background: #6c757d;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-size: 14px;
        transition: background 0.2s;
    }
    
    .btn:hover {
        background: #5a6268;
    }
    
    .reviews-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .review-card {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 20px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .review-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .review-author {
        font-weight: 600;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .review-author i {
        color: #6c757d;
    }
    
    .review-date {
        color: #6c757d;
        font-size: 13px;
    }
    
    .review-rating {
        color: #ffc107;
        margin-bottom: 12px;
        font-size: 16px;
        letter-spacing: 2px;
    }
    
    .review-text {
        color: #2c3e50;
        line-height: 1.6;
        font-size: 14px;
        max-height: 150px;
        overflow-y: auto;
        padding-right: 10px;
    }
    
    .review-text::-webkit-scrollbar {
        width: 4px;
    }
    
    .review-text::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    .review-text::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    
    .loading {
        text-align: center;
        padding: 60px;
        color: #6c757d;
    }
    
    .loading i {
        margin-right: 8px;
    }
    
    .error {
        background: #f8d7da;
        color: #721c24;
        padding: 15px;
        border-radius: 8px;
        margin: 20px 0;
        border: 1px solid #f5c6cb;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px;
        color: #6c757d;
    }
    
    .empty-state i {
        font-size: 48px;
        margin-bottom: 20px;
        color: #dee2e6;
    }
    
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 12px;
        text-align: center;
    }
    
    .stat-value {
        font-size: 36px;
        font-weight: 700;
        margin-bottom: 5px;
    }
    
    .stat-label {
        font-size: 14px;
        opacity: 0.9;
    }
    
    .sort-select {
        width: 200px;
        padding: 10px;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        margin-bottom: 20px;
        background: white;
        cursor: pointer;
    }
    
    .sort-select:focus {
        outline: none;
        border-color: #667eea;
    }
    
    .pagination {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 30px;
    }
    
    .pagination button {
        padding: 8px 16px;
        border: 1px solid #dee2e6;
        background: white;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .pagination button:hover {
        background: #e9ecef;
    }
    
    .pagination button.active {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }
</style>

@if($settings && $settings->yandex_maps_url)
    <div class="url-info">
        <p><i class="fas fa-link"></i> <strong>URL:</strong> {{ $settings->yandex_maps_url }}</p>
        <a href="{{ route('yandex-maps.settings') }}" class="btn">
            <i class="fas fa-edit"></i> Изменить URL
        </a>
    </div>

    <div id="status" class="loading">
        <i class="fas fa-spinner fa-spin"></i> Загрузка отзывов...
    </div>
    
    <div id="statsContainer" class="stats-container" style="display: none;"></div>
    
    <select id="sortSelect" class="sort-select" style="display: none;">
        <option value="newest">Сначала новые</option>
        <option value="oldest">Сначала старые</option>
        <option value="highest">Высокий рейтинг</option>
        <option value="lowest">Низкий рейтинг</option>
    </select>
    
    <div id="reviewsList" class="reviews-grid"></div>
    
    <div id="pagination" class="pagination" style="display: none;"></div>
@else
    <div class="empty-state">
        <i class="fas fa-map-marked-alt"></i>
        <h3>URL не настроен</h3>
        <p>Перейдите в <a href="{{ route('yandex-maps.settings') }}">настройки</a> чтобы подключить Яндекс.Карты.</p>
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($settings && $settings->yandex_maps_url)
        setTimeout(() => {
            loadReviews('{{ $settings->yandex_maps_url }}');
        }, 100);
    @endif
});

let allReviews = [];
let currentPage = 1;
const reviewsPerPage = 6;

async function loadReviews(url) {
    const statusEl = document.getElementById('status');
    const statsContainer = document.getElementById('statsContainer');
    const sortSelect = document.getElementById('sortSelect');
    
    try {
        statusEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Загрузка отзывов...';
        
        const response = await fetch('{{ route("yandex-maps.fetch-reviews") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ url })
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.error || 'Ошибка загрузки отзывов');
        }
        
        statusEl.style.display = 'none';
        
        if (data.reviews && data.reviews.length > 0) {
            allReviews = data.reviews;
            
            // Показываем статистику
            statsContainer.style.display = 'grid';
            statsContainer.innerHTML = `
                <div class="stat-card">
                    <div class="stat-value">${data.stats.total_reviews}</div>
                    <div class="stat-label">всего отзывов</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${data.stats.average_rating}</div>
                    <div class="stat-label">средний рейтинг</div>
                </div>
            `;
            
            currentPage = 1;
            renderReviewsPage(currentPage);
            
            sortSelect.style.display = 'block';
            sortSelect.onchange = function() {
                allReviews = sortReviews([...allReviews], this.value);
                currentPage = 1;
                renderReviewsPage(currentPage);
            };
            
        } else {
            document.getElementById('reviewsList').innerHTML = 
                '<div class="empty-state">Нет отзывов для отображения</div>';
        }
    } catch (error) {
        console.error('Error:', error);
        
        statusEl.innerHTML = `
            <div class="error">
                <i class="fas fa-exclamation-circle"></i> 
                ${error.message}
            </div>
        `;
    }
}

function renderReviewsPage(page) {
    const reviewsList = document.getElementById('reviewsList');
    const paginationDiv = document.getElementById('pagination');
    
    if (!allReviews || allReviews.length === 0) {
        reviewsList.innerHTML = '<div class="empty-state">Нет отзывов</div>';
        paginationDiv.style.display = 'none';
        return;
    }
    
    const totalPages = Math.ceil(allReviews.length / reviewsPerPage);
    const start = (page - 1) * reviewsPerPage;
    const end = start + reviewsPerPage;
    const pageReviews = allReviews.slice(start, end);
    
    reviewsList.innerHTML = pageReviews.map(review => {
        const date = new Date(review.date);
        const formattedDate = !isNaN(date) 
            ? date.toLocaleDateString('ru-RU', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
              })
            : review.date;
        
        const rating = Math.min(5, Math.max(0, Math.round(review.rating)));
        const stars = '★'.repeat(rating) + '☆'.repeat(5 - rating);
        
        return `
            <div class="review-card">
                <div class="review-header">
                    <span class="review-author">
                        <i class="fas fa-user-circle"></i> ${escapeHtml(review.author)}
                    </span>
                    <span class="review-date">
                        <i class="far fa-calendar-alt"></i> ${formattedDate}
                    </span>
                </div>
                <div class="review-rating">${stars}</div>
                <div class="review-text">${escapeHtml(review.text)}</div>
            </div>
        `;
    }).join('');
    
    if (totalPages > 1) {
        paginationDiv.style.display = 'flex';
        
        let paginationHtml = '';
        
        paginationHtml += `<button onclick="changePage(${page - 1})" ${page === 1 ? 'disabled' : ''}>\n            <i class="fas fa-chevron-left"></i>\n        </button>`;
        
        for (let i = 1; i <= totalPages; i++) {
            paginationHtml += `<button onclick="changePage(${i})" ${i === page ? 'class="active"' : ''}>${i}</button>`;
        }
        
        paginationHtml += `<button onclick="changePage(${page + 1})" ${page === totalPages ? 'disabled' : ''}>\n            <i class="fas fa-chevron-right"></i>\n        </button>`;
        
        paginationDiv.innerHTML = paginationHtml;
    } else {
        paginationDiv.style.display = 'none';
    }
}

function changePage(page) {
    const totalPages = Math.ceil(allReviews.length / reviewsPerPage);
    
    if (page < 1 || page > totalPages) {
        return;
    }
    
    currentPage = page;
    renderReviewsPage(currentPage);
    
    document.getElementById('reviewsList').scrollIntoView({ behavior: 'smooth' });
}

function sortReviews(reviews, sortBy) {
    const sorted = [...reviews];
    
    switch(sortBy) {
        case 'newest':
            return sorted.sort((a, b) => new Date(b.date) - new Date(a.date));
        case 'oldest':
            return sorted.sort((a, b) => new Date(a.date) - new Date(b.date));
        case 'highest':
            return sorted.sort((a, b) => (b.rating || 0) - (a.rating || 0));
        case 'lowest':
            return sorted.sort((a, b) => (a.rating || 0) - (b.rating || 0));
        default:
            return sorted;
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endsection
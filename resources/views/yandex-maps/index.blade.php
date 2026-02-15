@extends('layouts.app')

@section('content')
<style>
    .reviews-main-container { 
        padding: 10px 20px; 
    }
    .reviews-header {
        display: flex; 
        justify-content: flex-end; 
        margin-bottom: 20px; 
    }
    .reviews-header-icon { 
        font-size: 20px; 
        color: #909AB4; 
        cursor: pointer;
    }
    .reviews-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
        align-items: start;
    }
    .review-card-new {
        background-color: white;
        border: 1px solid #E5E9F2;
        border-radius: 12px;
        padding: 20px;
        animation: fadeIn 0.5s ease-out;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.04);
        min-height: 150px;
        transition: transform 0.2s;
    }
    .review-card-new:hover {
        transform: translateY(-2px);
        box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.08);
    }
    .review-card-new.placeholder {
        background-color: #F7F8FA;
        border: 1px dashed #DCE4EA;
        box-shadow: none;
        min-height: 100px;
    }
    .review-card-new.placeholder:hover {
        transform: none;
    }

    .review-card-new-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: #6C757D;
        font-size: 13px;
        margin-bottom: 15px;
        flex-wrap: wrap;
        gap: 10px;
    }
    .review-card-new-header .author {
        display: flex;
        align-items: center;
        gap: 5px;
        font-weight: 600;
        color: #363740;
    }
    .review-card-new-header .branch {
        display: flex;
        align-items: center;
        gap: 5px;
        font-weight: 500;
        background: #F7F8FA;
        padding: 4px 8px;
        border-radius: 6px;
    }
    .review-card-new-header .branch i {
        color: #EF4444;
    }

    .review-card-new-rating {
        margin-bottom: 10px;
    }
    .stars {
        color: #FFC107;
        letter-spacing: 2px;
        font-size: 14px;
    }
    .rating-value {
        color: #6C757D;
        font-size: 12px;
        margin-left: 5px;
    }

    .review-card-new-text {
        line-height: 1.7;
        color: #363740;
        font-size: 14px;
        font-weight: 400;
        max-height: 150px;
        overflow-y: auto;
        padding-right: 5px;
    }
    .review-card-new-text::-webkit-scrollbar {
        width: 4px;
    }
    .review-card-new-text::-webkit-scrollbar-track {
        background: #F7F8FA;
    }
    .review-card-new-text::-webkit-scrollbar-thumb {
        background: #DCE4EA;
        border-radius: 4px;
    }

    .stats-container {
        display: flex;
        align-items: center;
        gap: 30px;
        margin-bottom: 30px;
        padding: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        color: white;
    }
    .stat-item {
        text-align: center;
    }
    .stat-value {
        font-size: 32px;
        font-weight: 700;
        line-height: 1.2;
    }
    .stat-label {
        font-size: 13px;
        opacity: 0.9;
        margin-top: 5px;
    }

    .no-reviews, .fetch-status { 
        text-align: center; 
        padding: 60px 20px; 
        color: #6C757D; 
        width: 100%;
        background: #F7F8FA;
        border-radius: 12px;
        font-size: 15px;
    }
    .fetch-status.loading {
        background: #E3F2FD;
        color: #1976D2;
        border: 1px solid #BBDEFB;
    }
    .fetch-status.error {
        background: #FFEBEE;
        color: #C62828;
        border: 1px solid #FFCDD2;
    }
    .fetch-status.success {
        background: #E8F5E9;
        color: #2E7D32;
        border: 1px solid #C8E6C9;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .sort-select {
        width: 200px;
        padding: 8px 12px;
        border: 1px solid #E5E9F2;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 13px;
        color: #363740;
        background: white;
        cursor: pointer;
    }
    .sort-select:focus {
        outline: none;
        border-color: #667eea;
    }

    .reviews-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .reviews-title {
        font-size: 18px;
        font-weight: 600;
        color: #363740;
    }
</style>

<div class="reviews-main-container">
    @if ($settings && $settings->yandex_maps_url)
        <div class="reviews-header">
            <span class="reviews-title">Отзывы с Яндекс.Карт</span>
            <select id="sortSelect" class="sort-select" style="display: none;">
                <option value="newest">Сначала новые</option>
                <option value="oldest">Сначала старые</option>
                <option value="highest">По рейтингу ↓</option>
                <option value="lowest">По рейтингу ↑</option>
            </select>
            <span class="reviews-header-icon"><i class="far fa-clone"></i></span>
        </div>
        
        <div id="statsContainer" class="stats-container" style="display: none;">
            <div class="stat-item">
                <div class="stat-value" id="totalReviews">0</div>
                <div class="stat-label">всего отзывов</div>
            </div>
            <div class="stat-item">
                <div class="stat-value" id="avgRating">0.0</div>
                <div class="stat-label">средний рейтинг</div>
            </div>
        </div>
        
        <div id="fetchStatus" class="fetch-status loading" style="display: none;"></div>
        <div id="reviewsList" class="reviews-grid"></div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const url = @json($settings->yandex_maps_url);
                loadYandexReviews(url);
            });

            let allReviews = [];

            async function loadYandexReviews(url) {
                const statusEl = document.getElementById('fetchStatus');
                const listEl = document.getElementById('reviewsList');
                const statsContainer = document.getElementById('statsContainer');
                const sortSelect = document.getElementById('sortSelect');
                
                statusEl.style.display = 'block';
                statusEl.className = 'fetch-status loading';
                statusEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Загрузка отзывов...';
                listEl.innerHTML = '';
                statsContainer.style.display = 'none';
                sortSelect.style.display = 'none';
                
                try {
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

                    if (!response.ok) {
                        throw new Error(data.error || 'Ошибка загрузки');
                    }
                    
                    statusEl.style.display = 'none';
                    
                    if (data.reviews && data.reviews.length) {
                        allReviews = data.reviews;
                        
                        // Показываем статистику
                        statsContainer.style.display = 'flex';
                        document.getElementById('totalReviews').textContent = data.stats.total_reviews;
                        document.getElementById('avgRating').textContent = data.stats.average_rating;
                        
                        // Показываем сортировку
                        sortSelect.style.display = 'block';
                        
                        // Отображаем отзывы
                        renderReviews(allReviews);
                        
                        // Добавляем обработчик сортировки
                        sortSelect.onchange = function() {
                            const sorted = sortReviews([...allReviews], this.value);
                            renderReviews(sorted);
                        };
                    } else {
                        listEl.innerHTML = '<div class="no-reviews">Нет отзывов для отображения.</div>';
                    }
                } catch (e) {
                    statusEl.className = 'fetch-status error';
                    statusEl.innerHTML = `❌ Ошибка: ${e.message}`;
                    console.error('Error:', e);
                }
            }

            function renderReviews(reviews) {
                const listEl = document.getElementById('reviewsList');
                
                if (reviews.length === 0) {
                    listEl.innerHTML = '<div class="no-reviews">Нет отзывов</div>';
                    return;
                }
                
                listEl.innerHTML = reviews.map(review => {
                    const reviewDate = review.date ? new Date(review.date.replace(' ', 'T')) : new Date();
                    const formattedDate = !isNaN(reviewDate)
                        ? new Intl.DateTimeFormat('ru-RU', { 
                            day: 'numeric', 
                            month: 'long', 
                            year: 'numeric',
                            hour: '2-digit', 
                            minute: '2-digit' 
                        }).format(reviewDate)
                        : 'Дата не указана';

                    const rating = parseFloat(review.rating) || 0;
                    const stars = '★'.repeat(Math.round(rating)) + '☆'.repeat(5 - Math.round(rating));

                    return `<div class="review-card-new">
                        <div class="review-card-new-header">
                            <span class="author">
                                <i class="fas fa-user-circle"></i> ${escapeHtml(review.author)}
                            </span>
                            <span class="branch">
                                <i class="fas fa-map-marker-alt"></i> филиал 1
                            </span>
                        </div>
                        <div class="review-card-new-rating">
                            <span class="stars">${stars}</span>
                            <span class="rating-value">${rating > 0 ? rating.toFixed(1) : ''}</span>
                        </div>
                        <div class="review-card-new-text">${escapeHtml(review.text || 'Нет текста отзыва')}</div>
                        <div style="margin-top: 10px; font-size: 11px; color: #999; text-align: right;">
                            ${formattedDate}
                        </div>
                    </div>`;
                }).join('');
                
                // Добавляем placeholder карточки для визуальной целостности
                const placeholderCount = Math.max(0, 3 - (reviews.length % 3));
                for (let i = 0; i < placeholderCount; i++) {
                    listEl.innerHTML += '<div class="review-card-new placeholder"></div>';
                }
            }

            function sortReviews(reviews, sortBy) {
                return [...reviews].sort((a, b) => {
                    switch(sortBy) {
                        case 'newest':
                            return new Date(b.date || 0) - new Date(a.date || 0);
                        case 'oldest':
                            return new Date(a.date || 0) - new Date(b.date || 0);
                        case 'highest':
                            return (parseFloat(b.rating) || 0) - (parseFloat(a.rating) || 0);
                        case 'lowest':
                            return (parseFloat(a.rating) || 0) - (parseFloat(b.rating) || 0);
                        default:
                            return 0;
                    }
                });
            }

            function escapeHtml(unsafe) {
                if (!unsafe) return '';
                return unsafe
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }
        </script>
    @else
        <div class="no-reviews">
            <p>Чтобы увидеть отзывы, перейдите в раздел "Настройка" и подключите Яндекс.Карты.</p>
        </div>
    @endif
</div>
@endsection
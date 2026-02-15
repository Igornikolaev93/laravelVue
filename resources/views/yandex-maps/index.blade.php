('layouts.app')

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
        loadReviews('{{ $settings->yandex_maps_url }}');
    @endif
});

let allReviews = [];

async function loadReviews(url) {
    try {
        const response = await fetch('{{ route("yandex-maps.fetch-reviews") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ url })
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'Ошибка загрузки');
        }

        document.getElementById('status').style.display = 'none';
        
        if (data.reviews && data.reviews.length > 0) {
            allReviews = data.reviews;
            displayStats(data.stats);
            renderReviews(allReviews);
            
            document.getElementById('sortSelect').style.display = 'block';
            document.getElementById('sortSelect').addEventListener('change', function() {
                const sorted = sortReviews([...allReviews], this.value);
                renderReviews(sorted);
            });
        } else {
            document.getElementById('reviewsList').innerHTML = 
                '<div class="empty-state">Нет отзывов для отображения</div>';
        }
    } catch (error) {
        document.getElementById('status').innerHTML = 
            `<div class="error"><i class="fas fa-exclamation-circle"></i> ${error.message}</div>`;
    }
}

function displayStats(stats) {
    const container = document.getElementById('statsContainer');
    container.style.display = 'grid';
    container.innerHTML = `
        <div class="stat-card">
            <div class="stat-value">${stats.total_reviews}</div>
            <div class="stat-label">всего отзывов</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">${stats.average_rating}</div>
            <div class="stat-label">средний рейтинг</div>
        </div>
    `;
}

function renderReviews(reviews) {
    const html = reviews.map(review => {
        const date = new Date(review.date);
        const formattedDate = date.toLocaleDateString('ru-RU', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
        
        const stars = '★'.repeat(Math.round(review.rating)) + '☆'.repeat(5 - Math.round(review.rating));
        
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
    
    document.getElementById('reviewsList').innerHTML = html;
}

function sortReviews(reviews, sortBy) {
    return reviews.sort((a, b) => {
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

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endsection

@extends('layouts.app')

@section('content')

<style>
    .reviews-main-container {
        padding: 10px 30px;
    }

    .reviews-header {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 20px;
    }

    .reviews-header-icon {
        font-size: 20px;
        color: #909AB4;
    }

    .review-card-new {
        background-color: #F7F8FA;
        border: 1px solid #E5E5E5;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        max-width: 750px;
    }

    .review-card-new-header {
        display: flex;
        align-items: center;
        color: #6C757D;
        font-size: 14px;
        margin-bottom: 15px;
        gap: 15px;
    }

    .review-card-new-header .branch {
        font-weight: 500;
    }

    .review-card-new-header .icon {
        color: #EF4444; 
    }

    .review-card-new-author {
        margin-bottom: 15px;
    }

    .review-card-new-author strong {
        font-size: 15px;
        font-weight: 600;
    }

    .review-card-new-text {
        line-height: 1.6;
        color: #363740;
        font-size: 14px;
    }

    .no-reviews {
        text-align: center;
        padding: 40px;
        color: #6C757D;
    }

    .fetch-status {
        padding: 15px;
        border-radius: 6px;
        margin-top: 20px;
    }
    .fetch-status.loading {
        background: #e3f2fd;
        color: #0d47a1;
    }
    .fetch-status.error {
        background: #ffebee;
        color: #c62828;
    }
</style>

<div class="reviews-main-container">
    <div class="reviews-header">
        <span class="reviews-header-icon"><i class="fas fa-external-link-alt"></i></span>
    </div>

    <div id="fetchStatus" class="fetch-status" style="display: none;"></div>

    <div id="reviewsList"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const url = '{{ $settings->yandex_maps_url }}';
    
    if (url) {
        loadYandexReviews(url);
    } else {
        document.getElementById('reviewsList').innerHTML = '<div class="no-reviews">URL Яндекс.Карт не настроен.</div>';
    }
});

async function loadYandexReviews(url) {
    const statusDiv = document.getElementById('fetchStatus');
    const reviewsList = document.getElementById('reviewsList');
    
    statusDiv.style.display = 'block';
    statusDiv.className = 'fetch-status loading';
    statusDiv.innerHTML = 'Загрузка отзывов...';
    reviewsList.innerHTML = '';

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
        statusDiv.style.display = 'none';
        
        renderReviews(reviews);
        
    } catch (error) {
        statusDiv.className = 'fetch-status error';
        statusDiv.innerHTML = `❌ Ошибка: ${error.message}`;
        console.error('Error loading reviews:', error);
    }
}

function renderReviews(reviews) {
    const reviewsList = document.getElementById('reviewsList');
    
    if (reviews.length === 0) {
        reviewsList.innerHTML = '<div class="no-reviews">Нет отзывов для отображения.</div>';
        return;
    }
    
    reviewsList.innerHTML = reviews.map(review => {
        const formattedDate = formatDisplayDate(review.date);
        return `
            <div class="review-card-new">
                <div class="review-card-new-header">
                    <span>${formattedDate}</span>
                    <span class="branch">филиал 1</span>
                    <span class="icon"><i class="fas fa-map-marker-alt"></i></span>
                </div>
                <div class="review-card-new-author">
                    <strong>${escapeHtml(review.author)}</strong>
                </div>
                <div class="review-card-new-text">
                    ${escapeHtml(review.text)}
                </div>
            </div>
        `;
    }).join('');
}

function formatDisplayDate(dateString) {
    try {
        const date = new Date(dateString);
        const options = { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' };
        return new Intl.DateTimeFormat('ru-RU', options).format(date);
    } catch {
        return dateString; 
    }
}

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

</script>
@endsection

@extends('layouts.app')

@section('content')
<style>
    /* --- STYLES FOR REVIEWS DISPLAY --- */
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
        animation: fadeIn 0.5s ease-out;
    }
    .review-card-new-header {
        display: flex;
        align-items: center;
        color: #6C757D;
        font-size: 14px;
        margin-bottom: 15px;
        gap: 15px;
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
    .no-reviews, .fetch-status {
        text-align: center;
        padding: 40px;
        color: #6C757D;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="reviews-main-container">
    @if ($settings && $settings->yandex_maps_url)
        <div class="reviews-header">
            <span class="reviews-header-icon"><i class="fas fa-external-link-alt"></i></span>
        </div>
        <div id="fetchStatus" class="fetch-status" style="display: none;"></div>
        <div id="reviewsList"></div>
    @else
        <div class="no-reviews">
            <p>Чтобы увидеть отзывы, перейдите в раздел "Настройка" и подключите Яндекс.Карты.</p>
        </div>
    @endif
</div>

@if ($settings && $settings->yandex_maps_url)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        loadYandexReviews('{{ $settings->yandex_maps_url }}');
    });

    async function loadYandexReviews(url) {
        const statusDiv = document.getElementById('fetchStatus');
        const reviewsList = document.getElementById('reviewsList');
        
        statusDiv.style.display = 'block';
        statusDiv.textContent = 'Загрузка отзывов...';

        try {
            const response = await fetch('{{ route("yandex-maps.fetch-reviews") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ url })
            });
            
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || 'Ошибка при загрузке отзывов');
            }
            
            const reviews = await response.json();
            statusDiv.style.display = 'none';
            renderReviews(reviews);
            
        } catch (error) {
            statusDiv.textContent = `❌ Ошибка: ${error.message}`;
            console.error('Error loading reviews:', error);
        }
    }

    function renderReviews(reviews) {
        const reviewsList = document.getElementById('reviewsList');
        if (!reviews || reviews.length === 0) {
            reviewsList.innerHTML = '<div class="no-reviews">Нет отзывов для отображения.</div>';
            return;
        }
        
        reviewsList.innerHTML = reviews.map(review => {
            const date = review.date ? new Date(review.date) : new Date();
            const formattedDate = !isNaN(date) ? 
                new Intl.DateTimeFormat('ru-RU', { 
                    year: 'numeric', 
                    month: '2-digit', 
                    day: '2-digit', 
                    hour: '2-digit', 
                    minute: '2-digit' 
                }).format(date) : 'Дата не указана';
            
            return `
                <div class="review-card-new">
                    <div class="review-card-new-header">
                        <span>${formattedDate}</span>
                        <span class="branch">филиал 1</span>
                        <span class="icon"><i class="fas fa-map-marker-alt"></i></span>
                    </div>
                    <div class="review-card-new-author">
                        <strong>${escapeHtml(review.author || 'Аноним')}</strong>
                    </div>
                    <div class="review-card-new-text">
                        ${escapeHtml(review.text || 'Нет текста отзыва')}
                    </div>
                </div>
            `;
        }).join('');
    }

    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
</script>
@endif

@endsection

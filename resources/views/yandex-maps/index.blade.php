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
    }
    .review-card-new.placeholder {
        background-color: #F7F8FA;
        border: 1px dashed #DCE4EA;
        box-shadow: none;
    }

    .review-card-new-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: #6C757D;
        font-size: 13px;
        margin-bottom: 15px;
        flex-wrap: wrap;
    }
    .review-card-new-header .branch {
        display: flex;
        align-items: center;
        gap: 5px;
        font-weight: 500;
    }
    .review-card-new-header .branch i {
        color: #EF4444;
    }

    .review-card-new-text {
        line-height: 1.7;
        color: #363740;
        font-size: 14px;
        font-weight: 400;
    }
    .no-reviews, .fetch-status { 
        text-align: center; 
        padding: 40px; 
        color: #6C757D; 
        width: 100%;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="reviews-main-container">
    @if ($settings && $settings->yandex_maps_url)
        <div class="reviews-header">
            <span class="reviews-header-icon"><i class="far fa-clone"></i></span>
        </div>
        
        <div id="fetchStatus" class="fetch-status" style="display: none;"></div>
        <div id="reviewsList" class="reviews-grid"></div>

        <script>
            document.addEventListener('DOMContentLoaded', ()=>loadYandexReviews('{{ $settings->yandex_maps_url }}'));

            async function loadYandexReviews(url) {
                const statusEl = document.getElementById('fetchStatus');
                const listEl = document.getElementById('reviewsList');
                statusEl.style.display = 'block';
                statusEl.textContent = 'Загрузка отзывов...';
                listEl.innerHTML = '';
                
                try {
                    const response = await fetch('{{ route("yandex-maps.fetch-reviews") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ url })
                    });
                    const data = await response.json();

                    if (!response.ok) throw new Error(data.error || 'Ошибка загрузки');
                    
                    statusEl.style.display = 'none';
                    if (data.reviews && data.reviews.length) {
                        renderReviews(data.reviews);
                    } else {
                        listEl.innerHTML = '<div class="no-reviews">Нет отзывов для отображения.</div>';
                    }
                } catch (e) {
                    statusEl.textContent = `❌ Ошибка: ${e.message}`;
                    console.error(e);
                }
            }

            function renderReviews(reviews) {
                const listEl = document.getElementById('reviewsList');
                listEl.innerHTML = reviews.map(review => {
                    const reviewDate = review.date ? new Date(review.date) : new Date();
                    const formattedDate = !isNaN(reviewDate)
                        ? new Intl.DateTimeFormat('ru-RU', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' }).format(reviewDate)
                        : 'Дата не указана';

                    return `<div class="review-card-new">
                        <div class="review-card-new-header">
                            <span>${formattedDate}</span>
                            <span class="branch">филиал 1 <i class="fas fa-map-marker-alt"></i></span>
                        </div>
                        <div class="review-card-new-text">${escapeHtml(review.text || 'Нет текста отзыва')}</div>
                    </div>`;
                }).join('');
                
                // Add placeholder cards for visual consistency
                listEl.innerHTML += '<div class="review-card-new placeholder"></div>';
                listEl.innerHTML += '<div class="review-card-new placeholder"></div>';
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

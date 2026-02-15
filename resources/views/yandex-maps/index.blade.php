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
    .review-card-new-header .branch {
        font-weight: 500;
    }
    .review-card-new-header .icon {
        color: #EF4444; 
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

    /* --- STYLES FOR YANDEX CONNECT FORM --- */
    .campaigns-section {
        max-width: 600px;
        padding: 20px;
        font-family: 'Mulish', 'Inter', sans-serif;
        animation: fadeIn 0.5s ease-out;
    }
    .section-title {
        color: #252733;
        font-size: 16px;
        font-weight: 600;
        line-height: 20px;
        letter-spacing: 0.2px;
        margin-bottom: 8px;
    }
    .section-description {
        color: #6C757D;
        font-size: 12px;
        font-weight: 600;
        line-height: 20px;
        letter-spacing: 0.2px;
        margin-bottom: 12px;
    }
    .input-wrapper {
        width: 100%;
        max-width: 480px;
        background: white;
        border: 1px solid #DCE4EA;
        border-radius: 6px;
        padding: 6px 14px;
        margin-bottom: 12px;
        transition: border-color 0.3s ease;
    }
    .input-wrapper:focus-within {
        border-color: #339AF0;
        box-shadow: 0 0 0 2px rgba(51, 154, 240, 0.1);
    }
    .url-input {
        width: 100%;
        border: none;
        outline: none;
        color: #788397;
        font-size: 12px;
        font-family: 'Mulish', 'sans-serif';
        font-weight: 400;
        text-decoration: underline;
        background: transparent;
    }
    .url-input[readonly] {
        cursor: default;
        opacity: 0.9;
    }
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 128px;
        height: 38px;
        padding: 0 16px;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        font-family: 'Inter', sans-serif;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        white-space: nowrap;
    }
    .btn-primary {
        background: #339AF0;
        color: white;
    }
    .btn-primary:hover {
        background: #2b8ad4;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(51, 154, 240, 0.3);
    }
    .btn-primary:active {
        background: #247ac0;
        transform: translateY(0);
        box-shadow: none;
    }
    .btn-primary:focus-visible {
        outline: 2px solid #339AF0;
        outline-offset: 2px;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* --- ADAPTIVE STYLES --- */
    @media (max-width: 768px) {
        .campaigns-section {
            padding: 16px;
        }
        
        .input-wrapper {
            max-width: 100%;
        }
        
        .btn {
            width: 100%;
            max-width: 128px;
        }
    }

    @media (max-width: 480px) {
        .btn {
            max-width: 100%;
            width: 100%;
        }
        
        .url-input {
            font-size: 11px;
        }
        
        .review-card-new {
            padding: 15px;
        }
        
        .review-card-new-header {
            flex-wrap: wrap;
            gap: 8px;
        }
    }

    @media (max-width: 320px) {
        .section-title {
            font-size: 14px;
        }
        
        .section-description {
            font-size: 11px;
        }
    }

    /* --- UTILITY CLASSES --- */
    .url-display {
        width: 471px;
        color: #788397;
        font-size: 12px;
        font-family: 'Mulish', 'sans-serif';
        font-weight: 400;
        text-decoration: underline;
        word-wrap: break-word;
        margin-top: 8px;
    }

    .hidden {
        display: none;
    }

    /* --- ACCESSIBILITY --- */
    @media (prefers-reduced-motion: reduce) {
        .btn,
        .input-wrapper,
        .campaigns-section,
        .review-card-new {
            transition: none;
            animation: none;
        }
    }

    /* --- DARK THEME (OPTIONAL) --- */
    @media (prefers-color-scheme: dark) {
        .input-wrapper {
            background: #2d2d2d;
            border-color: #404040;
        }
        
        .url-input {
            color: #a0a0a0;
        }
        
        .section-title {
            color: #e0e0e0;
        }
        
        .section-description {
            color: #b0b0b0;
        }
    }

    /* --- PRINT STYLES --- */
    @media print {
        .btn {
            display: none;
        }
        
        .input-wrapper {
            border: 1px solid #000;
            background: none;
        }
        
        .url-input {
            color: #000;
            text-decoration: none;
        }
    }
</style>

@if ($settings && $settings->yandex_maps_url)
    <div class="reviews-main-container">
        <div class="reviews-header">
            <span class="reviews-header-icon"><i class="fas fa-external-link-alt"></i></span>
        </div>
        <div id="fetchStatus" class="fetch-status" style="display: none;"></div>
        <div id="reviewsList"></div>
    </div>

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

        // Helper function to escape HTML
        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    </script>
@else
    <div class="campaigns-section">
        <h2 class="section-title">Подключить Яндекс</h2>
        <p class="section-description">Укажите ссылку на Яндекс.Карты с отзывами</p>
        
        <form action="{{ route('yandex-maps.connect') }}" method="POST">
            @csrf
            <div class="input-wrapper">
                <input 
                    type="url" 
                    name="yandex_maps_url"
                    class="url-input" 
                    value="https://yandex.ru/maps/org/samoye_populyarnoye_kafe/1010501395/reviews/"
                    placeholder="https://yandex.ru/maps/org/..."
                    required
                >
            </div>
            
            @error('yandex_maps_url')
                <div style="color: #EF4444; font-size: 12px; margin-bottom: 10px;">
                    {{ $message }}
                </div>
            @enderror
            
            <button type="submit" class="btn btn-primary">
                Сохранить
            </button>
        </form>
        
        <div class="url-display hidden">
            https://yandex.ru/maps/org/samoye_populyarnoye_kafe/1010501395/reviews/
        </div>
    </div>
@endif

@endsection
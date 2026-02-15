@extends('layouts.app')

@section('content')
<style>
    .tabs {
        display: flex;
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 20px;
        padding: 0 20px;
    }
    .tab-item {
        padding: 12px 20px;
        cursor: pointer;
        border: none;
        background: none;
        font-size: 15px;
        font-weight: 600;
        color: #6C757D;
        border-bottom: 3px solid transparent;
        transition: all 0.3s ease;
    }
    .tab-item.active {
        border-bottom-color: #339AF0;
        color: #252733;
    }
    .tab-content {
        display: none;
        animation: fadeIn 0.5s ease-out;
    }
    .tab-content.active {
        display: block;
    }
    .container-padding {
        padding: 0 20px;
    }

    /* --- STYLES FOR REVIEWS --- */
    .reviews-main-container {
        padding: 10px;
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
        display: flex; align-items: center; color: #6C757D;
        font-size: 14px; margin-bottom: 15px; gap: 15px;
    }
    .review-card-new-author strong { font-size: 15px; font-weight: 600; }
    .review-card-new-text { line-height: 1.6; color: #363740; font-size: 14px; }
    .no-reviews, .fetch-status { text-align: center; padding: 40px; color: #6C757D; }

    /* --- STYLES FOR SETTINGS FORM --- */
    .campaigns-section {
        max-width: 600px;
        font-family: 'Mulish', 'Inter', sans-serif;
    }
    .section-title { color: #252733; font-size: 16px; font-weight: 600; margin-bottom: 8px; }
    .section-description { color: #6C757D; font-size: 12px; font-weight: 600; margin-bottom: 12px; }
    .input-wrapper {
        width: 100%; max-width: 480px; background: white;
        border: 1px solid #DCE4EA; border-radius: 6px;
        padding: 6px 14px; margin-bottom: 12px; transition: border-color 0.3s ease;
    }
    .input-wrapper:focus-within { border-color: #339AF0; box-shadow: 0 0 0 2px rgba(51, 154, 240, 0.1); }
    .url-input {
        width: 100%; border: none; outline: none; color: #788397;
        font-size: 12px; background: transparent; text-decoration: underline;
    }
    .btn { 
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 128px; height: 38px; padding: 0 16px; border: none;
        border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer;
        transition: all 0.3s ease; text-align: center;
    }
    .btn-primary { background: #339AF0; color: white; }
    .btn-primary:hover { background: #2b8ad4; }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="tabs">
    <button class="tab-item active" data-tab="reviews">Отзывы</button>
    <button class="tab-item" data-tab="settings">Настройка</button>
</div>

<div id="reviews" class="tab-content active">
    <div class="container-padding">
        @if ($settings && $settings->yandex_maps_url)
            <div class="reviews-main-container">
                <div id="fetchStatus" class="fetch-status" style="display: none;"></div>
                <div id="reviewsList"></div>
            </div>
        @else
            <div class="no-reviews">
                <p>Чтобы увидеть отзывы, подключите Яндекс.Карты в разделе "Настройка".</p>
            </div>
        @endif
    </div>
</div>

<div id="settings" class="tab-content">
    <div class="container-padding">
        <div class="campaigns-section">
            <h2 class="section-title">{{ ($settings && $settings->yandex_maps_url) ? 'Настройки Яндекс.Карт' : 'Подключить Яндекс' }}</h2>
            <p class="section-description">Укажите ссылку на страницу вашей организации в Яндекс.Картах</p>
            
            <form action="{{ route('yandex-maps.connect') }}" method="POST">
                @csrf
                <div class="input-wrapper">
                    <input 
                        type="url" 
                        name="yandex_maps_url"
                        class="url-input" 
                        value="{{ ($settings && $settings->yandex_maps_url) ? $settings->yandex_maps_url : 'https://yandex.ru/maps/org/samoye_populyarnoye_kafe/1010501395/reviews/' }}"
                        placeholder="https://yandex.ru/maps/org/..."
                        required
                    >
                </div>
                @error('yandex_maps_url')
                    <div style="color: #EF4444; font-size: 12px; margin-bottom: 10px;">{{ $message }}</div>
                @enderror
                <button type="submit" class="btn btn-primary">
                    {{ ($settings && $settings->yandex_maps_url) ? 'Обновить' : 'Сохранить' }}
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- TAB SWITCHING LOGIC ---
    const tabs = document.querySelectorAll('.tab-item');
    const tabContents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(item => item.classList.remove('active'));
            tab.classList.add('active');

            const target = document.getElementById(tab.dataset.tab);
            tabContents.forEach(content => content.classList.remove('active'));
            target.classList.add('active');
        });
    });

    // --- LOAD REVIEWS LOGIC ---
    @if ($settings && $settings->yandex_maps_url)
        loadYandexReviews('{{ $settings->yandex_maps_url }}');
    @endif
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
                year: 'numeric', month: '2-digit', day: '2-digit', 
                hour: '2-digit', minute: '2-digit' 
            }).format(date) : 'Дата не указана';
        
        return `
            <div class="review-card-new">
                <div class="review-card-new-header">
                    <span>${formattedDate}</span>
                    <span class="branch">филиал 1</span>
                    <span class="icon"><i class="fas fa-map-marker-alt"></i></span>
                </div>
                <div class="review-card-new-author"><strong>${escapeHtml(review.author || 'Аноним')}</strong></div>
                <div class="review-card-new-text">${escapeHtml(review.text || 'Нет текста отзыва')}</div>
            </div>
        `;
    }).join('');
}

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}
</script>
@endsection

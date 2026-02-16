@extends('layouts.app')

@section('content')
<style>
    .container {
        padding: 20px;
        font-family: 'Mulish', sans-serif;
    }
    .reviews-container {
        margin-top: 20px;
    }
    .review {
        border-bottom: 1px solid #eee;
        padding: 10px 0;
    }
    .review-author {
        font-weight: bold;
    }
    .review-date {
        color: #888;
        font-size: 0.9em;
    }
</style>

<div class="container">
    <h2>Настройки Яндекс Карт</h2>

    @if ($settings && $settings->yandex_maps_url)
        <p><strong>Текущий URL:</strong> {{ $settings->yandex_maps_url }}</p>
        <button id="fetch-reviews-btn" class="button-base">Загрузить отзывы</button>
    @else
        <p>URL Яндекс Карт не настроен. Перейдите на <a href="{{ route('yandex-maps.index') }}">страницу подключения</a>.</p>
    @endif

    <div id="reviews-container" class="reviews-container"></div>
</div>

<script>
    document.getElementById('fetch-reviews-btn').addEventListener('click', function() {
        const url = '{{ $settings->yandex_maps_url ?? '' }}';
        if (!url) {
            alert('URL не указан');
            return;
        }

        fetch('{{ route("yandex-maps.fetch-reviews") }}?url=' + encodeURIComponent(url))
            .then(response => response.json())
            .then(data => {
                const reviewsContainer = document.getElementById('reviews-container');
                reviewsContainer.innerHTML = ''; // Clear previous reviews

                if (data.success && data.reviews.length > 0) {
                    let reviewsHtml = '<h3>Отзывы:</h3>';
                    data.reviews.forEach(review => {
                        reviewsHtml += `
                            <div class="review">
                                <div class="review-author">${review.author}</div>
                                <div class="review-date">${review.date}</div>
                                <div>${review.text}</div>
                            </div>
                        `;
                    });
                    reviewsContainer.innerHTML = reviewsHtml;
                } else {
                    reviewsContainer.innerHTML = '<p>' + (data.error || 'Отзывы не найдены.') + '</p>';
                }
            })
            .catch(error => {
                console.error('Error fetching reviews:', error);
                document.getElementById('reviews-container').innerHTML = '<p>Ошибка при загрузке отзывов.</p>';
            });
    });
</script>
@endsection

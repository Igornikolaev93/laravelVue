@extends('layouts.app')

@section('content')
<style>
.input-group {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 20px;
    background: #F6F8FA;
    padding: 20px 25px;
    border-radius: 16px;
    margin-top: 5px;
}
.field {
    display: flex;
    flex-direction: column;
    gap: 8px;
    flex: 2;
    min-width: 380px;
}
.field label {
    color: #6C757D;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.2px;
}
.field input {
    background: white;
    border: 1px solid #DCE4EA;
    border-radius: 8px;
    padding: 10px 16px;
    font-size: 12px;
    color: #788397;
    width: 100%;
    font-family: 'Mulish', sans-serif;
}
.save-btn {
    background: #339AF0;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 12px 32px;
    font-family: 'Inter', sans-serif;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    height: fit-content;
    white-space: nowrap;
}
.reviews-feed {
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.review-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0px 3px 6px rgba(92,101,111,0.30);
    border: 1px solid #E0E7EC;
}
.review-inner {
    background: #F6F8FA;
    margin: 12px;
    padding: 18px 22px;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.review-header {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 20px;
}
.review-meta {
    display: flex;
    gap: 30px;
    align-items: center;
}
.review-meta .name {
    font-weight: 700;
    font-size: 12px;
    color: #363740;
}
</style>
    <div class="input-group">
        <div class="field">
            <label>Укажите ссылку на Яндекс, пример</label>
            <input type="text" id="yandex_maps_url" value="https://yandex.ru/maps/46/kirov/?ll=49.680826%2C58.602742&mode=poi&poi%5Bpoint%5D=49.682197%2C58.597895&poi%5Buri%5D=ymapsbm1%3A%2F%2Forg%3Foid%3D198060798118&z=13">
        </div>
        <button class="save-btn">Сохранить</button>
    </div>

    <div class="reviews-feed">
    </div>

<script>
    document.querySelector('.save-btn').addEventListener('click', function (e) {
        e.preventDefault();

        const urlInput = document.getElementById('yandex_maps_url');
        const url = urlInput.value;
        const reviewsFeed = document.querySelector('.reviews-feed');
        const inputGroup = document.querySelector('.input-group');

        if (!url) {
            alert('Пожалуйста, введите URL');
            return;
        }

        reviewsFeed.innerHTML = '<p>Загрузка отзывов...</p>';

        fetch('{{ route('yandex-maps.fetch-reviews') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ url: url })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                inputGroup.style.display = 'none';
                let reviewsHtml = '';
                if (data.reviews && data.reviews.length > 0) {
                    data.reviews.forEach(review => {
                        reviewsHtml += `
                            <div class="review-card">
                                <div class="review-inner">
                                    <div class="review-header">
                                        <span style="font-weight:700; font-size:12px;">${review.date}</span>
                                    </div>
                                    <div class="review-meta">
                                        <span class="name">${review.author}</span>
                                    </div>
                                    <div class="review-text">
                                       ${review.text}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    reviewsHtml = '<p>Отзывов не найдено.</p>';
                }
                reviewsFeed.innerHTML = reviewsHtml;

            } else {
                reviewsFeed.innerHTML = '<p>Ошибка: ' + (data.error || 'Не удалось получить отзывы.') + '</p>';
            }
        })
        .catch(error => {
            reviewsFeed.innerHTML = '<p>Произошла ошибка при запросе. Проверьте консоль для деталей.</p>';
            console.error('Error:', error);
        });
    });
</script>
@endsection

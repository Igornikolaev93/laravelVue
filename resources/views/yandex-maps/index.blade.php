@extends('layouts.app')

@section('content')
<style>
.platform-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0px 3px 6px rgba(92,101,111,0.30);
    border: 1px solid #E0E7EC;
    padding: 20px 24px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
}
.platform-info {
    display: flex;
    align-items: center;
    gap: 15px;
}
.rating-block {
    display: flex;
    align-items: center;
    gap: 12px;
}
.stars {
    display: flex;
    gap: 6px;
    color: #FBBC04;
    font-size: 22px;
}
.stars .grey {
    color: #6C757D;
    opacity: 0.4;
}
.rating-value {
    font-size: 40px;
    font-weight: 500;
    color: #363740;
    line-height: 1;
}
.reviews-total {
    font-size: 12px;
    font-weight: 700;
    color: #363740;
    border-top: 2px solid #F1F4F7;
    padding-top: 8px;
    margin-top: 4px;
}
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
    text-decoration: underline;
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
    display: none; /* Initially hidden */
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
.review-meta .phone {
    font-weight: 700;
    font-size: 10px;
    color: #363740;
}
.review-icons {
    display: flex;
    gap: 8px;
    color: #DCE4EA;
    font-size: 14px;
}
.location-marker {
    display: flex;
    align-items: center;
    gap: 4px;
    background: white;
    border-radius: 20px;
    padding: 2px 12px 2px 8px;
    font-size: 12px;
    font-weight: 700;
    color: #363740;
}
.location-marker i {
    color: #FF4433;
    font-size: 16px;
}
.review-text {
    font-size: 12px;
    color: black;
    line-height: 1.5;
    max-width: 780px;
}

</style>

<div class="platform-card">
    <div class="platform-info">
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-map-marker-alt" style="color:#FF4433; font-size: 20px;"></i>
            <span style="font-weight: 600; background: #F0F2F5; padding: 6px 16px; border-radius: 30px; font-size: 14px;">Яндекс Карты</span>
        </div>
        <div class="rating-block">
            <span class="rating-value">0.0</span>
            <div class="stars">
                <i class="fas fa-star grey"></i><i class="fas fa-star grey"></i><i class="fas fa-star grey"></i><i class="fas fa-star grey"></i><i class="fas fa-star grey"></i>
            </div>
        </div>
    </div>
    <div class="reviews-total">Всего отзывов: 0</div>
</div>

<div class="input-group">
    <div class="field">
        <label>Укажите ссылку на Яндекс, пример</label>
        <input type="text" id="yandex_maps_url" value="https://yandex.ru/maps/org/samoye_populyarnoye_kafe/1010501395/reviews/">
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

        reviewsFeed.style.display = 'flex';
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
            const platformCard = document.querySelector('.platform-card');
            const ratingValue = platformCard.querySelector('.rating-value');
            const reviewsTotal = platformCard.querySelector('.reviews-total');
            const starsContainer = platformCard.querySelector('.stars');

            if (data.success) {
                inputGroup.style.display = 'none';

                // Update stats
                ratingValue.textContent = data.stats.average_rating;
                reviewsTotal.textContent = 'Всего отзывов: ' + data.stats.total_reviews;
                
                let starsHtml = '';
                let avg_rating = Math.round(data.stats.average_rating);
                for (let i = 1; i <= 5; i++) {
                    if (i <= avg_rating) {
                        starsHtml += '<i class="fas fa-star"></i>';
                    } else {
                        starsHtml += '<i class="fas fa-star grey"></i>';
                    }
                }
                starsContainer.innerHTML = starsHtml;

                // Update reviews
                let reviewsHtml = '';
                if (data.reviews && data.reviews.length > 0) {
                    data.reviews.forEach(review => {
                         let reviewStars = '';
                         for (let i = 1; i <= 5; i++) {
                             if (i <= review.rating) {
                                 reviewStars += '<i class="fas fa-star"></i>';
                             } else {
                                 reviewStars += '<i class="fas fa-star grey"></i>';
                             }
                         }

                        reviewsHtml += `
                            <div class="review-card">
                                <div class="review-inner">
                                    <div class="review-header">
                                        <span style="font-weight:700; font-size:12px;">${review.date}</span>
                                         <div class="stars" style="font-size: 14px; color: #FBBC04;">
                                             ${reviewStars}
                                         </div>
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

@extends('layouts.app')

@section('content')
<style>
.main-container, [data-layer="Подключение площадок"] {
    width: 1381px;
    background: white;
    position: relative;
    min-height: 2014px;
    margin: 20px auto;
    box-shadow: 0 0 20px rgba(0,0,0,0.05);
}

.header, [data-layer="Rectangle 121"] {
    width: 100%;
    height: 75px;
    background: white;
    border-bottom: 1px solid #DCE4EA;
}

.top-icon, [data-layer="Rectangle 122"] {
    position: absolute;
    right: 90px;
    top: 14px;
    font-size: 24px;
    color: #909AB4;
}

.vector-icon {
    position: absolute;
    font-size: 20px;
    color: #909AB4;
}

.phone-icon {
    right: 77px;
    top: 26px;
}

.arrow-icon {
    right: 69px;
    top: 31px;
}

.sidebar, [data-layer="fon"] {
    width: 280px;
    background: #F6F8FA;
    box-shadow: 0px 4px 3px #E5E5E5;
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    height: 2014px;
}

.logo, [data-layer="Daily Grow"] {
    position: absolute;
    left: 55.25px;
    top: 34.37px;
    z-index: 2;
}

.logo-text {
    font-family: 'Mulish', sans-serif;
    font-size: 24px;
    font-weight: bold;
    color: #363740;
}

.logo-decor {
    position: absolute;
    width: 6px;
    height: 10px;
    z-index: 2;
}

.decor-1 {
    left: 29.69px;
    top: 30px;
    background: #03A3EA;
    clip-path: polygon(0 0, 100% 50%, 0 100%);
}

.decor-2 {
    left: 34.94px;
    top: 35.25px;
    background: #039DE5;
    clip-path: polygon(0 0, 100% 50%, 0 100%);
}

.decor-3 {
    left: 40.19px;
    top: 40.21px;
    background: #0256B2;
    clip-path: polygon(0 0, 100% 50%, 0 100%);
}

.decor-4 {
    left: 29.69px;
    top: 35.25px;
    background: #0399E2;
    clip-path: polygon(100% 0, 100% 100%, 0 50%);
}

.decor-5 {
    left: 34.94px;
    top: 40.21px;
    background: #0381D1;
    clip-path: polygon(100% 0, 100% 100%, 0 50%);
}

.decor-6 {
    left: 29px;
    top: 45.17px;
    width: 12px;
    height: 13px;
    background: #0256B2;
    clip-path: polygon(0 0, 100% 40%, 40% 100%);
}

.menu, [data-layer="Menu"] {
    width: 249px;
    height: 52px;
    position: absolute;
    left: 15px;
    top: 120px;
    overflow: hidden;
    z-index: 3;
}

.menu-selector, [data-layer="Menu"] [data-layer="Selector"] {
    width: 249px;
    height: 48px;
    background: white;
    box-shadow: 0px 2px 1px rgba(0, 0, 0, 0.02);
    border-radius: 12px;
}

.menu-base {
    width: 280px;
    height: 52px;
}

.menu-selector-transparent {
    width: 249px;
    height: 48px;
    background: rgba(255, 255, 255, 0);
    border-radius: 12px;
}

.menu-text, [data-layer="Menu"] [data-layer="Overview"] {
    position: absolute;
    left: 51px;
    top: 14px;
    color: #363740;
    font-size: 16px;
    font-weight: 500;
}

.menu-icon {
    position: absolute;
    left: 15px;
    top: 12px;
    color: #DCE4EA;
    font-size: 20px;
}

.sidebar-selector, [data-layer="Selector"][style*="width: 249px; height: 23px;"] {
    width: 249px;
    height: 23px;
    background: white;
    box-shadow: 0px 2px 1px rgba(0, 0, 0, 0.02);
    border-radius: 12px;
    position: absolute;
    left: 15px;
    top: 201px;
    z-index: 2;
}

.sidebar-menu-item, [data-layer="Overview"] {
    color: #363740;
    font-size: 12px;
    font-weight: 500;
    position: absolute;
    z-index: 2;
}

.reviews {
    left: 65px;
    top: 180px;
}

.settings {
    left: 65px;
    top: 205px;
}

.account-name, [data-layer="3"] {
    position: absolute;
    left: 15px;
    top: 72px;
    color: #6C757D;
    font-size: 16px;
    font-family: 'Mulish', sans-serif;
    font-weight: 700;
    line-height: 20px;
    letter-spacing: 0.20px;
    z-index: 2;
}

.additional-selector, [data-layer="Selector"][style*="width: 51px;"] {
    width: 51px;
    height: 47.08px;
    position: absolute;
    left: 32px;
    top: 670px;
    box-shadow: 0px 2px 1px rgba(0, 0, 0, 0.02);
    border-radius: 12px;
    z-index: 2;
}

.small-arrow {
    position: absolute;
    left: 4px;
    top: 5.73px;
    color: #6C757D;
    font-size: 8px;
    z-index: 3;
}

.campaign-title, [data-layer="Кампании"] {
    position: absolute;
    left: 315px;
    top: 93px;
    color: #252733;
    font-size: 16px;
    font-weight: 600;
    line-height: 20px;
    letter-spacing: 0.20px;
}

.input-label, [data-layer="группа 31"] {
    position: absolute;
    left: 315px;
    top: 128px;
    color: #6C757D;
    font-size: 12px;
    font-weight: 600;
    line-height: 20px;
    letter-spacing: 0.20px;
}

.input-container, [data-layer=""] {
    width: 480px;
    height: 24px;
    padding: 6px 14px;
    position: absolute;
    left: 315px;
    top: 157px;
    background: white;
    border-radius: 6px;
    outline: 1px solid #DCE4EA;
    outline-offset: -1px;
    display: inline-flex;
    align-items: center;
    gap: 15px;
}

.input-text, [data-layer*="https://yandex.ru"] {
    width: 471px;
    color: #788397;
    font-size: 12px;
    font-family: 'Mulish', sans-serif;
    font-weight: 400;
    text-decoration: underline;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.duplicate-text {
    display: none;
}

.button-base, [data-layer="Base"][style*="background: #339AF0;"] {
    width: 128px;
    height: 25px;
    position: absolute;
    left: 315px;
    top: 199px;
    background: #339AF0;
    border-radius: 6px;
}

.button-container, [data-layer="Text"] {
    width: 96px;
    height: 14.17px;
    position: absolute;
    left: 331px;
    top: 204px;
    overflow: hidden;
    border-radius: 6px;
}

.button-text, [data-layer="+ Add Dashlet"] {
    position: absolute;
    left: 10px;
    top: -2px;
    color: white;
    font-size: 14px;
    font-family: 'Inter', sans-serif;
    font-weight: 600;
    text-align: center;
    white-space: nowrap;
}

.reviews-feed {
    display: none; /* Initially hidden */
    flex-direction: column;
    gap: 20px;
    position: absolute;
    left: 315px;
    top: 250px;
    width: 70%;
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
</style>

<div data-layer="Подключение площадок" class="main-container">
    <!-- Header -->
    <div data-layer="Rectangle 121" class="header"></div>
    
    <!-- Top right icons (replaced SVG with text) -->
    <div data-layer="Rectangle 122" class="top-icon">
        <i class="far fa-square"></i>
    </div>
    <div data-layer="Vector" class="vector-icon phone-icon">
        <i class="fas fa-mobile-alt"></i>
    </div>
    <div data-layer="Vector" class="vector-icon arrow-icon">
        <i class="fas fa-arrow-right"></i>
    </div>

    @include('layouts.sidebar')

    <div id="initial-view">
        <div data-layer="Кампании" class="campaign-title">Подключить Яндекс</div>
        <div data-layer="группа 31" class="input-label">Укажите ссылку на Яндекс, пример</div>
        
        <div data-layer="" class="input-container">
            <input type="text" id="yandex_maps_url" class="input-text" value="https://yandex.ru/maps/org/samoye_populyarnoye_kafe/1010501395/reviews/">
        </div>

        <div data-layer="Base" class="button-base save-btn"></div>
        <div data-layer="Text" class="button-container save-btn">
            <div data-layer="+ Add Dashlet" class="button-text">Сохранить</div>
        </div>
    </div>

    <div class="reviews-feed">
    </div>
</div>

<script>
    document.querySelector('.save-btn').addEventListener('click', function (e) {
        e.preventDefault();

        const urlInput = document.getElementById('yandex_maps_url');
        const url = urlInput.value;
        const reviewsFeed = document.querySelector('.reviews-feed');
        const initialView = document.getElementById('initial-view');

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
            if (data.success) {
                initialView.style.display = 'none';
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

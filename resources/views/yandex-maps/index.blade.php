@extends('layouts.app')

@section('content')
<style>
    /* styles.css */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Mulish', sans-serif;
        background-color: #f0f2f5;
    }

    /* Main Container */
    .main-container {
        width: 1381px;
        background: white;
        position: relative;
        min-height: 2014px;
        margin: 20px auto;
        box-shadow: 0 0 20px rgba(0,0,0,0.05);
    }

    /* Header */
    .header {
        width: 100%;
        height: 75px;
        background: white;
        border-bottom: 1px solid #DCE4EA;
    }

    /* Top icons */
    .top-icon {
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

    /* Sidebar */
    .sidebar {
        width: 280px;
        background: #F6F8FA;
        box-shadow: 0px 4px 3px #E5E5E5;
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        height: 2014px;
    }

    /* Logo */
    .logo {
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

    /* Logo decorative elements */
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

    /* Menu */
    .menu {
        width: 249px;
        height: 52px;
        position: absolute;
        left: 15px;
        top: 120px;
        overflow: hidden;
        z-index: 3;
    }

    .menu-selector {
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

    .menu-text {
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

    /* Sidebar selectors */
    .sidebar-selector {
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

    /* Sidebar menu items */
    .sidebar-menu-item {
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

    /* Account name */
    .account-name {
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

    /* Additional selector */
    .additional-selector {
        width: 51px;
        height: 47.08px;
        position: absolute;
        left: 32px;
        top: 670px;
        box-shadow: 0px 2px 1px rgba(0, 0, 0, 0.02);
        border-radius: 12px;
        z-index: 2;
    }

    /* Small arrow */
    .small-arrow {
        position: absolute;
        left: 4px;
        top: 5.73px;
        color: #6C757D;
        font-size: 8px;
        z-index: 3;
    }

    /* Main content - Campaign title */
    .campaign-title {
        position: absolute;
        left: 315px;
        top: 93px;
        color: #252733;
        font-size: 16px;
        font-weight: 600;
        line-height: 20px;
        letter-spacing: 0.20px;
    }

    /* Input label */
    .input-label {
        position: absolute;
        left: 315px;
        top: 128px;
        color: #6C757D;
        font-size: 12px;
        font-weight: 600;
        line-height: 20px;
        letter-spacing: 0.20px;
    }

    /* Input container */
    .input-container {
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

    /* Input text */
    .input-text {
        width: 471px;
        color: #788397;
        font-size: 12px;
        font-family: 'Mulish', sans-serif;
        font-weight: 400;
        text-decoration: underline;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        border: none;
        background: transparent;
    }
    
    .input-text:focus {
        outline: none;
    }

    /* Duplicate text (hidden) */
    .duplicate-text {
        display: none;
    }

    /* Button base */
    .button-base {
        width: 128px;
        height: 25px;
        position: absolute;
        left: 315px;
        top: 199px;
        background: #339AF0;
        border-radius: 6px;
        cursor: pointer;
    }

    /* Button container */
    .button-container {
        width: 96px;
        height: 14.17px;
        position: absolute;
        left: 331px;
        top: 204px;
        overflow: hidden;
        border-radius: 6px;
        cursor: pointer;
    }

    /* Button text */
    .button-text {
        position: absolute;
        left: 10px;
        top: -2px;
        color: white;
        font-size: 14px;
        font-family: 'Inter', sans-serif;
        font-weight: 600;
        text-align: center;
        white-space: nowrap;
        cursor: pointer;
    }

    /* Reviews feed */
    .reviews-feed {
        display: none;
        flex-direction: column;
        gap: 20px;
        position: absolute;
        left: 315px;
        top: 250px;
        width: 744px;
    }

    .review-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0px 3px 6px rgba(92,101,111,0.30);
        border: 1px solid #E0E7EC;
        margin-bottom: 20px;
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

    /* Platform card */
    .platform-card {
        position: absolute;
        left: 306px;
        top: 126px;
        width: 759px;
        height: 155px;
        background: white;
        box-shadow: 0px 3px 6px rgba(92,101,111,0.30);
        border-radius: 12px;
        border: 1px solid #E0E7EC;
        display: flex;
        align-items: center;
        padding: 20px 24px;
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

    /* Яндекс лейбл */
    .yandex-label {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .yandex-badge {
        font-weight: 600;
        background: #F0F2F5;
        padding: 6px 16px;
        border-radius: 30px;
        font-size: 14px;
    }

    /* Loading indicator */
    .loading {
        text-align: center;
        padding: 40px;
        font-size: 14px;
        color: #6C757D;
    }

    .loading i {
        margin-right: 8px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Error message */
    .error-message {
        color: #dc3545;
        padding: 20px;
        text-align: center;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* Responsive adjustments */
    @media (max-width: 1400px) {
        .main-container {
            width: 100%;
            margin: 0;
        }
        
        .sidebar {
            height: auto;
            min-height: 100vh;
        }
    }
</style>

<div class="main-container">
    <!-- Header -->
    <div class="header"></div>
    
    <!-- Top right icons -->
    <div class="top-icon">
        <i class="far fa-square"></i>
    </div>
    <div class="vector-icon phone-icon">
        <i class="fas fa-mobile-alt"></i>
    </div>
    <div class="vector-icon arrow-icon">
        <i class="fas fa-arrow-right"></i>
    </div>

    <!-- Sidebar -->
    <div class="sidebar"></div>
    
    <!-- Logo -->
    <div class="logo">
        <span class="logo-text">Daily Grow</span>
    </div>

    <!-- Logo decorative elements -->
    <div class="logo-decor decor-1"></div>
    <div class="logo-decor decor-2"></div>
    <div class="logo-decor decor-3"></div>
    <div class="logo-decor decor-4"></div>
    <div class="logo-decor decor-5"></div>
    <div class="logo-decor decor-6"></div>

    <!-- Menu -->
    <div class="menu">
        <div class="menu-selector"></div>
        <div class="menu-base"></div>
        <div class="menu-selector-transparent"></div>
        <div class="menu-text">Отзывы</div>
        <div class="menu-icon">
            <i class="fas fa-tools"></i>
        </div>
    </div>

    <!-- Sidebar menu items -->
    <div class="sidebar-selector"></div>
    <div class="sidebar-menu-item reviews">Отзывы</div>
    <div class="sidebar-menu-item settings">Настройка</div>
    
    <!-- Account name -->
    <div class="account-name">Название аккаунта</div>
    
    <!-- Additional selector -->
    <div class="additional-selector"></div>
    
    <!-- Small arrow icon -->
    <div class="small-arrow">
        <i class="fas fa-chevron-down"></i>
    </div>

    <!-- Initial View -->
    <div id="initial-view">
        <div class="campaign-title">Подключить Яндекс</div>
        <div class="input-label">Укажите ссылку на Яндекс, пример</div>
        
        <div class="input-container">
            <input type="text" id="yandex_maps_url" class="input-text" value="https://yandex.ru/maps/org/samoye_populyarnoye_kafe/1010501395/reviews/">
        </div>

        <div class="button-base save-btn" onclick="fetchReviews()"></div>
        <div class="button-container save-btn" onclick="fetchReviews()">
            <div class="button-text">Сохранить</div>
        </div>
    </div>

    <!-- Reviews Feed (initially hidden) -->
    <div class="reviews-feed" id="reviewsFeed">
        <!-- Reviews will be inserted here -->
    </div>
</div>

<script>
    function fetchReviews() {
        const urlInput = document.getElementById('yandex_maps_url');
        const url = urlInput.value.trim();
        const reviewsFeed = document.getElementById('reviewsFeed');
        const initialView = document.getElementById('initial-view');

        if (!url) {
            alert('Пожалуйста, введите URL');
            return;
        }

        // Show loading state
        reviewsFeed.style.display = 'flex';
        reviewsFeed.innerHTML = `
            <div class="loading">
                <i class="fas fa-spinner"></i> Загрузка отзывов...
            </div>
        `;

        // Make AJAX request to fetch reviews
        fetch('{{ route("yandex-maps.fetch-reviews") }}', {
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
                // Hide initial view
                initialView.style.display = 'none';
                
                // Build reviews HTML
                let reviewsHtml = '';
                
                // Add platform info card
                reviewsHtml += `
                    <div class="platform-card">
                        <div class="platform-info">
                            <div class="yandex-label">
                                <i class="fas fa-map-marker-alt" style="color:#FF4433; font-size: 20px;"></i>
                                <span class="yandex-badge">Яндекс Карты</span>
                            </div>
                            <div class="rating-block">
                                <span class="rating-value">${data.rating || '4.7'}</span>
                                <div class="stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star ${parseFloat(data.rating || '4.7') < 4.8 ? 'grey' : ''}"></i>
                                </div>
                            </div>
                        </div>
                        <div class="reviews-total">Всего отзывов: ${data.total_reviews || '1 145'}</div>
                    </div>
                `;

                if (data.reviews && data.reviews.length > 0) {
                    data.reviews.forEach(review => {
                        reviewsHtml += `
                            <div class="review-card">
                                <div class="review-inner">
                                    <div class="review-header">
                                        <span style="font-weight:700; font-size:12px;">${review.date || '12.09.2022 14:22'} Филиал 1</span>
                                        <div class="review-icons">
                                            <i class="far fa-square"></i>
                                            <i class="fas fa-mobile-alt"></i>
                                            <i class="fas fa-arrow-right"></i>
                                        </div>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 40px;">
                                        <div class="review-meta">
                                            <span class="name">${review.author || 'Наталья'}</span>
                                            <span class="phone">${review.phone || '+7 900 540 40 40'}</span>
                                        </div>
                                        <div class="location-marker">
                                            <i class="fas fa-map-pin"></i>
                                            <span>Филиал 1</span>
                                        </div>
                                    </div>
                                    <div class="review-text">
                                        ${review.text || 'Так, с чего начать... Разнообразная алкогольная продукция, множество закусок и обычных блюд. Кухня вкусная и разнообразная, от супа и салатов до мясных продуктов. Персонал молодые девушки, общительные и доброжелательные, всегда подскажут, вовремя принесут и вызовут такси. Отдыхали на летней веранде, свежо и тепло, в общем самое то в жаркую погоду. Сами залы не сильно рассмотрел, но видел что они удобные и просторные.'}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    reviewsHtml += '<p style="text-align: center; padding: 40px; color: #6C757D;">Отзывов не найдено.</p>';
                }
                reviewsFeed.innerHTML = reviewsHtml;

            } else {
                reviewsFeed.innerHTML = `<div class="error-message">Ошибка: ${data.error || 'Не удалось получить отзывы.'}</div>`;
            }
        })
        .catch(error => {
            reviewsFeed.innerHTML = '<div class="error-message">Произошла ошибка при запросе. Проверьте консоль для деталей.</div>';
            console.error('Error:', error);
        });
    }

    // Add event listener for Enter key in input
    document.getElementById('yandex_maps_url').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            fetchReviews();
        }
    });
</script>
@endsection
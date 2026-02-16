<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отзывы Яндекс.Карт</title>
    <!-- Google Fonts + Font Awesome -->
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@400;500;600;700&family=Inter:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Базовый сброс */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Mulish', sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            padding: 20px;
        }

        /* каркас — флекс, без absolute */
        .app {
            max-width: 1381px;
            width: 100%;
            background: white;
            border-radius: 16px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
        }

        /* хедер */
        .header {
            height: 75px;
            border-bottom: 1px solid #DCE4EA;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 0 40px;
            gap: 24px;
        }
        .header-icon {
            color: #909AB4;
            font-size: 22px;
        }

        /* основной ряд: сайдбар + контент */
        .main-row {
            display: flex;
            align-items: stretch;
        }

        /* сайдбар */
        .sidebar {
            width: 280px;
            background: #F6F8FA;
            box-shadow: 0px 4px 3px #E5E5E5;
            padding: 20px 15px;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        /* блок логотипа (составной) */
        .logo-area {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: 10px;
        }
        .logo-shapes {
            display: flex;
            flex-wrap: wrap;
            width: 45px;
            height: 30px;
            position: relative;
        }
        /* эмуляция треугольных фигур через clip-path */
        .shape {
            width: 8px;
            height: 12px;
            background: #03A3EA;
            clip-path: polygon(0 0, 100% 50%, 0 100%);
            margin-right: 2px;
        }
        .shape.dark {
            background: #0256B2;
        }
        .shape.medium {
            background: #039DE5;
        }
        .shape.light {
            background: #0399E2;
        }
        .shape.reverse {
            clip-path: polygon(100% 0, 100% 100%, 0 50%);
            background: #0381D1;
        }
        .shape.special {
            width: 14px;
            height: 16px;
            background: #0256B2;
            clip-path: polygon(0 0, 100% 30%, 40% 100%);
            margin-top: 2px;
        }
        .logo-text {
            font-size: 24px;
            font-weight: 800;
            color: #363740;
            letter-spacing: -0.5px;
        }

        /* аккаунт */
        .account-name {
            color: #6C757D;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0.2px;
            padding-left: 10px;
        }

        /* меню */
        .menu-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            border-radius: 12px;
            background: white;
            box-shadow: 0px 2px 1px rgba(0,0,0,0.02);
            width: 100%;
            font-weight: 500;
            color: #363740;
        }
        .menu-item i {
            color: #DCE4EA;
            font-size: 22px;
            width: 24px;
        }
        .sub-menu {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-top: -8px;
        }
        .sub-item {
            padding: 8px 16px 8px 50px;
            font-size: 14px;
            font-weight: 500;
            color: #363740;
            background: white;
            border-radius: 12px;
            box-shadow: 0px 2px 1px rgba(0,0,0,0.02);
        }

        /* контент справа */
        .content {
            flex: 1;
            padding: 30px 35px;
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        /* карточка площадки (Яндекс) */
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

        /* инпут + кнопка */
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

        /* отзывы */
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

        /* маленькие доработки */
        .separator {
            height: 0;
            border-top: 1px solid #DCE4EA;
            margin: 0 0 10px 0;
        }
        .fa-chevron-down {
            font-size: 10px;
            color: #6C757D;
        }
    </style>
</head>
<body>
<div class="app">
    <!-- Шапка -->
    <header class="header">
        <i class="fas fa-square header-icon"></i>
        <i class="fas fa-mobile-alt header-icon"></i>
        <i class="fas fa-arrow-right header-icon"></i>
    </header>

    <div class="main-row">
        <!-- Боковая панель -->
        <aside class="sidebar">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div class="logo-area">
                    <div class="logo-shapes">
                        <div class="shape"></div>
                        <div class="shape medium"></div>
                        <div class="shape dark"></div>
                        <div class="shape reverse"></div>
                        <div class="shape" style="background:#0399E2;"></div>
                        <div class="shape special"></div>
                    </div>
                    <span class="logo-text">Daily Grow</span>
                </div>
                <i class="fas fa-chevron-down" style="color:#6C757D;"></i>
            </div>

            <div class="account-name">Название аккаунта</div>

            <!-- активное меню -->
            <div class="menu-item">
                <i class="fas fa-tools"></i>
                <span>Отзывы</span>
            </div>

            <div class="sub-menu">
                <div class="sub-item">Отзывы</div>
                <div class="sub-item">Настройка</div>
            </div>

            <!-- нижний декоративный селектор (можно просто отступ) -->
            <div style="margin-top: auto; height: 40px;"></div>
        </aside>

        <!-- основной контент -->
        <main class="content">
            <!-- плашка подключения / площадка -->
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

            <!-- поле ввода ссылки + кнопка -->
            <div class="input-group">
                <div class="field">
                    <label>Укажите ссылку на Яндекс, пример</label>
                    <input type="text" id="yandex_maps_url" placeholder="https://yandex.ru/maps/org/samoye_populyarnoye_kafe/1010501395/reviews/">
                </div>
                <button class="save-btn">Сохранить</button>
            </div>

            <!-- лента отзывов (три одинаковых карточки для демонстрации) -->
            <div class="reviews-feed">
            </div>
        </main>
    </div>
    <!-- небольшой отступ внизу -->
    <div style="height: 20px;"></div>
</div>
<script>
    document.querySelector('.save-btn').addEventListener('click', function (e) {
        e.preventDefault();

        const urlInput = document.getElementById('yandex_maps_url');
        const url = urlInput.value;
        const reviewsFeed = document.querySelector('.reviews-feed');
        
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
            const platformCard = document.querySelector('.platform-card');
            const ratingValue = platformCard.querySelector('.rating-value');
            const reviewsTotal = platformCard.querySelector('.reviews-total');
            const starsContainer = platformCard.querySelector('.stars');

            if (data.success) {
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
</body>
</html>

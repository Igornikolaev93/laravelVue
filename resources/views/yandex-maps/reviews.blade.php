
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подключение площадок</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Mulish', sans-serif;
    background-color: #f0f2f5;
    display: flex;
    justify-content: center;
}

.main-container {
    width: 1381px;
    background: white;
    position: relative;
    min-height: 2014px;
    margin: 20px auto;
    box-shadow: 0 0 20px rgba(0,0,0,0.05);
}

.header {
    width: 100%;
    height: 75px;
    background: white;
    border-bottom: 1px solid #DCE4EA;
}

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

.small-arrow {
    position: absolute;
    left: 4px;
    top: 5.73px;
    color: #6C757D;
    font-size: 8px;
    z-index: 3;
}

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

#yandex_maps_url {
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
    font-size: 12px;
    font-family: 'Mulish', sans-serif;
}

.button-base {
    width: 128px;
    height: 25px;
    position: absolute;
    left: 315px;
    top: 199px;
    background: #339AF0;
    border-radius: 6px;
    border: none;
    color: white;
    font-size: 14px;
    font-family: 'Inter', sans-serif;
    font-weight: 600;
    text-align: center;
    cursor: pointer;
}

#reviews-container {
    position: absolute;
    left: 315px;
    top: 250px;
    width: calc(100% - 345px);
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}

th {
    background-color: #f2f2f2;
}
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Header -->
        <div class="header"></div>
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
        <div class="logo">
            <span class="logo-text">Daily Grow</span>
        </div>
        <div class="logo-decor decor-1"></div>
        <div class="logo-decor decor-2"></div>
        <div class="logo-decor decor-3"></div>
        <div class="logo-decor decor-4"></div>
        <div class="logo-decor decor-5"></div>
        <div class="logo-decor decor-6"></div>

        <div class="menu">
            <div class="menu-selector"></div>
            <div class="menu-base"></div>
            <div class="menu-selector-transparent"></div>
            <div class="menu-text">Отзывы</div>
            <div class="menu-icon">
                <i class="fas fa-tools"></i>
            </div>
        </div>
        <div class="sidebar-selector"></div>
        <div class="sidebar-menu-item reviews">Отзывы</div>
        <div class="sidebar-menu-item settings">Настройка</div>
        <div class="account-name">Название аккаунта</div>
        <div class="additional-selector"></div>
        <div class="small-arrow">
            <i class="fas fa-chevron-down"></i>
        </div>

        <!-- Main content -->
        <div class="campaign-title">Подключить Яндекс</div>
        <div class="input-label">Укажите ссылку на Яндекс, пример</div>

        <form id="fetch-reviews-form">
            <input type="text" id="yandex_maps_url" name="yandex_maps_url" placeholder="https://yandex.ru/maps/org/samoye_populyarnoye_kafe/1010501395/reviews/">
            <button type="submit" class="button-base">Сохранить</button>
        </form>

        <div id="reviews-container">
            <!-- Reviews will be displayed here -->
        </div>
    </div>

    <script>
        document.getElementById('fetch-reviews-form').addEventListener('submit', function (e) {
            e.preventDefault();

            const url = document.getElementById('yandex_maps_url').value;
            const reviewsContainer = document.getElementById('reviews-container');
            reviewsContainer.innerHTML = '<p>Загрузка отзывов...</p>';

            fetch('{{ route('yandex-maps.fetch-reviews') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ url: url })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let html = '<h2>Статистика</h2>';
                    html += '<p>Всего отзывов: ' + data.stats.total_reviews + '</p>';
                    html += '<p>Средний рейтинг: ' + data.stats.average_rating + '</p>';

                    html += '<h2>Отзывы</h2>';
                    html += '<table>';
                    html += '<tr><th>Автор</th><th>Дата</th><th>Рейтинг</th><th>Текст</th></tr>';
                    data.reviews.forEach(review => {
                        html += '<tr>';
                        html += '<td>' + review.author + '</td>';
                        html += '<td>' + review.date + '</td>';
                        html += '<td>' + review.rating + '</td>';
                        html += '<td>' + review.text + '</td>';
                        html += '</tr>';
                    });
                    html += '</table>';
                    reviewsContainer.innerHTML = html;
                } else {
                    reviewsContainer.innerHTML = '<p>Ошибка: ' + data.error + '</p>';
                }
            })
            .catch(error => {
                reviewsContainer.innerHTML = '<p>Произошла ошибка при загрузке отзывов.</p>';
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>

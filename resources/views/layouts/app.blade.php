<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yandex Reviews</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@400;500;600;700&family=Inter:wght@600&display=swap" rel="stylesheet">
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

/* Main Container */
.main-container,
[data-layer="Подключение площадок"] {
    width: 1381px;
    background: white;
    position: relative;
    min-height: 2014px;
    margin: 20px auto;
    box-shadow: 0 0 20px rgba(0,0,0,0.05);
}

/* Header */
.header,
[data-layer="Rectangle 121"] {
    width: 100%;
    height: 75px;
    background: white;
    border-bottom: 1px solid #DCE4EA;
}

/* Top icons */
.top-icon,
[data-layer="Rectangle 122"] {
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
.sidebar,
[data-layer="fon"] {
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
.logo,
[data-layer="Daily Grow"] {
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

/* Logo decorative elements - replacing SVGs */
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
.menu,
[data-layer="Menu"] {
    width: 249px;
    height: 52px;
    position: absolute;
    left: 15px;
    top: 120px;
    overflow: hidden;
    z-index: 3;
}

.menu-selector,
[data-layer="Menu"] [data-layer="Selector"] {
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

.menu-text,
[data-layer="Menu"] [data-layer="Overview"] {
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
.sidebar-selector,
[data-layer="Selector"][style*="width: 249px; height: 23px;"] {
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
.sidebar-menu-item,
[data-layer="Overview"] {
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
.account-name,
[data-layer="3"] {
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
.additional-selector,
[data-layer="Selector"][style*="width: 51px;"] {
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
.campaign-title,
[data-layer="Кампании"] {
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
.input-label,
[data-layer="группа 31"] {
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
.input-container,
[data-layer=""] {
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
.input-text,
[data-layer*="https://yandex.ru"] {
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

/* Duplicate text (hidden) */
.duplicate-text {
    display: none;
}

/* Button base */
.button-base,
[data-layer="Base"][style*="background: #339AF0;"] {
    width: 128px;
    height: 25px;
    position: absolute;
    left: 315px;
    top: 199px;
    background: #339AF0;
    border-radius: 6px;
}

/* Button container */
.button-container,
[data-layer="Text"] {
    width: 96px;
    height: 14.17px;
    position: absolute;
    left: 331px;
    top: 204px;
    overflow: hidden;
    border-radius: 6px;
}

/* Button text */
.button-text,
[data-layer="+ Add Dashlet"] {
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
</head>
<body>
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

        <!-- Sidebar -->
        <div data-layer="fon" class="sidebar">
            <!-- Logo (replaced SVG with text) -->
            <div data-layer="Daily Grow" class="logo">
                <span class="logo-text">Daily Grow</span>
            </div>

            <!-- Logo decorative elements (replaced SVGs with styled divs) -->
            <div class="logo-decor decor-1"></div>
            <div class="logo-decor decor-2"></div>
            <div class="logo-decor decor-3"></div>
            <div class="logo-decor decor-4"></div>
            <div class="logo-decor decor-5"></div>
            <div class="logo-decor decor-6"></div>

            <!-- Menu -->
            <div data-layer="Menu" class="menu">
                <div data-layer="Selector" class="menu-selector"></div>
                <div data-layer="Base" class="menu-base"></div>
                <div data-layer="Selector" class="menu-selector-transparent"></div>
                <a href="{{ route('yandex-maps.index') }}" style="text-decoration: none; color: inherit;"><div data-layer="Overview" class="menu-text">Отзывы</div></a>
                <div data-layer="User Interface / Repair Tool" class="menu-icon">
                    <i class="fas fa-tools"></i>
                </div>
            </div>

            <!-- Sidebar menu items -->
            <div data-layer="Selector" class="sidebar-selector"></div>
            <div data-layer="Overview" class="sidebar-menu-item reviews"><a href="{{ route('yandex-maps.index') }}">Отзывы</a></div>
            <div data-layer="Overview" class="sidebar-menu-item settings"><a href="{{ route('yandex-maps.settings') }}">Настройка</a></div>
            
            <!-- Account name -->
            <div data-layer="3" class="account-name">Название аккаунта</div>
            
            <!-- Additional selector -->
            <div data-layer="Selector" class="additional-selector"></div>
            
            <!-- Small arrow icon (replaced SVG) -->
            <div class="small-arrow">
                <i class="fas fa-chevron-down"></i>
            </div>
        </div>

        <!-- Main content -->
        <div class="main-content-area" style="position: absolute; left: 280px; top: 75px; right: 0; bottom: 0; padding: 20px;">
            @yield('content')
        </div>
    </div>
</body>
</html>

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

/* Main content - Campaign title */
.campaign-title,
[data-layer="Кампании"] {
    position: absolute;
    left: 35px; /* Adjusted from 315px */
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
    left: 35px; /* Adjusted from 315px */
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
    left: 35px; /* Adjusted from 315px */
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
    left: 35px; /* Adjusted from 315px */
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
    left: 51px; /* Adjusted from 331px */
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

        <!-- Main content -->
        <div class="main-content-area" style="padding: 20px; margin-top: 75px;">
            @yield('content')
        </div>
    </div>
</body>
</html>

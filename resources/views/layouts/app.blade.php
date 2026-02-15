<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        /* Global styles */
        body {
            margin: 0;
            font-family: 'Mulish', 'Inter', sans-serif;
            background-color: #F9F9F9;
        }

        /* --- NAVIGATION MENU STYLES --- */
        .navigation-menu {
            width: 260px;
            background: white;
            min-height: 100vh;
            padding: 20px 0;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.04);
            position: fixed;
            left: 0;
            top: 0;
        }

        .nav-logo {
            padding: 0 20px 30px 20px;
            border-bottom: 1px solid #DCE4EA;
            margin-bottom: 20px;
        }

        .nav-items {
            list-style: none;
            padding: 0;
        }

        .nav-item {
            margin: 8px 15px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #363740;
            font-size: 16px;
            font-weight: 500;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            gap: 12px;
        }

        .nav-link:hover {
            background: rgba(51, 154, 240, 0.1);
            color: #339AF0;
        }

        .nav-link.active {
            background: #339AF0;
            color: white;
            box-shadow: 0px 2px 5px rgba(51, 154, 240, 0.2);
        }

        .nav-icon {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-link.active .nav-icon {
            color: white;
        }

        .main-content {
            margin-left: 260px;
            padding: 20px;
        }

        /* --- ADAPTIVE STYLES --- */
        @media (max-width: 768px) {
            .navigation-menu {
                width: 80px;
            }
            .nav-logo span, .nav-link span:not(.nav-icon) {
                display: none;
            }
            .main-content {
                margin-left: 80px;
            }
            .nav-link {
                justify-content: center;
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div id="app">
        <!-- Navigation Menu -->
        <div class="navigation-menu">
            <div class="nav-logo">
                <div style="color: #363740; font-size: 20px; font-weight: 700;">Daily Grow</div>
            </div>
            
            <ul class="nav-items">
                <li class="nav-item">
                    <a href="{{ route('yandex-maps.index') }}" class="nav-link {{ request()->routeIs('yandex-maps.index') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="fas fa-star"></i></span>
                        <span>Отзывы</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('yandex-maps.settings') }}" class="nav-link {{ request()->routeIs('yandex-maps.settings') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="fas fa-cog"></i></span>
                        <span>Настройка</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <main class="main-content">
            @yield('content')
        </main>
    </div>
</body>
</html>

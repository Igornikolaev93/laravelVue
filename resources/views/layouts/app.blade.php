<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

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

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Styles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f4f6f9;
            color: #2c3e50;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
        }
        .main-header {
            background-color: #ffffff;
            padding: 20px 30px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .main-header .logo {
            font-size: 24px;
            font-weight: 700;
            color: #3490dc;
            text-decoration: none;
        }
        .main-nav a {
            margin-left: 20px;
            text-decoration: none;
            color: #555;
            font-weight: 600;
        }
        .main-content {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <header class="main-header">
        <a href="{{ url('/') }}" class="logo">{{ config('app.name', 'Laravel') }}</a>
        <nav class="main-nav">
            <a href="{{ route('home') }}">Отзывы</a>
            <a href="{{ route('yandex-maps.settings') }}">Настройки</a>
        </nav>
    </header>

    <main class="container">
        @yield('content')
    </main>
</body>
</html>

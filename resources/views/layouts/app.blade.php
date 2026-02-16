<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@400;500;600;700&family=Inter:wght@600&display=swap" rel="stylesheet">
    <!-- Styles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
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

        .app {
            max-width: 1381px;
            width: 100%;
            background: white;
            border-radius: 16px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
        }

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

        .main-row {
            display: flex;
            align-items: stretch;
        }

        .content {
            flex: 1;
            padding: 30px 35px;
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

    </style>
</head>
<body>
    <div class="app">
        <header class="header">
            <i class="fas fa-square header-icon"></i>
            <i class="fas fa-mobile-alt header-icon"></i>
            <i class="fas fa-arrow-right header-icon"></i>
        </header>

        <div class="main-row">
            @include('layouts.sidebar')
            <main class="content">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Яндекс Отзывы - Подключение</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f8f9fa;
            color: #212529;
            line-height: 1.5;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 15px 0;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 24px;
            color: #339AF0;
        }
        
        .main-content {
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .connect-container {
            text-align: center;
            padding: 40px 20px;
        }
        
        .connect-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        .connect-title {
            font-size: 24px;
            color: #212529;
            margin-bottom: 10px;
        }
        
        .connect-description {
            color: #6c757d;
            margin-bottom: 30px;
        }
        
        .url-form {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .url-form form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .url-input {
            flex: 1;
            min-width: 300px;
            height: 42px;
            padding: 8px 16px;
            border: 1px solid #DCE4EA;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .url-input:focus {
            outline: none;
            border-color: #339AF0;
            box-shadow: 0 0 0 3px rgba(51,154,240,0.1);
        }
        
        .submit-button {
            background: #339AF0;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 10px 24px;
            font-weight: 600;
            cursor: pointer;
            height: 42px;
            font-size: 14px;
            transition: background-color 0.2s;
        }
        
        .submit-button:hover {
            background: #2b7ac9;
        }
        
        .alert {
            padding: 15px;
            margin-top: 20px;
            border-radius: 4px;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .example-url {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 6px;
            text-align: left;
        }
        
        .example-url h3 {
            font-size: 16px;
            margin-bottom: 10px;
            color: #495057;
        }
        
        .example-url code {
            display: block;
            padding: 10px;
            background: #e9ecef;
            border-radius: 4px;
            color: #e83e8c;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>Яндекс Карты - Отзывы</h1>
        </div>
    </div>
    
    <div class="container">
        <div class="main-content">
            <div class="connect-container">
                <div class="connect-icon">🗺️</div>
                <h2 class="connect-title">Подключение к Яндекс Картам</h2>
                <p class="connect-description">Введите ссылку на организацию в Яндекс Картах, чтобы загрузить отзывы</p>
                
                <div class="url-form">
                    <form action="{{ route('yandex-maps.index') }}" method="POST">
                        @csrf
                        <input type="url" 
                               name="yandex_maps_url" 
                               class="url-input" 
                               placeholder="https://yandex.ru/maps/org/..." 
                               required>
                        <button type="submit" class="submit-button">Подключить</button>
                    </form>
                    
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            {{ $errors->first() }}
                        </div>
                    @endif
                    
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                </div>
                
                <div class="example-url">
                    <h3>Примеры ссылок:</h3>
                    <code>https://yandex.ru/maps/org/yandex/1121637822/</code>
                    <code style="margin-top: 10px;">https://yandex.ru/maps/213/moscow/org/kafe_pushka/123456789/</code>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
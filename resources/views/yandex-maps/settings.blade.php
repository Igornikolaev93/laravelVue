@extends('layouts.app')

@section('content')
<style>
    .campaigns-section {
        max-width: 800px;
        padding: 20px;
        font-family: 'Instrument Sans', sans-serif;
        animation: fadeIn 0.5s ease-out;
    }
    .section-title {
        color: #252733;
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 8px;
    }
    .section-description {
        color: #6C757D;
        font-size: 14px;
        font-weight: 400;
        margin-bottom: 20px;
    }
    .input-wrapper {
        width: 100%;
        max-width: 500px;
        background: white;
        border: 1px solid #DCE4EA;
        border-radius: 8px;
        padding: 10px 14px;
        margin-bottom: 20px;
        transition: border-color 0.3s ease;
    }
    .input-wrapper:focus-within {
        border-color: #339AF0;
        box-shadow: 0 0 0 2px rgba(51, 154, 240, 0.1);
    }
    .url-input {
        width: 100%;
        border: none;
        outline: none;
        color: #333;
        font-size: 14px;
        background: transparent;
        text-decoration: underline;
    }
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 120px;
        height: 40px;
        padding: 0 20px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
    }
    .btn-primary {
        background: #339AF0;
        color: white;
    }
    .btn-primary:hover {
        background: #2b8ad4;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="campaigns-section">
    <h2 class="section-title">{{ ($settings && $settings->yandex_maps_url) ? 'Настройки Яндекс.Карт' : 'Подключить Яндекс' }}</h2>
    <p class="section-description">Укажите ссылку на страницу вашей организации в Яндекс, пример</p>
    
    <form action="{{ route('yandex-maps.connect') }}" method="POST">
        @csrf
        <div class="input-wrapper">
            <input 
                type="url" 
                name="yandex_maps_url"
                class="url-input" 
                value="{{ ($settings && $settings->yandex_maps_url) ? $settings->yandex_maps_url : 'https://yandex.ru/maps/org/samoye_populyarnoye_kafe/1010501395/reviews/' }}"
                placeholder="https://yandex.ru/maps/org/..."
                required
            >
        </div>
        @error('yandex_maps_url')
            <div style="color: #EF4444; font-size: 12px; margin-bottom: 10px;">{{ $message }}</div>
        @enderror
        <button type="submit" class="btn btn-primary">
            {{ ($settings && $settings->yandex_maps_url) ? 'Обновить' : 'Сохранить' }}
        </button>
    </form>
</div>
@endsection

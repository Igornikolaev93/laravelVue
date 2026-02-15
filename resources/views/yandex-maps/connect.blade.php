@extends('layouts.app')

@section('content')
<style>
    .connect-container {
        padding: 25px 35px;
    }
    .connect-title {
        font-size: 19px;
        font-weight: 700;
        color: #252733;
        margin-bottom: 8px;
    }
    .connect-label {
        font-size: 14px;
        color: #6C757D;
        margin-bottom: 20px;
    }
    .connect-form {
        max-width: 550px;
    }
    .connect-input {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #DCE4EA;
        border-radius: 6px;
        font-size: 14px;
        margin-bottom: 20px;
    }
    .connect-button {
        background-color: #339AF0;
        color: white;
        border: none;
        border-radius: 6px;
        padding: 10px 24px;
        font-weight: 600;
        cursor: pointer;
        font-size: 14px;
    }
    .connect-button:hover {
        background-color: #2b7ac9;
    }
</style>

<div class="connect-container">
    <h1 class="connect-title">Подключить Яндекс</h1>
    <p class="connect-label">Укажите ссылку на Яндекс, пример</p>

    <form action="{{ route('yandex-maps.connect') }}" method="POST" class="connect-form">
        @csrf
        <input type="url" 
               name="yandex_maps_url" 
               class="connect-input" 
               value="https://yandex.ru/maps/org/samoye_populyarnoye_kafe/1010501395/reviews/">
        
        <button type="submit" class="connect-button">Сохранить</button>
    </form>
</div>
@endsection

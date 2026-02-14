@extends('layouts.app')

@section('content')
<style>
    .connect-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding-top: 50px;
    }
    .connect-container .campaign-title,
    .connect-container .input-label {
        position: static;
        margin-bottom: 15px;
    }
    .url-input {
        width: 480px;
        height: 38px;
        padding: 6px 14px;
        border: 1px solid #DCE4EA;
        border-radius: 6px;
    }
    .submit-button {
        background-color: #339AF0;
        color: white;
        border: none;
        border-radius: 6px;
        padding: 10px 20px;
        font-weight: 600;
        cursor: pointer;
        height: 38px;
    }
</style>

<div class="connect-container">
    <div class="campaign-title">Подключить Яндекс</div>
    <div class="input-label">Укажите ссылку на Яндекс, пример</div>

    <form action="{{ route('yandex-maps.index') }}" method="POST" style="display: flex; align-items: center; gap: 10px;">
        @csrf
        <input type="text" name="yandex_maps_url" class="url-input" placeholder="https://yandex.ru/maps/org/samoye_populyarnoye_kafe/1010501395/reviews/" required>
        <button type="submit" class="submit-button">Сохранить</button>
    </form>
</div>
@endsection

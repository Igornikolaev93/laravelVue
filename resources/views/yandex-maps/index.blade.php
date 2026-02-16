@extends('layouts.app')

@section('content')
<style>
    .container {
        padding: 20px;
        font-family: 'Mulish', sans-serif;
    }

    .campaign-title {
        color: #252733;
        font-size: 16px;
        font-weight: 600;
        line-height: 20px;
        letter-spacing: 0.20px;
        margin-bottom: 15px;
    }

    .input-label {
        color: #6C757D;
        font-size: 12px;
        font-weight: 600;
        line-height: 20px;
        letter-spacing: 0.20px;
        margin-bottom: 5px;
    }

    .input-container {
        width: 480px;
        height: 36px;
        padding: 6px 14px;
        background: white;
        border-radius: 6px;
        border: 1px solid #DCE4EA;
        display: inline-flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 15px;
    }

    .input-container input {
        width: 100%;
        border: none;
        outline: none;
        color: #788397;
        font-size: 12px;
        font-family: 'Mulish', sans-serif;
        font-weight: 400;
    }

    .button-base {
        width: 128px;
        height: 35px;
        background: #339AF0;
        border-radius: 6px;
        border: none;
        color: white;
        font-size: 14px;
        font-family: 'Inter', sans-serif;
        font-weight: 600;
        text-align: center;
        cursor: pointer;
    }
</style>

<div class="container">
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="campaign-title">Подключить Яндекс</div>

    <form action="{{ route('yandex-maps.connect') }}" method="POST">
        @csrf
        <div class="input-label">Укажите ссылку на Яндекс, пример</div>
        <div class="input-container">
            <input type="text" name="yandex_maps_url" placeholder="https://yandex.ru/maps/org/..." value="{{ $settings->yandex_maps_url ?? '' }}">
        </div>
        <br>
        <button type="submit" class="button-base">Сохранить</button>
    </form>
</div>
@endsection

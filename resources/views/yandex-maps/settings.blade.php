@extends('layouts.app')

@section('content')
<style>
    .settings-form {
        max-width: 600px;
        margin: 0 auto;
        background: #fff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .form-group {
        margin-bottom: 25px;
    }
    .form-label {
        display: block;
        margin-bottom: 10px;
        font-weight: 600;
        color: #333;
    }
    .form-control {
        width: 100%;
        padding: 12px;
        border: 1px solid #ccc;
        border-radius: 8px;
        transition: border-color 0.2s;
    }
    .form-control:focus {
        outline: none;
        border-color: #3490dc;
    }
    .btn-primary {
        padding: 12px 25px;
        background-color: #3490dc;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .btn-primary:hover {
        background-color: #2779bd;
    }
    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 8px;
    }
    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .alert-error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
</style>

<div class="settings-form">
    <h3 style="text-align: center; margin-bottom: 30px;">Настройки Яндекс.Карт</h3>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('yandex-maps.connect') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="yandex_maps_url" class="form-label">URL организации на Яндекс.Картах</label>
            <input type="text" name="yandex_maps_url" id="yandex_maps_url" class="form-control" 
                   placeholder="https://yandex.ru/maps/org/...." 
                   value="{{ old('yandex_maps_url', $settings->yandex_maps_url ?? '') }}">
            @error('yandex_maps_url')
                <div style="color: #e3342f; font-size: 14px; margin-top: 5px;">{{ $message }}</div>
            @enderror
        </div>
        <div style="text-align: center;">
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </div>
    </form>
</div>
@endsection

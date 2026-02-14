@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="campaign-title" style="position: static; margin-bottom: 20px;">Yandex Maps Settings</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('yandex-maps.save-settings') }}" method="POST">
        @csrf
        <div class="mb-3">
            <div class="input-label" style="position: static; margin-bottom: 10px;">Укажите ссылку на Яндекс, пример</div>
            <div class="input-container" style="position: static; margin-bottom: 20px;">
                <input type="text" class="input-text" style="width: 100%; border: none; outline: none; text-decoration: none;" id="yandex_maps_url" name="yandex_maps_url" value="{{ old('yandex_maps_url', $settings->yandex_maps_url ?? '') }}" placeholder="https://yandex.ru/maps/org/samoye_populyarnoye_kafe/1010501395/reviews/">
            </div>
            @error('yandex_maps_url')
                <div class="form-text text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="button-container" style="position: static;">
            <button type="submit" class="button-base" style="position: static; border: none; width: 128px; height: 25px;">
                <div class="button-text" style="position: static; text-align: center; width: 100%;">Сохранить</div>
            </button>
        </div>
    </form>
</div>
@endsection

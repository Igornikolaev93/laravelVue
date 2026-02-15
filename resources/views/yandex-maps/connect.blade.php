
@extends('layouts.app')

@section('content')
<div data-layer="Кампании" class="campaign-title">Подключить Яндекс</div>
<div data-layer="группа 31" class="input-label">Укажите ссылку на Яндекс, пример</div>

<form action="{{ route('yandex-maps.connect') }}" method="POST">
    @csrf
    <div data-layer="" class="input-container">
        <input type="url" name="yandex_maps_url" class="input-text" value="https://yandex.ru/maps/org/samoye_populyarnoye_kafe/1010501395/reviews/">
    </div>

    <div class="button-base"></div>
    <div class="button-container">
        <button type="submit" class="button-text">Сохранить</button>
    </div>
</form>
@endsection

@extends('layouts.app')

@section('content')
<style>
    .url-form-container { display: flex; justify-content: center; align-items: center; height: 70vh; }
    .url-form-box { background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); text-align: center; }
    .url-form-box h1 { margin-bottom: 10px; font-size: 1.5em; color: #333; }
    .url-form-box p { margin-bottom: 20px; color: #666; }
    .url-form-box form { display: flex; align-items: center; gap: 10px; }
    .url-input { width: 480px; height: 38px; padding: 6px 14px; border: 1px solid #DCE4EA; border-radius: 6px; }
    .submit-button { background: #339AF0; color: white; border: none; border-radius: 6px; padding: 10px 20px; font-weight: 600; cursor: pointer; height: 38px; }
    .alert { padding: 15px; margin-top: 20px; border-radius: 4px; text-align: left; }
    .alert-danger { background: #f8d7da; color: #721c24; }
</style>

<div class="url-form-container">
    <div class="url-form-box">
        <h1>Connect to Yandex Maps</h1>
        <p>Please enter your Yandex Maps organization URL to fetch reviews.</p>
        <form action="{{ route('yandex-maps.index') }}" method="POST">
            @csrf
            <input type="url" name="yandex_maps_url" class="url-input" 
                   placeholder="https://yandex.ru/maps/org/..." required>
            <button type="submit" class="submit-button">Save URL</button>
        </form>
        
        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif
    </div>
</div>
@endsection

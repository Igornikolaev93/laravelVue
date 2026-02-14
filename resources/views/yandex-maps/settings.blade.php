@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Yandex Maps Settings</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('yandex-maps.save-settings') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="url">Yandex Maps URL</label>
            <input type="url" name="url" id="url" class="form-control" value="{{ $settings->url ?? '' }}">
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</div>
@endsection

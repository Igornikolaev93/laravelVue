@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Yandex Maps Reviews</h1>

    @if ($settings)
        <p><strong>Rating:</strong> {{ $settings->rating ?? 'N/A' }}</p>
        <p><strong>Total Reviews:</strong> {{ $settings->total_reviews ?? 'N/A' }}</p>

        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('yandex-maps.index', ['sort' => 'newest']) }}" class="btn btn-sm btn-light {{ $sort === 'newest' ? 'active' : '' }}">Newest</a>
            <a href="{{ route('yandex-maps.index', ['sort' => 'oldest']) }}" class="btn btn-sm btn-light {{ $sort === 'oldest' ? 'active' : '' }}">Oldest</a>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Author</th>
                    <th>Rating</th>
                    <th>Text</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reviews as $review)
                    <tr>
                        <td>{{ $review['author'] }}</td>
                        <td>{{ $review['rating'] }}</td>
                        <td>{{ $review['text'] }}</td>
                        <td>{{ $review['date'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No reviews found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $reviews->links() }}
    @else
        <p>Please configure the Yandex Maps URL in the <a href="{{ route('yandex-maps.settings') }}">settings</a>.</p>
    @endif
</div>
@endsection
